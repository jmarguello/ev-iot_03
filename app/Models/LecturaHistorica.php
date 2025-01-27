<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// app/Models/LecturaHistorica.php
class LecturaHistorica extends Model
{
    protected $table = 'lecturas_historicas';

    public $timestamps = false;

    protected $fillable = [
        'sensor_id',
        'valor_promedio',
        'valor_minimo',
        'valor_maximo',
        'fecha_hora',
        'cantidad_lecturas'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }
}
