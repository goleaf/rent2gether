<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->indexes() as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes): void {
                foreach ($indexes as $columns) {
                    if ($this->columnsExist($table, $columns) && ! Schema::hasIndex($table, $columns)) {
                        $blueprint->index($columns);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes()) as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes): void {
                foreach (array_reverse($indexes) as $columns) {
                    if ($this->columnsExist($table, $columns) && Schema::hasIndex($table, $columns)) {
                        $blueprint->dropIndex($columns);
                    }
                }
            });
        }
    }

    /**
     * @return array<string, list<list<string>>>
     */
    private function indexes(): array
    {
        return [
            'properties' => [
                ['status', 'type'],
                ['status', 'distance_to_center_meters'],
                ['status', 'has_elevator'],
                ['status', 'has_parking'],
            ],
            'rooms' => [
                ['status', 'type'],
                ['status', 'gender_policy'],
                ['status', 'max_guests'],
            ],
            'sleeping_places' => [
                ['status', 'currency', 'base_price_per_night'],
                ['status', 'type', 'base_price_per_night'],
                ['status', 'max_guests'],
                ['status', 'instant_booking_enabled'],
                ['status', 'requires_host_approval'],
                ['status', 'deposit_amount'],
                ['status', 'has_locker'],
                ['status', 'has_bedding'],
                ['status', 'has_towel'],
            ],
            'host_profiles' => [
                ['user_id', 'rating_average'],
                ['rating_average', 'reviews_count'],
                ['verified_at'],
                ['default_cancellation_policy'],
            ],
        ];
    }

    /**
     * @param  list<string>  $columns
     */
    private function columnsExist(string $table, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }
};
