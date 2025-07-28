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
            $table->dateTime('followup_date')->nullable();
            $table->string('followup_note')->nullable();
            $table->string('followup_result')->nullable();
            
            $table->foreignId('sales_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->foreignId('konsumen_id')
                  ->constrained('konsumens')
                  ->onDelete('cascade');
            $table->dateTime('followup_last_day')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('followup_monitorings', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['sales_id']);
            $table->dropForeign(['konsumen_id']);

            // Hapus kolom
            $table->dropColumn([
                'first_date',
                'last_date',
                'followup_date',
                'followup_note',
                'followup_result',
                'sales_id',
                'konsumen_id'
            ]);
        });
    }
};
