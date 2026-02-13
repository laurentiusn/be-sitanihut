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
        Schema::create('lampiran_laporan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_laporan')->constrained('laporan')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nama_file');
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lampiran_laporan');
    }
};
