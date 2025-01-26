<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// app/Models/Concentrador.php
class Concentrador extends Model
{
    protected $table = 'concentradores';

    protected $fillable = [
        'direccion_mac',
        'nombre',
        'ubicacion',
        'estado'
    ];

    protected $casts = [
        'ultima_comunicacion' => 'datetime'
    ];

    public function lecturasActuales(): HasMany
    {
        return $this->hasMany(LecturaActual::class);
    }
}
