<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('raw_messages', function (Blueprint $table) {
        $table->id();
        $table->string('gmac')->nullable();
        $table->json('payload');
        $table->boolean('processed')->default(false);
        $table->timestamp('processed_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_messages');
    }
};
