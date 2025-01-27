<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// app/Models/Sensor.php
class Sensor extends Model
{
    protected $table = 'sensores';

    protected $fillable = [
        'tipo_variable_id',
        'direccion_mac',
        'nombre',
        'ubicacion',
        'descripcion',
        'tipo_bateria',
        'umbral_bateria',
        'umbral_senal',
        'estado'
    ];

    protected $casts = [
        'ultima_lectura' => 'datetime'
    ];

    public function tipoVariable(): BelongsTo
    {
        return $this->belongsTo(TipoVariable::class);
    }

    public function lecturaActual(): HasOne
    {
        return $this->hasOne(LecturaActual::class);
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(LecturaHistorica::class);
    }
}
