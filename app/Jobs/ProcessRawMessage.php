<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessRawMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $rawMessageId;

    public function __construct($rawMessageId)
    {
        $this->rawMessageId = $rawMessageId;
    }

    public function handle()
    {
        try {
            $maxRetries = 3;
            $retryDelay = 1; // segundos

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    // Obtener el mensaje crudo
                    $rawMessage = DB::table('raw_messages')
                        ->where('id', $this->rawMessageId)
                        ->first();

                    if (!$rawMessage || $rawMessage->processed) {
                        return;
                    }

                    $payload = json_decode($rawMessage->payload, true);
                    $concentrador = $this->getOrCreateConcentrador($payload['gmac']);

                    foreach ($payload['obj'] as $sensorData) {
                        DB::transaction(function () use ($sensorData, $concentrador) {
                            $sensor = $this->getOrCreateSensor($sensorData);
                            $this->procesarLecturaActual($sensor, $sensorData, $concentrador);
                            $this->procesarLecturaHistorica($sensor, $sensorData);
                        }, 5); // 5 reintentos para la transacción
                    }

                    // Marcar mensaje como procesado
                    DB::table('raw_messages')
                        ->where('id', $this->rawMessageId)
                        ->update([
                            'processed' => true,
                            'processed_at' => now(),
                            'updated_at' => now()
                        ]);

                    break; // Si llegamos aquí, todo salió bien

                } catch (\PDOException $e) {
                    if ($e->getCode() == 'HY000' && $attempt < $maxRetries) {
                        // Base de datos bloqueada, esperar y reintentar
                        Log::warning("Base de datos bloqueada, reintento {$attempt} de {$maxRetries}", [
                            'message_id' => $this->rawMessageId
                        ]);
                        sleep($retryDelay);
                        continue;
                    }
                    throw $e;
                }
            }

        } catch (\Exception $e) {
            Log::error('Error procesando mensaje raw', [
                'message_id' => $this->rawMessageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getOrCreateConcentrador($mac)
    {
        $concentrador = DB::table('concentradores')
            ->where('direccion_mac', $mac)
            ->first();

        if (!$concentrador) {
            $id = DB::table('concentradores')->insertGetId([
                'direccion_mac' => $mac,
                'nombre' => "Concentrador {$mac}",
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $concentrador = DB::table('concentradores')->find($id);
        }

        // Actualizar última comunicación
        DB::table('concentradores')
            ->where('id', $concentrador->id)
            ->update([
                'ultima_comunicacion' => now(),
                'updated_at' => now()
            ]);

        return $concentrador;
    }

    private function getOrCreateSensor($sensorData)
    {
        $sensor = DB::table('sensores')
            ->where('direccion_mac', $sensorData['dmac'])
            ->first();

        if (!$sensor) {
            // Asumimos que es un sensor de temperatura por defecto
            $tipoVariableId = DB::table('tipos_variables')
                ->where('nombre', 'Temperatura')
                ->first()->id;

            $id = DB::table('sensores')->insertGetId([
                'tipo_variable_id' => $tipoVariableId,
                'direccion_mac' => $sensorData['dmac'],
                'nombre' => "Sensor {$sensorData['dmac']}",
                'estado' => 'activo',
                'tipo_bateria' => 'CR2032',
                'umbral_bateria' => 2.5,
                'umbral_senal' => -90,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $sensor = DB::table('sensores')->find($id);
        }

        return $sensor;
    }

    private function procesarLecturaActual($sensor, $sensorData, $concentrador)
    {
        try {
            Log::info('Iniciando procesarLecturaActual', [
                'sensor_id' => $sensor->id,
                'dmac' => $sensorData['dmac']
            ]);

            $estadoId = DB::table('estados_variables')
                ->where('tipo_variable_id', $sensor->tipo_variable_id)
                ->where('severidad', 'info')
                ->first()->id;

            $now = now();
            $fechaHora = Carbon::parse($sensorData['time']);

            // Verificar si existe una lectura actual para este sensor
            $exists = DB::table('lecturas_actuales')
                ->where('sensor_id', $sensor->id)
                ->exists();

            if ($exists) {
                $sql = "UPDATE lecturas_actuales SET 
                        valor = ?,
                        estado_id = ?,
                        nivel_senal = ?,
                        nivel_bateria = ?,
                        concentrador_id = ?,
                        cantidad_lecturas = cantidad_lecturas + 1,
                        fecha_hora = ?,
                        updated_at = ?
                        WHERE sensor_id = ?";

                DB::statement($sql, [
                    $sensorData['temp'],
                    $estadoId,
                    $sensorData['rssi'],
                    $sensorData['vbatt'] / 1000,
                    $concentrador->id,
                    $fechaHora,
                    $now,
                    $sensor->id
                ]);
            } else {
                $sql = "INSERT INTO lecturas_actuales 
                        (sensor_id, valor, estado_id, nivel_senal, nivel_bateria, 
                        concentrador_id, cantidad_lecturas, fecha_hora, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                DB::statement($sql, [
                    $sensor->id,
                    $sensorData['temp'],
                    $estadoId,
                    $sensorData['rssi'],
                    $sensorData['vbatt'] / 1000,
                    $concentrador->id,
                    1, // cantidad_lecturas inicial
                    $fechaHora,
                    $now,
                    $now
                ]);
            }

            // Actualizar última lectura del sensor
            DB::table('sensores')
                ->where('id', $sensor->id)
                ->update([
                    'ultima_lectura' => $fechaHora,
                    'updated_at' => $now
                ]);

            Log::info('Lectura procesada correctamente', [
                'sensor_id' => $sensor->id,
                'operacion' => $exists ? 'update' : 'insert'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en procesarLecturaActual', [
                'error' => $e->getMessage(),
                'sensor_id' => $sensor->id,
                'sensor_data' => $sensorData,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function procesarLecturaHistorica($sensor, $sensorData)
    {
        try {
            $fechaHora = Carbon::parse($sensorData['time'])->startOfHour();
            $valor = floatval($sensorData['temp']);

            // Buscar registro existente para esta hora
            $registro = DB::table('lecturas_historicas')
                ->where('sensor_id', $sensor->id)
                ->where('fecha_hora', $fechaHora)
                ->first();

            Log::info('Procesando lectura histórica', [
                'sensor_id' => $sensor->id,
                'fecha_hora' => $fechaHora,
                'valor' => $valor,
                'existe_registro' => !is_null($registro)
            ]);

            if ($registro) {
                // Calcular nuevos valores
                $nuevaCantidad = $registro->cantidad_lecturas + 1;
                $nuevoPromedio = (($registro->valor_promedio * $registro->cantidad_lecturas) + $valor) / $nuevaCantidad;

                DB::table('lecturas_historicas')
                    ->where('sensor_id', $sensor->id)
                    ->where('fecha_hora', $fechaHora)
                    ->update([
                        'valor_promedio' => round($nuevoPromedio, 3),
                        'valor_minimo' => min($registro->valor_minimo, $valor),
                        'valor_maximo' => max($registro->valor_maximo, $valor),
                        'cantidad_lecturas' => $nuevaCantidad
                    ]);
            } else {
                // Crear nuevo registro
                DB::table('lecturas_historicas')->insert([
                    'sensor_id' => $sensor->id,
                    'fecha_hora' => $fechaHora,
                    'valor_promedio' => $valor,
                    'valor_minimo' => $valor,
                    'valor_maximo' => $valor,
                    'cantidad_lecturas' => 1
                ]);
            }

            Log::info('Lectura histórica procesada correctamente', [
                'sensor_id' => $sensor->id,
                'fecha_hora' => $fechaHora,
                'operacion' => $registro ? 'update' : 'insert'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en procesarLecturaHistorica', [
                'error' => $e->getMessage(),
                'sensor_id' => $sensor->id,
                'fecha_hora' => $fechaHora ?? null,
                'valor' => $valor ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}