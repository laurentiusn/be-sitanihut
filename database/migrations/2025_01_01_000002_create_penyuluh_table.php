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
        Schema::create('penyuluh', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_penyuluh', 35);
            $table->string('nip', 20);
            $table->string('nomor_wa', 20);
            $table->enum('jenis_kelamin', ['pria', 'wanita']);
            $table->string('jabatan', 100);
            $table->foreignUuid('id_kph')->constrained('kph')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyuluh');
    }
};
