<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('properti_bloks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('properti_id')->reference('propertis')->onDelete('cascade');
            $table->foreignId('blok_id')->reference('bloks')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('properti_bloks');
    }
};
