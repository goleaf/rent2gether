<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_preferences', function (Blueprint $table): void {
            if (! Schema::hasColumn('guest_preferences', 'preferred_city_id')) {
                $table->foreignId('preferred_city_id')
                    ->nullable()
                    ->after('preferred_currency')
                    ->constrained('cities')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('guest_preferences', 'needs_early_check_out')) {
                $table->boolean('needs_early_check_out')->default(false)->after('needs_late_check_in');
            }

            if (! Schema::hasColumn('guest_preferences', 'needs_accessibility')) {
                $table->boolean('needs_accessibility')->default(false)->after('needs_quiet_hours');
            }

            if (! Schema::hasColumn('guest_preferences', 'max_people_in_room')) {
                $table->unsignedTinyInteger('max_people_in_room')->nullable()->after('needs_accessibility');
            }

            if (! Schema::hasColumn('guest_preferences', 'max_walking_distance_to_transport_meters')) {
                $table->unsignedInteger('max_walking_distance_to_transport_meters')->nullable()->after('max_people_in_room');
            }

            if (! Schema::hasColumn('guest_preferences', 'sleep_schedule')) {
                $table->string('sleep_schedule')->nullable()->after('max_walking_distance_to_transport_meters');
            }

            if (! Schema::hasColumn('guest_preferences', 'social_level')) {
                $table->string('social_level')->nullable()->after('sleep_schedule');
            }

            if (! Schema::hasColumn('guest_preferences', 'allergies')) {
                $table->text('allergies')->nullable()->after('social_level');
            }

            if (! Schema::hasColumn('guest_preferences', 'baggage_size')) {
                $table->string('baggage_size')->nullable()->after('allergies');
            }

            $table->index(['preferred_city_id', 'preferred_currency']);
        });

        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'distance_to_transport_meters')) {
                $table->unsignedInteger('distance_to_transport_meters')->nullable()->after('nearest_transport')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('guest_preferences', function (Blueprint $table): void {
            $table->dropIndex(['preferred_city_id', 'preferred_currency']);

            $columns = collect([
                'baggage_size',
                'allergies',
                'social_level',
                'sleep_schedule',
                'max_walking_distance_to_transport_meters',
                'max_people_in_room',
                'needs_accessibility',
                'needs_early_check_out',
                'preferred_city_id',
            ])->filter(fn (string $column): bool => Schema::hasColumn('guest_preferences', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('properties', function (Blueprint $table): void {
            if (Schema::hasColumn('properties', 'distance_to_transport_meters')) {
                $table->dropIndex(['distance_to_transport_meters']);
                $table->dropColumn('distance_to_transport_meters');
            }
        });
    }
};
