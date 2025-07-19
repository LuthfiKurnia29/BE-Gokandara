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
            $table->string('name');
            $table->string('address');
            $table->string('ktp_number');
            $table->string('phone');
            $table->string('email');
            $table->string('description')->nullable();
            $table->float('kesiapan_dana')->nullable();
            $table->string('pengalaman')->nullable();
            $table->string('materi_fu')->nullable();
            $table->date('tgl_fu')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projeks')->nullOnDelete();
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
