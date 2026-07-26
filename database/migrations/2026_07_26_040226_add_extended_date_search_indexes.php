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
        $this->addIndexes('sleeping_places', $this->sleepingPlaceIndexes());
        $this->addIndexes('sleeping_place_calendar_settings', $this->calendarSettingIndexes());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexes('sleeping_place_calendar_settings', $this->calendarSettingIndexes());
        $this->dropIndexes('sleeping_places', $this->sleepingPlaceIndexes());
    }

    /**
     * @return array<string, list<string>>
     */
    private function sleepingPlaceIndexes(): array
    {
        return [
            'sleeping_places_status_min_max_nights_idx' => ['status', 'min_nights', 'max_nights'],
            'sleeping_places_status_can_extend_idx' => ['status', 'can_extend'],
            'sleeping_places_status_early_checkin_idx' => ['status', 'early_check_in_allowed'],
            'sleeping_places_status_late_checkout_idx' => ['status', 'late_check_out_allowed'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function calendarSettingIndexes(): array
    {
        return [
            'sp_calendar_settings_can_extend_place_idx' => ['can_extend', 'sleeping_place_id'],
            'sp_calendar_settings_checkin_until_place_idx' => ['check_in_time_until', 'sleeping_place_id'],
            'sp_calendar_settings_checkout_until_place_idx' => ['check_out_time_until', 'sleeping_place_id'],
            'sp_calendar_settings_checkin_from_place_idx' => ['check_in_time_from', 'sleeping_place_id'],
            'sp_calendar_settings_earliest_checkin_place_idx' => ['earliest_check_in_time', 'sleeping_place_id'],
            'sp_calendar_settings_latest_checkout_place_idx' => ['latest_check_out_time', 'sleeping_place_id'],
        ];
    }

    /**
     * @param  array<string, list<string>>  $indexes
     */
    private function addIndexes(string $tableName, array $indexes): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach ($indexes as $indexName => $columns) {
            if ($this->hasAllColumns($tableName, $columns) && ! Schema::hasIndex($tableName, $indexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
                    $table->index($columns, $indexName);
                });
            }
        }
    }

    /**
     * @param  array<string, list<string>>  $indexes
     */
    private function dropIndexes(string $tableName, array $indexes): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach (array_keys($indexes) as $indexName) {
            if (Schema::hasIndex($tableName, $indexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
                    $table->dropIndex($indexName);
                });
            }
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function hasAllColumns(string $tableName, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return false;
            }
        }

        return true;
    }
};
