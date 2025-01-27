<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessRawMessage;

class ReprocesarMensajes extends Command
{
    protected $signature = 'mensajes:reprocesar';
    protected $description = 'Reprocesa los mensajes raw no procesados';

    public function handle()
    {
        // Obtener todos los mensajes no procesados
        $mensajes = DB::table('raw_messages')
            ->where('processed', false)
            ->orderBy('created_at')
            ->get();

        $this->info("Encontrados {$mensajes->count()} mensajes para procesar");

        foreach ($mensajes as $mensaje) {
            $this->info("Procesando mensaje ID: {$mensaje->id}");
            ProcessRawMessage::dispatch($mensaje->id);
        }

        $this->info('Todos los mensajes han sido enviados para procesamiento');
    }
}