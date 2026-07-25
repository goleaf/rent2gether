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
                ['status', 'repair_state'],
                ['status', 'floor'],
                ['status', 'floors_count'],
                ['status', 'balconies_count'],
            ],
            'property_condition_details' => [
                ['repair_state', 'property_id'],
            ],
            'property_access_details' => [
                ['entrance_type', 'property_id'],
                ['has_private_entrance', 'property_id'],
                ['has_shared_entrance', 'property_id'],
            ],
            'rooms' => [
                ['status', 'has_balcony'],
                ['status', 'window_view'],
                ['status', 'noise_level'],
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
