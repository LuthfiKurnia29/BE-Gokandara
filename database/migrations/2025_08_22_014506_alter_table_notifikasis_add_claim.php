<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->foreignId('target_id')->nullable()->constrained('targets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('target_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('target_id');
            $table->dropColumn('user_id');
        });
    }
};
