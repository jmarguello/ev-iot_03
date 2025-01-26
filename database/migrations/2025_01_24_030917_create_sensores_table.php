<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_sensores_table.php
    public function up(): void
    {
        Schema::create('sensores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_variable_id')->constrained('tipos_variables');
            $table->string('direccion_mac', 17);
            $table->string('nombre', 100);
            $table->string('ubicacion', 200)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('tipo_bateria', 50)->nullable();
            $table->decimal('umbral_bateria', 5, 2)->nullable();
            $table->integer('umbral_senal')->nullable();
            $table->enum('estado', ['activo', 'mantenimiento', 'inactivo'])->default('activo');
            $table->timestamp('ultima_lectura')->nullable();
            $table->timestamps();

            $table->unique('direccion_mac');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensores');
    }
};
