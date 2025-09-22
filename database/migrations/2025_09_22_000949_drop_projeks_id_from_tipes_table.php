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
        Schema::table('tipes', function (Blueprint $table) {
            if (Schema::hasColumn('tipes', 'projeks_id')) {
                $table->dropColumn('projeks_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipes', function (Blueprint $table) {
            if (!Schema::hasColumn('tipes', 'projeks_id')) {
                $table->foreignId('projeks_id')->nullable()->constrained('projeks')->onDelete('cascade');
            }
        });
    }
};
