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
            $table->foreignId('created_id')->reference('users')->onDelete('cascade');
            $table->foreignId('updated_id')->reference('users')->onDelete('cascade');
        });

        Schema::table('konsumens', function (Blueprint $table) {
            $table->foreignId('created_id')->reference('users')->onDelete('cascade');
            $table->foreignId('updated_id')->reference('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('created_id');
            $table->dropColumn('updated_id');
        });

        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropColumn('created_id');
            $table->dropColumn('updated_id');
        });
    }
};
