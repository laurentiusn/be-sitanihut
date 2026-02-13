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
        Schema::create('masa_tanam', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_laporan')->constrained('laporan')->cascadeOnDelete();
            $table->date('tanggal')->nullable();
            $table->foreignUuid('id_komoditas')->constrained('komoditas')->cascadeOnDelete();
            $table->decimal('usia_tanam', 10, 2)->nullable();
            $table->decimal('jumlah', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masa_tanam');
    }
};
