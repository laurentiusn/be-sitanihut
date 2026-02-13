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
        Schema::create('user_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_user', 30);
            $table->string('nomor_induk', 20);
            $table->enum('jenis_kelamin', ['pria', 'wanita']);
            $table->string('profile_picture')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nomor_wa', 20)->nullable();
            $table->string('pendidikan_terakhir', 10)->nullable();
            $table->string('pekerjaan_sampingan', 20)->nullable();
            $table->float('luas_lahan')->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->foreignUuid('id_user')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
