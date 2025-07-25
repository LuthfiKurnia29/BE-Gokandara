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
            $table->foreignId('project_id')->reference('projects')->onDelete('cascade');
            $table->foreignId('blok_id')->reference('bloks')->onDelete('cascade');
            $table->foreignId('unit_id')->reference('units')->onDelete('cascade');
            $table->foreignId('tipe_id')->reference('tipes')->onDelete('cascade');
            $table->string('luas_bangunan');
            $table->string('luas_tanah');
            $table->string('kelebihan');
            $table->string('lokasi');
            $table->float('harga');
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
