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
        //
        Schema::table('transaksis', function (Blueprint $table) {
            $table->foreignId('projeks_id')->nullable();
            $table->integer('kelebihan_tanah')->nullable();
            $table->integer('harga_per_meter')->nullable();
            $table->integer('kavling_dipesan')->nullable();
        });
        Schema::table('tipes', function (Blueprint $table) {
            $table->integer('jumlah_unit')->nullable();
            $table->integer('unit_terjual')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
