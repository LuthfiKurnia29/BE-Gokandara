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
        if (!Schema::hasColumn('tipes', 'luas_tanah')) {
            Schema::table('tipes', function (Blueprint $table) {
                $table->string('luas_tanah')->nullable();
            });
        }
        if (!Schema::hasColumn('tipes', 'luas_bangunan')) {
            Schema::table('tipes', function (Blueprint $table) {
                $table->string('luas_bangunan')->nullable();
            });
        }
        if (!Schema::hasColumn('tipes', 'harga')) {
            Schema::table('tipes', function (Blueprint $table) {
                $table->string('harga')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tipes', 'luas_tanah')) {
            Schema::table('tipes', function (Blueprint $table) {
                $table->dropColumn('luas_tanah');
            });
        }
        if (Schema::hasColumn('tipes', 'luas_bangunan')) {
            Schema::table('tipes', function (Blueprint $table) {
                $table->dropColumn('luas_bangunan');
            });
        }
        if (Schema::hasColumn('tipes', 'harga')) {
            Schema::table('tipes', function (Blueprint $table) {
                $table->dropColumn('harga');
            });
        }
    }
};
