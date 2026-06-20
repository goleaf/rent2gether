<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendUsers();
        $this->extendProperties();
        $this->extendRooms();
        $this->extendSleepingPlaces();
        $this->extendNotifications();
        $this->createFoundationMediaAndRuleTables();
    }

    public function down(): void
    {
        $this->dropFoundationMediaAndRuleTables();
        $this->dropNotificationExtensions();
        $this->dropSleepingPlaceExtensions();
        $this->dropRoomExtensions();
        $this->dropPropertyExtensions();
        $this->dropUserExtensions();
    }

    private function extendUsers(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role_mode')) {
                $table->string('role_mode')->default('guest');
            }

            if (! Schema::hasColumn('users', 'preferred_locale')) {
                $table->string('preferred_locale', 10)->nullable();
            }

            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->nullable();
            }

            if (! Schema::hasColumn('users', 'is_guest')) {
                $table->boolean('is_guest')->default(true);
            }

            if (! Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable();
            }

            if (! Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable();
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            $this->index($table, 'users', ['role_mode'], 'users_role_mode_index');
            $this->index($table, 'users', ['preferred_locale'], 'users_preferred_locale_index');
            $this->index($table, 'users', ['is_guest', 'is_host'], 'users_guest_host_flags_index');
        });
    }

    private function extendProperties(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'district_id')) {
                $table->unsignedBigInteger('district_id')->nullable();
            }

            if (! Schema::hasColumn('properties', 'street_name')) {
                $table->string('street_name')->nullable();
            }

            if (! Schema::hasColumn('properties', 'floors_count')) {
                $table->unsignedTinyInteger('floors_count')->nullable();
            }

            if (! Schema::hasColumn('properties', 'max_residents_count')) {
                $table->unsignedSmallInteger('max_residents_count')->nullable();
            }

            if (! Schema::hasColumn('properties', 'free_places_count')) {
                $table->unsignedSmallInteger('free_places_count')->default(0);
            }

            if (! Schema::hasColumn('properties', 'occupied_places_count')) {
                $table->unsignedSmallInteger('occupied_places_count')->default(0);
            }
        });

        Schema::table('properties', function (Blueprint $table): void {
            $this->index($table, 'properties', ['user_id'], 'properties_user_id_foundation_index');
            $this->index($table, 'properties', ['city_id', 'district_id'], 'properties_city_district_foundation_index');
            $this->index($table, 'properties', ['property_type'], 'properties_property_type_foundation_index');
            $this->index($table, 'properties', ['publication_status'], 'properties_publication_status_foundation_index');
        });
    }

    private function extendRooms(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            if (! Schema::hasColumn('rooms', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('rooms', 'has_lockable_door')) {
                $table->boolean('has_lockable_door')->default(false);
            }

            if (! Schema::hasColumn('rooms', 'has_room_key')) {
                $table->boolean('has_room_key')->default(false);
            }

            if (! Schema::hasColumn('rooms', 'has_lockers')) {
                $table->boolean('has_lockers')->default(false);
            }

            if (! Schema::hasColumn('rooms', 'has_chairs')) {
                $table->boolean('has_chairs')->default(false);
            }

            if (! Schema::hasColumn('rooms', 'has_fan')) {
                $table->boolean('has_fan')->default(false);
            }

            if (! Schema::hasColumn('rooms', 'rules_text')) {
                $table->text('rules_text')->nullable();
            }
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $this->index($table, 'rooms', ['user_id'], 'rooms_user_id_foundation_index');
            $this->index($table, 'rooms', ['room_type'], 'rooms_room_type_foundation_index');
            $this->index($table, 'rooms', ['gender_policy'], 'rooms_gender_policy_foundation_index');
            $this->index($table, 'rooms', ['status'], 'rooms_status_foundation_index');
        });
    }

    private function extendSleepingPlaces(): void
    {
        Schema::table('sleeping_places', function (Blueprint $table): void {
            if (! Schema::hasColumn('sleeping_places', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('sleeping_places', 'title')) {
                $table->string('title')->nullable();
            }

            if (! Schema::hasColumn('sleeping_places', 'place_type')) {
                $table->string('place_type')->nullable();
            }

            if (! Schema::hasColumn('sleeping_places', 'bed_type')) {
                $table->string('bed_type')->nullable();
            }

            if (! Schema::hasColumn('sleeping_places', 'is_double_place')) {
                $table->boolean('is_double_place')->default(false);
            }

            if (! Schema::hasColumn('sleeping_places', 'max_guests_count')) {
                $table->unsignedTinyInteger('max_guests_count')->default(1);
            }

            if (! Schema::hasColumn('sleeping_places', 'base_price')) {
                $table->decimal('base_price', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('sleeping_places', 'has_mattress')) {
                $table->boolean('has_mattress')->default(false);
            }

            if (! Schema::hasColumn('sleeping_places', 'mattress_condition')) {
                $table->string('mattress_condition')->nullable();
            }

            if (! Schema::hasColumn('sleeping_places', 'has_privacy_curtain')) {
                $table->boolean('has_privacy_curtain')->default(false);
            }

            if (! Schema::hasColumn('sleeping_places', 'has_personal_lamp')) {
                $table->boolean('has_personal_lamp')->default(false);
            }

            if (! Schema::hasColumn('sleeping_places', 'has_socket')) {
                $table->boolean('has_socket')->default(false);
            }

            if (! Schema::hasColumn('sleeping_places', 'has_lockable_locker')) {
                $table->boolean('has_lockable_locker')->default(false);
            }

            if (! Schema::hasColumn('sleeping_places', 'suitable_for_tall_guest')) {
                $table->boolean('suitable_for_tall_guest')->default(false);
            }

            if (! Schema::hasColumn('sleeping_places', 'suitable_for_heavy_guest')) {
                $table->boolean('suitable_for_heavy_guest')->default(false);
            }

            if (! Schema::hasColumn('sleeping_places', 'suitable_for_couple')) {
                $table->boolean('suitable_for_couple')->default(false);
            }

            if (! Schema::hasColumn('sleeping_places', 'near_passage')) {
                $table->boolean('near_passage')->default(false);
            }
        });

        Schema::table('sleeping_places', function (Blueprint $table): void {
            $this->index($table, 'sleeping_places', ['property_id'], 'sleeping_places_property_id_foundation_index');
            $this->index($table, 'sleeping_places', ['room_id'], 'sleeping_places_room_id_foundation_index');
            $this->index($table, 'sleeping_places', ['user_id'], 'sleeping_places_user_id_foundation_index');
            $this->index($table, 'sleeping_places', ['place_type'], 'sleeping_places_place_type_foundation_index');
            $this->index($table, 'sleeping_places', ['status'], 'sleeping_places_status_foundation_index');
            $this->index($table, 'sleeping_places', ['publication_status'], 'sleeping_places_publication_status_foundation_index');
            $this->index($table, 'sleeping_places', ['base_price'], 'sleeping_places_base_price_foundation_index');
            $this->index($table, 'sleeping_places', ['max_guests_count'], 'sleeping_places_max_guests_count_foundation_index');
            $this->index($table, 'sleeping_places', ['publication_status', 'status'], 'sleeping_places_publication_status_status_index');
        });
    }

    private function createFoundationMediaAndRuleTables(): void
    {
        $this->createPhotoTable('property_photos', 'property_id', 'properties');
        $this->createPhotoTable('room_photos', 'room_id', 'rooms');
        $this->createPhotoTable('sleeping_place_photos', 'sleeping_place_id', 'sleeping_places');
        $this->createRuleTable('property_rules', 'property_id', 'properties');
        $this->createRuleTable('room_rules', 'room_id', 'rooms');
        $this->createRuleTable('sleeping_place_rules', 'sleeping_place_id', 'sleeping_places');
    }

    private function extendNotifications(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table): void {
            if (! Schema::hasColumn('notifications', 'sleeping_place_id')) {
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
            }
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $this->index($table, 'notifications', ['sleeping_place_id'], 'notifications_sleeping_place_id_foundation_index');
        });
    }

    private function dropNotificationExtensions(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table): void {
            $this->dropIndex($table, 'notifications_sleeping_place_id_foundation_index');

            if (Schema::hasColumn('notifications', 'sleeping_place_id')) {
                $table->dropConstrainedForeignId('sleeping_place_id');
            }
        });
    }

    private function dropFoundationMediaAndRuleTables(): void
    {
        Schema::dropIfExists('sleeping_place_rules');
        Schema::dropIfExists('room_rules');
        Schema::dropIfExists('property_rules');
        Schema::dropIfExists('sleeping_place_photos');
        Schema::dropIfExists('room_photos');
        Schema::dropIfExists('property_photos');
    }

    private function createPhotoTable(string $tableName, string $foreignKey, string $parentTable): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($foreignKey, $parentTable, $tableName): void {
            $table->id();
            $table->foreignId($foreignKey)->constrained($parentTable)->cascadeOnDelete();
            $table->foreignId('media_item_id')->nullable()->constrained('media_items')->nullOnDelete();
            $table->string('disk')->default('public');
            $table->string('path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index([$foreignKey, 'status'], $tableName.'_owner_status_index');
            $table->index([$foreignKey, 'sort_order'], $tableName.'_owner_sort_index');
            $table->index(['media_item_id'], $tableName.'_media_item_id_index');
        });
    }

    private function createRuleTable(string $tableName, string $foreignKey, string $parentTable): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($foreignKey, $parentTable, $tableName): void {
            $table->id();
            $table->foreignId($foreignKey)->constrained($parentTable)->cascadeOnDelete();
            $table->foreignId('rule_id')->nullable()->constrained('rules')->nullOnDelete();
            $table->string('rule_key')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index([$foreignKey, 'status'], $tableName.'_owner_status_index');
            $table->index(['rule_id'], $tableName.'_rule_id_index');
            $table->index(['rule_key'], $tableName.'_rule_key_index');
        });
    }

    private function dropSleepingPlaceExtensions(): void
    {
        Schema::table('sleeping_places', function (Blueprint $table): void {
            $this->dropIndex($table, 'sleeping_places_publication_status_status_index');
            $this->dropIndex($table, 'sleeping_places_max_guests_count_foundation_index');
            $this->dropIndex($table, 'sleeping_places_base_price_foundation_index');
            $this->dropIndex($table, 'sleeping_places_publication_status_foundation_index');
            $this->dropIndex($table, 'sleeping_places_status_foundation_index');
            $this->dropIndex($table, 'sleeping_places_place_type_foundation_index');
            $this->dropIndex($table, 'sleeping_places_user_id_foundation_index');
            $this->dropIndex($table, 'sleeping_places_room_id_foundation_index');
            $this->dropIndex($table, 'sleeping_places_property_id_foundation_index');

            if (Schema::hasColumn('sleeping_places', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            $this->dropColumns($table, 'sleeping_places', [
                'title',
                'place_type',
                'bed_type',
                'is_double_place',
                'max_guests_count',
                'base_price',
                'has_mattress',
                'mattress_condition',
                'has_privacy_curtain',
                'has_personal_lamp',
                'has_socket',
                'has_lockable_locker',
                'suitable_for_tall_guest',
                'suitable_for_heavy_guest',
                'suitable_for_couple',
                'near_passage',
            ]);
        });
    }

    private function dropRoomExtensions(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $this->dropIndex($table, 'rooms_status_foundation_index');
            $this->dropIndex($table, 'rooms_gender_policy_foundation_index');
            $this->dropIndex($table, 'rooms_room_type_foundation_index');
            $this->dropIndex($table, 'rooms_user_id_foundation_index');

            if (Schema::hasColumn('rooms', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            $this->dropColumns($table, 'rooms', [
                'has_lockable_door',
                'has_room_key',
                'has_lockers',
                'has_chairs',
                'has_fan',
                'rules_text',
            ]);
        });
    }

    private function dropPropertyExtensions(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $this->dropIndex($table, 'properties_publication_status_foundation_index');
            $this->dropIndex($table, 'properties_property_type_foundation_index');
            $this->dropIndex($table, 'properties_city_district_foundation_index');
            $this->dropIndex($table, 'properties_user_id_foundation_index');

            $this->dropColumns($table, 'properties', [
                'district_id',
                'street_name',
                'floors_count',
                'max_residents_count',
                'free_places_count',
                'occupied_places_count',
            ]);
        });
    }

    private function dropUserExtensions(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $this->dropIndex($table, 'users_guest_host_flags_index');
            $this->dropIndex($table, 'users_preferred_locale_index');
            $this->dropIndex($table, 'users_role_mode_index');

            $this->dropColumns($table, 'users', [
                'role_mode',
                'preferred_locale',
                'timezone',
                'is_guest',
                'avatar_path',
                'phone_verified_at',
            ]);
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function index(Blueprint $table, string $tableName, array $columns, string $indexName): void
    {
        if ($this->columnsExist($tableName, $columns) && ! Schema::hasIndex($tableName, $indexName)) {
            $table->index($columns, $indexName);
        }
    }

    private function dropIndex(Blueprint $table, string $indexName): void
    {
        try {
            $table->dropIndex($indexName);
        } catch (Throwable) {
            // The migration is additive and may be run against partially prepared local databases.
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function dropColumns(Blueprint $table, string $tableName, array $columns): void
    {
        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($tableName, $column),
        ));

        if ($existing !== []) {
            $table->dropColumn($existing);
        }
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
