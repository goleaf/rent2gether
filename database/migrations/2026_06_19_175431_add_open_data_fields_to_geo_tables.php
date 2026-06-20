<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addCountryOpenDataFields();
        $this->addCityOpenDataFields();
        $this->addGeoSearchIndexes();
    }

    public function down(): void
    {
        $this->dropGeoSearchIndexes();

        Schema::table('cities', function (Blueprint $table): void {
            $columns = collect([
                'geoname_id',
                'ascii_name',
                'alternate_names',
                'feature_class',
                'feature_code',
                'status',
            ])->filter(fn (string $column): bool => Schema::hasColumn('cities', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('countries', function (Blueprint $table): void {
            $columns = collect([
                'iso2',
                'name_en',
                'name_ru',
                'name_native',
                'timezone_default',
                'status',
            ])->filter(fn (string $column): bool => Schema::hasColumn('countries', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    private function addCountryOpenDataFields(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            if (! Schema::hasColumn('countries', 'iso2')) {
                $table->string('iso2', 2)->nullable()->after('id');
            }

            if (! Schema::hasColumn('countries', 'name_en')) {
                $table->string('name_en')->nullable()->after('iso3');
            }

            if (! Schema::hasColumn('countries', 'name_ru')) {
                $table->string('name_ru')->nullable()->after('name_en');
            }

            if (! Schema::hasColumn('countries', 'name_native')) {
                $table->string('name_native')->nullable()->after('name_ru');
            }

            if (! Schema::hasColumn('countries', 'timezone_default')) {
                $table->string('timezone_default')->nullable()->after('phone_code');
            }

            if (! Schema::hasColumn('countries', 'status')) {
                $table->string('status')->default('active')->after('timezone_default');
            }
        });
    }

    private function addCityOpenDataFields(): void
    {
        Schema::table('cities', function (Blueprint $table): void {
            if (! Schema::hasColumn('cities', 'geoname_id')) {
                $table->unsignedBigInteger('geoname_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('cities', 'ascii_name')) {
                $table->string('ascii_name')->nullable()->after('name');
            }

            if (! Schema::hasColumn('cities', 'alternate_names')) {
                $table->text('alternate_names')->nullable()->after('ascii_name');
            }

            if (! Schema::hasColumn('cities', 'feature_class')) {
                $table->string('feature_class', 1)->nullable()->after('timezone');
            }

            if (! Schema::hasColumn('cities', 'feature_code')) {
                $table->string('feature_code', 24)->nullable()->after('feature_class');
            }

            if (! Schema::hasColumn('cities', 'status')) {
                $table->string('status')->default('active')->after('feature_code');
            }
        });
    }

    private function addGeoSearchIndexes(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            if (! Schema::hasIndex('countries', 'countries_iso2_unique')) {
                $table->unique('iso2', 'countries_iso2_unique');
            }

            if (! Schema::hasIndex('countries', ['status', 'name_normalized'])) {
                $table->index(['status', 'name_normalized'], 'countries_status_name_normalized_index');
            }
        });

        Schema::table('cities', function (Blueprint $table): void {
            if (! Schema::hasIndex('cities', 'cities_geoname_id_unique')) {
                $table->unique('geoname_id', 'cities_geoname_id_unique');
            }

            if (! Schema::hasIndex('cities', ['status', 'name_normalized', 'population'])) {
                $table->index(['status', 'name_normalized', 'population'], 'cities_status_name_population_index');
            }

            if (! Schema::hasIndex('cities', ['country_id', 'status'])) {
                $table->index(['country_id', 'status'], 'cities_country_status_index');
            }
        });
    }

    private function dropGeoSearchIndexes(): void
    {
        Schema::table('cities', function (Blueprint $table): void {
            if (Schema::hasIndex('cities', 'cities_country_status_index')) {
                $table->dropIndex('cities_country_status_index');
            }

            if (Schema::hasIndex('cities', 'cities_status_name_population_index')) {
                $table->dropIndex('cities_status_name_population_index');
            }

            if (Schema::hasIndex('cities', 'cities_geoname_id_unique')) {
                $table->dropUnique('cities_geoname_id_unique');
            }
        });

        Schema::table('countries', function (Blueprint $table): void {
            if (Schema::hasIndex('countries', 'countries_status_name_normalized_index')) {
                $table->dropIndex('countries_status_name_normalized_index');
            }

            if (Schema::hasIndex('countries', 'countries_iso2_unique')) {
                $table->dropUnique('countries_iso2_unique');
            }
        });
    }
};
