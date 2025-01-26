<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_concentradores_table.php
    public function up(): void
    {
        Schema::create('concentradores', function (Blueprint $table) {
            $table->id();
            $table->string('direccion_mac', 17);
            $table->string('nombre', 100);
            $table->string('ubicacion', 200)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('ultima_comunicacion')->nullable();
            $table->timestamps();

            $table->unique('direccion_mac');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concentradores');
    }
};
