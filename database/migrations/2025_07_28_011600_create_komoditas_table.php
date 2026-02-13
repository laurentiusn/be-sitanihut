<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komoditas', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('kode', 10)->unique();
            $table->string('nama_komoditas', 50);
            $table->string('jenis_komoditas', 30);
            $table->decimal('nte', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komoditas');
    }
};