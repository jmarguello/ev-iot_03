<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Añadir esta línea

// app/Models/EstadoVariable.php
class EstadoVariable extends Model
{
    protected $table = 'estados_variables';
    protected $fillable = ['tipo_variable_id', 'nombre', 'severidad', 'descripcion'];

    public function tipoVariable(): BelongsTo
    {
        return $this->belongsTo(TipoVariable::class);
    }
}
