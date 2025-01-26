<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DataProcessingService;
use Illuminate\Http\Request;

class DataReceiverController extends Controller
{
    private $dataProcessingService;

    public function __construct(DataProcessingService $dataProcessingService)
    {
        $this->dataProcessingService = $dataProcessingService;
    }

    /*     public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'mac_concentrador' => 'required|string|size:17',
                'lecturas' => 'required|array',
                'lecturas.*.mac_sensor' => 'required|string|size:17',
                'lecturas.*.valor' => 'required|numeric',
                'lecturas.*.nivel_senal' => 'required|integer',
                'lecturas.*.nivel_bateria' => 'numeric|nullable',
                'lecturas.*.cantidad_lecturas' => 'numeric|nullable'
            ]);

            $result = $this->dataProcessingService->processData($validatedData);
            return response()->json(['message' => 'Datos recibidos correctamente'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    } */
    public function store(Request $request)
    {
        try {
            $data = $request->all();

            if ($data['msg'] !== 'advData') {
                throw new \Exception('Formato de mensaje inválido');
            }

            $concentradorId = $data['gmac'];
            $lecturas = [];

            foreach ($data['obj'] as $reading) {
                $lecturas[] = [
                    'mac_sensor' => $reading['dmac'],
                    'valor' => $reading['temp'], // Temperatura como valor principal
                    'nivel_senal' => $reading['rssi'],
                    'nivel_bateria' => $reading['vbatt'] / 1000, // Convertir mV a V
                    'cantidad_lecturas' => 1
                ];
            }

            $validatedData = [
                'mac_concentrador' => $concentradorId,
                'lecturas' => $lecturas
            ];

            $result = $this->dataProcessingService->processData($validatedData);
            return response()->json(['message' => 'Datos recibidos correctamente'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
