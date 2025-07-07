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
        Schema::create('konsumens', function (Blueprint $table) {
            $table->id();
            $table->string('Nama');
            $table->string('Alamat');
            $table->string('No_KTP');
            $table->string('No_HP');
            $table->string('Email');
            $table->float('Kesiapan_dana');
            $table->string('Pengalaman');
            $table->string('Materi_Fu');
            $table->date('Tgl_Fu');
            $table->foreignId('Prospek_Id')->reference('prospeks')->onDelete('cascade');
            $table->foreignId('Projek_Id')->reference('projeks')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konsumens');
    }
};
