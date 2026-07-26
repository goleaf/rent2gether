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
        Schema::table('compatibility_results', function (Blueprint $table): void {
            $table->index(
                ['user_id', 'check_in_date', 'check_out_date', 'fit_status', 'sleeping_place_id'],
                'compat_results_user_dates_fit_place_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compatibility_results', function (Blueprint $table): void {
            $table->dropIndex('compat_results_user_dates_fit_place_idx');
        });
    }
};
