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
        Schema::table('tipes', function (Blueprint $table) {
            $table->integer('luas_tanah')->nullable()->change();
            $table->integer('luas_bangunan')->nullable()->change();
            $table->unsignedBigInteger('harga')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipes', function (Blueprint $table) {
            $table->string('luas_tanah')->nullable()->change();
            $table->string('luas_bangunan')->nullable()->change();
            $table->string('harga')->nullable()->change();
        });
    }
};
