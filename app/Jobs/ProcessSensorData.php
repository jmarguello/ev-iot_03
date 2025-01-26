<?php

namespace App\Jobs;

use App\Models\Sensor;
use App\Models\LecturaActual;
use App\Models\LecturaHistorica;
use App\Models\EstadoVariable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSensorData implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $lectura;
    private $concentradorId;

    public function __construct(array $lectura, int $concentradorId)
    {
        $this->lectura = $lectura;
        $this->concentradorId = $concentradorId;
    }

    public function handle()
    {
        try {
            // Buscar el sensor por dirección MAC
            $sensor = Sensor::where('direccion_mac', $this->lectura['mac_sensor'])->firstOrFail();

            // Determinar el estado del sensor
            $estado = $this->determinarEstado($sensor);

            // Actualizar o crear lectura actual
            LecturaActual::updateOrCreate(
                ['sensor_id' => $sensor->id],
                [
                    'valor' => $this->lectura['valor'],
                    'estado_id' => $estado->id,
                    'nivel_senal' => $this->lectura['nivel_senal'],
                    'nivel_bateria' => $this->lectura['nivel_bateria'] ?? null,
                    'concentrador_id' => $this->concentradorId,
                    'cantidad_lecturas' => $this->lectura['cantidad_lecturas'] ?? null,
                    'fecha_hora' => now()
                ]
            );

            // Actualizar registros históricos
            $this->actualizarHistoricos($sensor);

            // Actualizar fecha de última lectura del sensor
            $sensor->update(['ultima_lectura' => now()]);
        } catch (\Exception $e) {
            // Logging de errores
            Log::error('Error en ProcessSensorData', [
                'message' => $e->getMessage(),
                'lectura' => $this->lectura,
                'concentradorId' => $this->concentradorId,
                'trace' => $e->getTraceAsString()
            ]);

            // Relanzar la excepción para que Laravel maneje el job fallido
            throw $e;
        }
    }

    private function determinarEstado(Sensor $sensor)
    {
        return EstadoVariable::where('tipo_variable_id', $sensor->tipo_variable_id)
            ->where('severidad', 'info')
            ->firstOrFail();
    }

    private function actualizarHistoricos(Sensor $sensor)
    {
        $hora = now()->startOfHour();

        // Buscar el registro histórico existente o crear uno nuevo
        $lecturaHistorica = LecturaHistorica::firstOrNew(
            [
                'sensor_id' => $sensor->id,
                'fecha_hora' => $hora
            ]
        );

        // Calcular nuevos valores
        $valorPromedio = $this->calcularPromedio($sensor, $hora);
        $valorMinimo = $this->calcularMinimo($sensor, $hora);
        $valorMaximo = $this->calcularMaximo($sensor, $hora);

        // Incrementar el contador de lecturas
        $lecturaHistorica->cantidad_lecturas++;

        // Actualizar los valores
        $lecturaHistorica->valor_promedio = $valorPromedio;
        $lecturaHistorica->valor_minimo = $valorMinimo;
        $lecturaHistorica->valor_maximo = $valorMaximo;

        // Guardar el registro
        $lecturaHistorica->save();
    }

    /*     private function actualizarHistoricos(Sensor $sensor)
    {
        $hora = now()->startOfHour();

        LecturaHistorica::updateOrCreate(
            [
                'sensor_id' => $sensor->id,
                'fecha_hora' => $hora
            ],
            [
                'valor_promedio' => $this->calcularPromedio($sensor, $hora),
                'valor_minimo' => $this->calcularMinimo($sensor, $hora),
                'valor_maximo' => $this->calcularMaximo($sensor, $hora),
                'cantidad_lecturas' => DB::raw('cantidad_lecturas + 1')
            ]
        );
    } */

    private function calcularPromedio(Sensor $sensor, $hora)
    {
        return LecturaActual::where('sensor_id', $sensor->id)
            ->where('fecha_hora', '>=', $hora)
            ->avg('valor');
    }

    private function calcularMinimo(Sensor $sensor, $hora)
    {
        return LecturaActual::where('sensor_id', $sensor->id)
            ->where('fecha_hora', '>=', $hora)
            ->min('valor');
    }

    private function calcularMaximo(Sensor $sensor, $hora)
    {
        return LecturaActual::where('sensor_id', $sensor->id)
            ->where('fecha_hora', '>=', $hora)
            ->max('valor');
    }
}
