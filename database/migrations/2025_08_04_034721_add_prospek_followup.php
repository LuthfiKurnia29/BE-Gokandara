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
        Schema::table('followup_monitorings', function (Blueprint $table) {
            $table->foreignId('prospek_id')
                ->constrained('prospeks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('followup_monitorings', function (Blueprint $table) {
            $table->dropColumn('prospek_id');
        });
    }
};
