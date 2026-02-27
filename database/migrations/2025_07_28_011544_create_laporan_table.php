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
        Schema::create('laporan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->year('periode');
            $table->enum('bulan', ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember']);
            $table->date('tanggal');
            $table->decimal('modal', 15, 2)->default(0);
            $table->decimal('nte', 15, 2)->default(0);
            $table->enum('status', ['belum diajukan', 'menunggu', 'diverifikasi', 'disetujui', 'ditolak'])->default('belum diajukan');
            $table->date('tanggal_keputusan')->nullable();
            $table->date('tanggal_tindakan')->nullable();
            $table->date('tanggal_pengajuan')->nullable();
            $table->text('catatan_petani')->nullable();
            $table->text('catatan_penyuluh')->nullable();
            $table->foreignUuid('id_user')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
