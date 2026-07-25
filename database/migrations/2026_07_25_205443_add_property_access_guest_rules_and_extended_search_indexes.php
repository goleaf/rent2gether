<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_access_details') && ! Schema::hasColumn('property_access_details', 'guest_rules_enabled')) {
            Schema::table('property_access_details', function (Blueprint $table): void {
                $table->boolean('guest_rules_enabled')->nullable()->after('guest_visitors_need_approval');
            });
        }

        foreach ($this->indexes() as $tableName => $indexes) {
            $this->addIndexes($tableName, $indexes);
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes()) as $tableName => $indexes) {
            $this->dropIndexes($tableName, array_keys($indexes));
        }

        if (Schema::hasTable('property_access_details') && Schema::hasColumn('property_access_details', 'guest_rules_enabled')) {
            Schema::table('property_access_details', function (Blueprint $table): void {
                $table->dropColumn('guest_rules_enabled');
            });
        }
    }

    /**
     * @return array<string, array<string, list<string>>>
     */
    private function indexes(): array
    {
        return [
            'property_location_details' => [
                'property_location_nearest_metro_idx' => ['nearest_metro', 'property_id'],
                'property_location_nearest_bus_idx' => ['nearest_bus_stop', 'property_id'],
                'property_location_nearest_shop_idx' => ['nearest_shop', 'property_id'],
                'property_location_nearest_pharmacy_idx' => ['nearest_pharmacy', 'property_id'],
                'property_location_nearest_hospital_idx' => ['nearest_hospital', 'property_id'],
                'property_location_nearest_university_idx' => ['nearest_university', 'property_id'],
                'property_location_nearest_railway_idx' => ['nearest_railway_station', 'property_id'],
                'property_location_nearest_airport_idx' => ['nearest_airport', 'property_id'],
                'property_location_distance_center_idx' => ['distance_to_center_meters', 'property_id'],
                'property_location_walk_center_idx' => ['walk_minutes_to_center', 'property_id'],
                'property_location_transport_center_idx' => ['transport_minutes_to_center', 'property_id'],
                'property_location_transport_level_idx' => ['transport_convenience_level', 'property_id'],
                'property_location_noise_level_idx' => ['district_noise_level', 'property_id'],
                'property_location_safety_level_idx' => ['district_safety_level', 'property_id'],
                'property_location_lighting_level_idx' => ['street_lighting_level', 'property_id'],
                'property_location_free_parking_idx' => ['has_free_parking', 'property_id'],
                'property_location_paid_parking_idx' => ['has_paid_parking', 'property_id'],
            ],
            'property_condition_details' => [
                'property_condition_cleanliness_idx' => ['cleanliness_level', 'property_id'],
                'property_condition_humidity_idx' => ['humidity_level', 'property_id'],
                'property_condition_winter_temp_idx' => ['winter_temperature_level', 'property_id'],
                'property_condition_summer_temp_idx' => ['summer_temperature_level', 'property_id'],
                'property_condition_indoor_noise_idx' => ['indoor_noise_level', 'property_id'],
                'property_condition_light_level_idx' => ['light_level', 'property_id'],
                'property_condition_insects_idx' => ['has_insects', 'property_id'],
                'property_condition_mold_idx' => ['has_mold', 'property_id'],
            ],
            'property_access_details' => [
                'property_access_guest_rules_idx' => ['guest_rules_enabled', 'property_id'],
                'property_access_courier_rules_idx' => ['courier_rules_enabled', 'property_id'],
                'property_access_delivery_idx' => ['delivery_allowed', 'property_id'],
                'property_access_door_code_idx' => ['has_door_code', 'property_id'],
                'property_access_electronic_lock_idx' => ['has_electronic_lock', 'property_id'],
                'property_access_key_safe_idx' => ['has_key_safe', 'property_id'],
                'property_access_24_7_idx' => ['access_24_7', 'property_id'],
                'property_access_night_restrictions_idx' => ['has_night_entry_restrictions', 'property_id'],
                'property_access_self_checkin_idx' => ['self_check_in_available', 'property_id'],
            ],
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
