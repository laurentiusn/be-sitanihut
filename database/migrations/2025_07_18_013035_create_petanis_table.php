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
        Schema::create('petani', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_petani', 35);
            $table->string('nik', 20)->unique();
            $table->enum('jenis_kelamin', ['pria', 'wanita']);
            $table->text('alamat');
            $table->string('nomor_wa', 20);
            $table->string('pendidikan_terakhir', 10);
            $table->string('pekerjaan_sampingan', 20);
            $table->float('luas_lahan');
            $table->foreignUuid('id_kth')->constrained('kth')->cascadeOnUpdate()->restrictOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petani');
    }
};