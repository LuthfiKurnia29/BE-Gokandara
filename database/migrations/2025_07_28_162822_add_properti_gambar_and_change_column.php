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
        Schema::table('properti__gambars', function (Blueprint $table) {
            $table->dropColumn('path');
            $table->dropColumn('size');
            $table->dropColumn('type');
        });

        Schema::table('properti__gambars', function (Blueprint $table) {
            $table->foreignId('properti_id')->reference('propertis')->onDelete('cascade');
            $table->string('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properti__gambars', function (Blueprint $table) {
            $table->dropColumn('properti_id');
            $table->dropColumn('image');
        });

        Schema::table('properti__gambars', function (Blueprint $table) {
            $table->string('path');
            $table->string('size');
            $table->string('type');
        });
    }
};
