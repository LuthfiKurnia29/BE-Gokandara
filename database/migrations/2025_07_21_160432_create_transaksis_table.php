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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsumen_id')->reference('konsumens')->onDelete('cascade');
            $table->foreignId('properti_id')->reference('propertis')->onDelete('cascade');
            $table->foreignId('blok_id')->reference('bloks')->onDelete('cascade');
            $table->foreignId('unit_id')->reference('units')->onDelete('cascade');
            $table->foreignId('tipe_id')->reference('tipes')->onDelete('cascade');
            $table->double('diskon')->nullable();
            $table->integer('grand_total');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
