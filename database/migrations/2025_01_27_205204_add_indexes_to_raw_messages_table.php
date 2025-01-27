<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_messages', function (Blueprint $table) {
            $table->index('gmac');
            $table->index('processed');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('raw_messages', function (Blueprint $table) {
            $table->dropIndex(['gmac']);
            $table->dropIndex(['processed']);
            $table->dropIndex(['created_at']);
        });
    }
};