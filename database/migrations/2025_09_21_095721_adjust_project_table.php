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
        Schema::table('projeks', function (Blueprint $table) {
            $table->string('kavling_total')->nullable();
            $table->string('address')->nullable();
        });
        
        Schema::table('tipes', function (Blueprint $table) {
            $table->foreignId('projeks_id')->nullable();
            $table->string('luas_tanah')->nullable();
            $table->string('luas_bangunan')->nullable();
            $table->integer('jumlah_unit')->nullable();
            $table->integer('jenis_pembayaran')->nullable();
            $table->string('harga')->nullable();
        });

        Schema::table('fasilitas', function (Blueprint $table) {
            $table->string('luas_fasilitas')->nullable();
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
