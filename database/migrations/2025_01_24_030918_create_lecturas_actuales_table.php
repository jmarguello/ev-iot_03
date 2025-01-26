<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_lecturas_actuales_table.php
    public function up(): void
    {
        Schema::create('lecturas_actuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained('sensores');
            $table->decimal('valor', 10, 3)->nullable();
            $table->foreignId('estado_id')->constrained('estados_variables');
            $table->integer('nivel_senal');
            $table->decimal('nivel_bateria', 5, 2)->nullable();
            $table->foreignId('concentrador_id')->constrained('concentradores');
            $table->integer('cantidad_lecturas');
            $table->timestamp('fecha_hora');
            $table->timestamps();

            $table->unique('sensor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturas_actuales');
    }
};
