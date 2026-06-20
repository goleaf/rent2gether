<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sleeping_places', function (Blueprint $table): void {
            if (! Schema::hasColumn('sleeping_places', 'display_name')) {
                $table->string('display_name')->nullable()->after('place_number');
            }

            if (! Schema::hasColumn('sleeping_places', 'has_hook')) {
                $table->boolean('has_hook')->default(false)->after('has_shelf');
            }

            if (! Schema::hasColumn('sleeping_places', 'near_window')) {
                $table->boolean('near_window')->default(false)->after('has_luggage_space');
            }

            if (! Schema::hasColumn('sleeping_places', 'near_door')) {
                $table->boolean('near_door')->default(false)->after('near_window');
            }

            if (! Schema::hasColumn('sleeping_places', 'near_radiator')) {
                $table->boolean('near_radiator')->default(false)->after('near_door');
            }

            if (! Schema::hasColumn('sleeping_places', 'near_air_conditioner')) {
                $table->boolean('near_air_conditioner')->default(false)->after('near_radiator');
            }

            if (! Schema::hasColumn('sleeping_places', 'privacy_level')) {
                $table->string('privacy_level')->nullable()->after('near_air_conditioner');
            }

            if (! Schema::hasColumn('sleeping_places', 'noise_level')) {
                $table->string('noise_level')->nullable()->after('privacy_level');
            }

            if (! Schema::hasColumn('sleeping_places', 'suitable_for_limited_mobility')) {
                $table->boolean('suitable_for_limited_mobility')->default(false)->after('suitable_for_elderly');
            }
        });

        Schema::table('sleeping_place_translations', function (Blueprint $table): void {
            if (! Schema::hasColumn('sleeping_place_translations', 'special_conditions')) {
                $table->text('special_conditions')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sleeping_place_translations', function (Blueprint $table): void {
            if (Schema::hasColumn('sleeping_place_translations', 'special_conditions')) {
                $table->dropColumn('special_conditions');
            }
        });

        Schema::table('sleeping_places', function (Blueprint $table): void {
            foreach ([
                'display_name',
                'has_hook',
                'near_window',
                'near_door',
                'near_radiator',
                'near_air_conditioner',
                'privacy_level',
                'noise_level',
                'suitable_for_limited_mobility',
            ] as $column) {
                if (Schema::hasColumn('sleeping_places', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
