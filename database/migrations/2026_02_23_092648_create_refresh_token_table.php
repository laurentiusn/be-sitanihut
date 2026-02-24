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
        Schema::create('refresh_token', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_user')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('device_unq')->nullable();
            $table->string('device_name')->nullable();
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->unique(['id_user', 'device_unq']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_token');
    }
};
