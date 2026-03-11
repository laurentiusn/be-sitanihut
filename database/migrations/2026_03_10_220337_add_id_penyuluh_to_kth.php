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
        Schema::table('kth', function (Blueprint $table) {
            $table->foreignUuid('id_penyuluh')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kth', function (Blueprint $table) {
            $table->dropForeign(['id_penyuluh']);
            $table->dropColumn('id_penyuluh');
        });
    }
};