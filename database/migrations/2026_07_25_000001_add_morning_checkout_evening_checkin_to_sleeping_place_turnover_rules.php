<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sleeping_place_turnover_rules', function (Blueprint $table): void {
            if (! Schema::hasColumn('sleeping_place_turnover_rules', 'morning_checkout_evening_checkin_allowed')) {
                $table->boolean('morning_checkout_evening_checkin_allowed')
                    ->default(true)
                    ->after('same_day_turnover_allowed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sleeping_place_turnover_rules', function (Blueprint $table): void {
            if (Schema::hasColumn('sleeping_place_turnover_rules', 'morning_checkout_evening_checkin_allowed')) {
                $table->dropColumn('morning_checkout_evening_checkin_allowed');
            }
        });
    }
};
