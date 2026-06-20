<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            if (! Schema::hasColumn('countries', 'geoname_id')) {
                $table->unsignedBigInteger('geoname_id')->nullable()->after('id');
            }

            if (! Schema::hasIndex('countries', 'countries_geoname_id_unique')) {
                $table->unique('geoname_id', 'countries_geoname_id_unique');
            }
        });

        Schema::create('country_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 16);
            $table->string('name');
            $table->string('name_normalized');
            $table->string('source')->default('geonames');
            $table->string('source_id')->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->boolean('is_short')->default(false);
            $table->boolean('is_colloquial')->default(false);
            $table->boolean('is_historic')->default(false);
            $table->string('valid_from')->nullable();
            $table->string('valid_to')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'locale', 'name_normalized'], 'country_translations_country_locale_name_unique');
            $table->index(['country_id', 'locale'], 'country_translations_country_locale_index');
            $table->index(['locale', 'name_normalized'], 'country_translations_locale_name_index');
            $table->index('name_normalized', 'country_translations_name_index');
            $table->index(['source', 'source_id'], 'country_translations_source_index');
        });

        Schema::create('city_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 16);
            $table->string('name');
            $table->string('name_normalized');
            $table->string('source')->default('geonames');
            $table->string('source_id')->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->boolean('is_short')->default(false);
            $table->boolean('is_colloquial')->default(false);
            $table->boolean('is_historic')->default(false);
            $table->string('valid_from')->nullable();
            $table->string('valid_to')->nullable();
            $table->timestamps();

            $table->unique(['city_id', 'locale', 'name_normalized'], 'city_translations_city_locale_name_unique');
            $table->index(['city_id', 'locale'], 'city_translations_city_locale_index');
            $table->index(['locale', 'name_normalized'], 'city_translations_locale_name_index');
            $table->index('name_normalized', 'city_translations_name_index');
            $table->index(['source', 'source_id'], 'city_translations_source_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_translations');
        Schema::dropIfExists('country_translations');

        Schema::table('countries', function (Blueprint $table): void {
            if (Schema::hasIndex('countries', 'countries_geoname_id_unique')) {
                $table->dropUnique('countries_geoname_id_unique');
            }

            if (Schema::hasColumn('countries', 'geoname_id')) {
                $table->dropColumn('geoname_id');
            }
        });
    }
};
