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
        $this->addIndexes('rooms', $this->roomIndexes());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexes('rooms', array_keys($this->roomIndexes()));
    }

    /**
     * @return array<string, list<string>>
     */
    private function roomIndexes(): array
    {
        return [
            'rooms_search_type_idx' => ['status', 'type'],
            'rooms_search_room_type_idx' => ['status', 'room_type'],
            'rooms_search_gender_policy_idx' => ['status', 'gender_policy'],
            'rooms_search_gender_type_idx' => ['status', 'gender_type'],
            'rooms_search_private_idx' => ['status', 'is_private'],
            'rooms_search_shared_idx' => ['status', 'is_shared'],
            'rooms_search_living_format_idx' => ['status', 'living_format'],
            'rooms_search_one_person_idx' => ['status', 'is_for_one_person'],
            'rooms_search_long_stay_idx' => ['status', 'is_for_long_stay'],
            'rooms_search_capacity_idx' => ['status', 'capacity'],
            'rooms_search_max_guests_idx' => ['status', 'max_guests'],
            'rooms_search_window_idx' => ['status', 'has_window'],
            'rooms_search_windows_count_idx' => ['status', 'windows_count'],
            'rooms_search_lock_idx' => ['status', 'has_lock'],
            'rooms_search_lockable_door_idx' => ['status', 'has_lockable_door'],
            'rooms_search_room_key_idx' => ['status', 'has_room_key'],
            'rooms_search_air_conditioning_idx' => ['status', 'has_air_conditioning'],
            'rooms_search_ac_idx' => ['status', 'has_ac'],
            'rooms_search_heating_idx' => ['status', 'has_heating'],
            'rooms_search_desk_idx' => ['status', 'has_desk'],
            'rooms_search_wardrobe_idx' => ['status', 'has_wardrobe'],
            'rooms_search_lockers_idx' => ['status', 'has_lockers'],
            'rooms_search_balcony_idx' => ['status', 'has_balcony'],
            'rooms_search_noise_level_idx' => ['status', 'noise_level'],
            'rooms_search_pass_through_idx' => ['status', 'is_pass_through'],
            'rooms_search_light_level_idx' => ['status', 'light_level'],
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

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexes): void {
            foreach ($indexes as $indexName => $columns) {
                if ($this->columnsExist($tableName, $columns) && ! Schema::hasIndex($tableName, $columns) && ! Schema::hasIndex($tableName, $indexName)) {
                    $table->index($columns, $indexName);
                }
            }
        });
    }

    /**
     * @param  list<string>  $indexNames
     */
    private function dropIndexes(string $tableName, array $indexNames): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexNames): void {
            foreach ($indexNames as $indexName) {
                if (Schema::hasIndex($tableName, $indexName)) {
                    $table->dropIndex($indexName);
                }
            }
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function columnsExist(string $tableName, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return false;
            }
        }

        return true;
    }
};
