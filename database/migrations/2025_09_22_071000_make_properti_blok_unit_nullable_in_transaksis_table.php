<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `transaksis` MODIFY `properti_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `transaksis` MODIFY `blok_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `transaksis` MODIFY `unit_id` BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `transaksis` MODIFY `properti_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `transaksis` MODIFY `blok_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `transaksis` MODIFY `unit_id` BIGINT UNSIGNED NOT NULL');
    }
};