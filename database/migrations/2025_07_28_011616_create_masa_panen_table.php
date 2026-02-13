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
        Schema::create('masa_panen', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_laporan')->constrained('laporan')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignUuid('id_komoditas')->constrained('komoditas')->cascadeOnDelete();
            $table->decimal('harga_satuan', 10, 2);
            $table->decimal('jumlah', 10, 2);
            $table->decimal('harga_total', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masa_panen');
    }
};
