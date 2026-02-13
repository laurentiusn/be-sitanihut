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
        Schema::create('kth', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_kth', 75);
            $table->string('desa', 40);
            $table->string('kecamatan', 30);
            $table->string('kabupaten', 30);
            $table->string('koordinator', 35);
            $table->string('nomor_wa', 20);
            $table->foreignUuid('id_kph')->constrained('kph')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kth');
    }
};
