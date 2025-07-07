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
        Schema::create('propertis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('Project_Id')->reference('projects')->onDelete('cascade');
            $table->foreignId('Blok_Id')->reference('bloks')->onDelete('cascade');
            $table->foreignId('Unit_Id')->reference('units')->onDelete('cascade');
            $table->foreignId('Tipe_Id')->reference('tipes')->onDelete('cascade');
            $table->string('Luas_Bangunan');
            $table->string('Luas_Tanah');
            $table->string('Kelebihan');
            $table->string('Lokasi');
            $table->float('Harga');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propertis');
    }
};
