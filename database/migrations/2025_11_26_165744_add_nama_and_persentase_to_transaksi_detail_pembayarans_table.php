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
        Schema::table('transaksi_detail_pembayarans', function (Blueprint $table) {
            $table->string('nama');
            $table->double('persentase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_detail_pembayarans', function (Blueprint $table) {
            $table->dropColumn(['nama', 'persentase']);
        });
    }
};
