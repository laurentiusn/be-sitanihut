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
        Schema::create('kph', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_kph', 75);
            $table->string('wilayah', 75);
            $table->text('alamat');
            $table->string('nomor_wa', 20)->unique();
            $table->string('jenis', 25);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kph');
    }
};
