<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DataProcessingService;
use App\Jobs\ProcessRawMessage; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataReceiverController extends Controller
{
    private $dataProcessingService;

    public function __construct(DataProcessingService $dataProcessingService)
    {
        $this->dataProcessingService = $dataProcessingService;
    }

    public function store(Request $request)
    {
        try {
            // Validación específica para el formato de tus mensajes
            $validated = $request->validate([
                'msg' => 'required|string|in:advData',
                'gmac' => 'required|string|size:12',
                'obj' => 'required|array',
                'obj.*.type' => 'required|integer',
                'obj.*.dmac' => 'required|string|size:12',
                'obj.*.time' => 'required|string',
                'obj.*.rssi' => 'required|integer',
                'obj.*.vbatt' => 'required|integer',
                'obj.*.temp' => 'required|numeric'
            ]);

            // Guardar mensaje crudo
            $rawMessageId = DB::table('raw_messages')->insertGetId([
                'gmac' => $request->input('gmac'),
                'payload' => json_encode($request->all()),
                'processed' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Disparar el job de procesamiento
            ProcessRawMessage::dispatch($rawMessageId);

            // Log para monitoreo
            Log::info('Mensaje IoT recibido', [
                'id' => $rawMessageId,
                'gmac' => $request->input('gmac'),
                'sensors' => count($request->input('obj'))
            ]);

            return response()->json([
                'message' => 'Datos recibidos correctamente',
                'id' => $rawMessageId
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validación fallida en mensaje IoT', [
                'errors' => $e->errors(),
                'payload' => $request->all()
            ]);
            return response()->json([
                'error' => 'Formato de datos inválido',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al procesar mensaje IoT', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);
            return response()->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
}