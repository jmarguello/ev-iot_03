<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_tipos_variables_table.php
    public function up(): void
    {
        Schema::create('tipos_variables', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('unidad', 20)->nullable();
            $table->enum('tipo_dato', ['numerico', 'booleano', 'coordenadas']);
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->unique('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_variables');
    }
};
