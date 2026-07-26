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
        if (Schema::hasTable('property_location_details')) {
            $columns = [
                'nearest_landmark' => fn (Blueprint $table) => $table->string('nearest_landmark')->nullable()->after('nearest_coworking'),
                'near_work_area' => fn (Blueprint $table) => $table->boolean('near_work_area')->nullable()->after('nearest_landmark'),
                'near_sea' => fn (Blueprint $table) => $table->boolean('near_sea')->nullable()->after('near_work_area'),
                'near_nightlife' => fn (Blueprint $table) => $table->boolean('near_nightlife')->nullable()->after('near_sea'),
                'area_residential' => fn (Blueprint $table) => $table->boolean('area_residential')->nullable()->after('near_nightlife'),
                'area_city_center' => fn (Blueprint $table) => $table->boolean('area_city_center')->nullable()->after('area_residential'),
                'area_suburb' => fn (Blueprint $table) => $table->boolean('area_suburb')->nullable()->after('area_city_center'),
                'area_industrial' => fn (Blueprint $table) => $table->boolean('area_industrial')->nullable()->after('area_suburb'),
                'area_tourist' => fn (Blueprint $table) => $table->boolean('area_tourist')->nullable()->after('area_industrial'),
                'area_students' => fn (Blueprint $table) => $table->boolean('area_students')->nullable()->after('area_tourist'),
                'area_workers' => fn (Blueprint $table) => $table->boolean('area_workers')->nullable()->after('area_students'),
                'area_long_stay' => fn (Blueprint $table) => $table->boolean('area_long_stay')->nullable()->after('area_workers'),
            ];

            foreach ($columns as $column => $definition) {
                if (! Schema::hasColumn('property_location_details', $column)) {
                    Schema::table('property_location_details', function (Blueprint $table) use ($definition): void {
                        $definition($table);
                    });
                }
            }

            foreach ($this->propertyLocationIndexes() as $name => $columns) {
                if (! Schema::hasIndex('property_location_details', $name)) {
                    Schema::table('property_location_details', function (Blueprint $table) use ($columns, $name): void {
                        $table->index($columns, $name);
                    });
                }
            }
        }

        if (Schema::hasTable('properties')) {
            foreach ($this->propertyIndexes() as $name => $columns) {
                if (! Schema::hasIndex('properties', $name)) {
                    Schema::table('properties', function (Blueprint $table) use ($columns, $name): void {
                        $table->index($columns, $name);
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('properties')) {
            foreach (array_keys($this->propertyIndexes()) as $index) {
                if (Schema::hasIndex('properties', $index)) {
                    Schema::table('properties', function (Blueprint $table) use ($index): void {
                        $table->dropIndex($index);
                    });
                }
            }
        }

        if (! Schema::hasTable('property_location_details')) {
            return;
        }

        foreach (array_keys($this->propertyLocationIndexes()) as $index) {
            if (Schema::hasIndex('property_location_details', $index)) {
                Schema::table('property_location_details', function (Blueprint $table) use ($index): void {
                    $table->dropIndex($index);
                });
            }
        }

        $columns = [
            'nearest_landmark',
            'near_work_area',
            'near_sea',
            'near_nightlife',
            'area_residential',
            'area_city_center',
            'area_suburb',
            'area_industrial',
            'area_tourist',
            'area_students',
            'area_workers',
            'area_long_stay',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('property_location_details', $column)) {
                Schema::table('property_location_details', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }

    /**
     * @return array<string, list<string>>
     */
    private function propertyLocationIndexes(): array
    {
        return [
            'property_location_landmark_idx' => ['nearest_landmark', 'property_id'],
            'property_location_nearest_train_station_idx' => ['nearest_train_station', 'property_id'],
            'property_location_nearest_park_idx' => ['nearest_park', 'property_id'],
            'property_location_nearest_mall_idx' => ['nearest_mall', 'property_id'],
            'property_location_nearest_gym_idx' => ['nearest_gym', 'property_id'],
            'property_location_nearest_coworking_idx' => ['nearest_coworking', 'property_id'],
            'property_location_near_work_area_idx' => ['near_work_area', 'property_id'],
            'property_location_near_sea_idx' => ['near_sea', 'property_id'],
            'property_location_near_nightlife_idx' => ['near_nightlife', 'property_id'],
            'property_location_area_residential_idx' => ['area_residential', 'property_id'],
            'property_location_area_city_center_idx' => ['area_city_center', 'property_id'],
            'property_location_area_suburb_idx' => ['area_suburb', 'property_id'],
            'property_location_area_industrial_idx' => ['area_industrial', 'property_id'],
            'property_location_area_tourist_idx' => ['area_tourist', 'property_id'],
            'property_location_area_students_idx' => ['area_students', 'property_id'],
            'property_location_area_workers_idx' => ['area_workers', 'property_id'],
            'property_location_area_long_stay_idx' => ['area_long_stay', 'property_id'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function propertyIndexes(): array
    {
        return [
            'properties_country_city_status_idx' => ['country_id', 'city_id', 'status'],
            'properties_city_street_idx' => ['city_id', 'street'],
            'properties_city_street_name_idx' => ['city_id', 'street_name'],
        ];
    }
};
