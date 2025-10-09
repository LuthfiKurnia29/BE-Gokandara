<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('projeks', function (Blueprint $table) {
            $table->integer('kamar_tidur')->default(0);
            $table->integer('kamar_mandi')->default(0);
            $table->boolean('wifi')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('projeks', function (Blueprint $table) {
            $table->dropColumn('kamar_tidur');
            $table->dropColumn('kamar_mandi');
            $table->dropColumn('wifi');
        });
    }
};
