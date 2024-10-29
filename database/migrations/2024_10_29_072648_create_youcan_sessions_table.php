<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('youcan_sessions', function (Blueprint $table) {
            $table->string('id', 64)->unique();
            $table->string('store_id', 255);
            $table->string('scope', 255)->nullable();
            $table->text('access_token')->nullable();
            $table->timestamp('expires')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youcan_sessions');
    }
};
