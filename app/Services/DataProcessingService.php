<?php

namespace App\Services;

use App\Jobs\ProcessSensorData;
use App\Models\Concentrador;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DataProcessingService
{
    use DispatchesJobs;

    public function processData(array $data)
    {
        return DB::transaction(function () use ($data) {
            $concentrador = $this->updateConcentrador($data['mac_concentrador']);

            foreach ($data['lecturas'] as $lectura) {
                $this->dispatch(new ProcessSensorData($lectura, $concentrador->id));
            }

            return $concentrador;
        });
    }

    private function updateConcentrador(string $mac)
    {
        return Concentrador::updateOrCreate(
            ['direccion_mac' => $mac],
            [
                'ultima_comunicacion' => now(),
                'estado' => 'activo'
            ]
        );
    }
}
