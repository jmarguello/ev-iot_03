<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lecturas_actuales', function (Blueprint $table) {
            $table->integer('cantidad_lecturas')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('lecturas_actuales', function (Blueprint $table) {
            $table->integer('cantidad_lecturas')->nullable(false)->change();
        });
    }
};