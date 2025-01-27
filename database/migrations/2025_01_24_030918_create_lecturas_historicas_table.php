<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_lecturas_historicas_table.php
    public function up(): void
    {
        Schema::create('lecturas_historicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained('sensores');
            $table->decimal('valor_promedio', 10, 3)->nullable();
            $table->decimal('valor_minimo', 10, 3)->nullable();
            $table->decimal('valor_maximo', 10, 3)->nullable();
            $table->timestamp('fecha_hora');
            $table->integer('cantidad_lecturas');

            $table->index(['sensor_id', 'fecha_hora']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturas_historicas');
    }
};
