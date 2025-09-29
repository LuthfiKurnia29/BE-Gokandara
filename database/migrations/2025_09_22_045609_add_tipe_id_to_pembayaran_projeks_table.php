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
        Schema::table('pembayaran_projeks', function (Blueprint $table) {
            $table->unsignedBigInteger('tipe_id')->nullable()->after('projek_id');
            $table->foreign('tipe_id')->references('id')->on('tipes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_projeks', function (Blueprint $table) {
            $table->dropForeign(['tipe_id']);
            $table->dropColumn('tipe_id');
        });
    }
};
