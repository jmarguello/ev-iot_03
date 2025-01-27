<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estados_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_variable_id')->constrained('tipos_variables');
            $table->string('nombre', 50);
            $table->enum('severidad', ['info', 'advertencia', 'error', 'critico']);
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->unique(['tipo_variable_id', 'nombre']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados_variables');
    }
};
