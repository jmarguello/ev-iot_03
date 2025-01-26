<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// app/Models/TipoVariable.php
class TipoVariable extends Model
{
    protected $table = 'tipos_variables';
    protected $fillable = ['nombre', 'unidad', 'tipo_dato', 'descripcion'];

    public function sensores(): HasMany
    {
        return $this->hasMany(Sensor::class);
    }

    public function estados(): HasMany
    {
        return $this->hasMany(EstadoVariable::class);
    }
}
