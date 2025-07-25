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
        Schema::table('propertis', function (Blueprint $table) {
            $table->dropColumn('blok_id');
            $table->dropColumn('unit_id');
            $table->dropColumn('tipe_id');
        });

        Schema::table('transaksis', function (Blueprint $table) {
            $table->foreignId('blok_id')->reference('bloks')->onDelete('cascade');
            $table->foreignId('unit_id')->reference('units')->onDelete('cascade');
            $table->foreignId('tipe_id')->reference('tipes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->foreignId('blok_id')->reference('bloks')->onDelete('cascade');
            $table->foreignId('unit_id')->reference('units')->onDelete('cascade');
            $table->foreignId('tipe_id')->reference('tipes')->onDelete('cascade');
        });

        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('blok_id');
            $table->dropColumn('unit_id');
            $table->dropColumn('tipe_id');
        });
    }
};
