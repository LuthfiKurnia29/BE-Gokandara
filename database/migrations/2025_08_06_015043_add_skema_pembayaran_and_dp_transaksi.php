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
        Schema::table('transaksis', function (Blueprint $table) {
            $table->enum('skema_pembayaran', ['Cash Keras', 'Cash Tempo', 'Kredit']);
            $table->integer('dp')->nullable();
            $table->integer('jangka_waktu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('skema_pembayaran');
            $table->dropColumn('dp');
        });
    }
};
