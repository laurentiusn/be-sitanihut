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
        Schema::create('log_laporan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('id_laporan')->constrained('laporan')->onDelete('cascade');
            $table->uuid('id_pengaju');
            $table->foreignUuid('id_pemeriksa')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['ditolak', 'diverifikasi', 'disetujui']);
            $table->year('periode');
            $table->enum('bulan', ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember']);
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_laporan');
    }
};
