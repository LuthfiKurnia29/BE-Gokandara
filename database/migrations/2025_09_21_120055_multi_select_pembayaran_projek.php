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
        Schema::create('pembayaran_projeks', function (Blueprint $table) {
            $table->foreignId('projek_id')->constrained('projeks')->onDelete('cascade');
            $table->foreignId('skema_pembayaran_id')->constrained('skema_pembayarans')->onDelete('cascade');
            $table->timestamps();
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
