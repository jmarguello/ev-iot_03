<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// app/Models/LecturaActual.php
class LecturaActual extends Model
{
    protected $table = 'lecturas_actuales';

    public $timestamps = true;
    
    protected $fillable = [
        'sensor_id',
        'valor',
        'estado_id',
        'nivel_senal',
        'nivel_bateria',
        'concentrador_id',
        'cantidad_lecturas',
        'fecha_hora'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoVariable::class);
    }

    public function concentrador(): BelongsTo
    {
        return $this->belongsTo(Concentrador::class);
    }
}
