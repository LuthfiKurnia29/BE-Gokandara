<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('detail_skema_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skema_pembayaran_id')->constrained('skema_pembayarans')->onDelete('cascade');
            $table->string('nama');
            $table->double('persentase');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('detail_skema_pembayarans');
    }
};
