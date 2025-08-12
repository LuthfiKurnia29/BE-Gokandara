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
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerima_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chat_id')->nullable()->constrained('chattings')->onDelete('cascade');
            $table->foreignId('konsumen_id')->nullable()->constrained('konsumens')->onDelete('cascade');
            $table->string('jenis_notifikasi');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
