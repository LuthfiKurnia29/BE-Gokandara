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
        // Schema::table('konsumens', function (Blueprint $table) {
        //     $table->dropColumn('materi_fu');
        //     $table->dropColumn('tgl_fu');
        // });

         Schema::table('konsumens', function (Blueprint $table) {
            $table->string('materi_fu_1');
            $table->string('materi_fu_2');
            $table->dateTime('tgl_fu_1');
            $table->dateTime('tgl_fu_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropColumn('materi_fu_1');
            $table->dropColumn('materi_fu_2');
            $table->dropColumn('tgl_fu_1');
            $table->dropColumn('tgl_fu_2');
        });

        Schema::table('konsumens', function (Blueprint $table) {
            $table->string('materi_fu')->nullable();
            $table->date('tgl_fu')->nullable();
        });
    }
};
