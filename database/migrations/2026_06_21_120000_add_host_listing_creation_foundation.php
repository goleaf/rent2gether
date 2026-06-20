<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('property_addresses')) {
            Schema::create('property_addresses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('property_id')->unique()->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('city_id')->nullable();
                $table->unsignedBigInteger('district_id')->nullable();
                $table->string('street_name')->nullable();
                $table->string('house_number')->nullable();
                $table->string('apartment_number')->nullable();
                $table->string('postal_code')->nullable();
                $table->integer('floor')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->decimal('approximate_latitude', 10, 7)->nullable();
                $table->decimal('approximate_longitude', 10, 7)->nullable();
                $table->string('public_location_label')->nullable();
                $table->boolean('show_exact_address_after_booking')->default(true);
                $table->boolean('show_street_before_booking')->default(false);
                $table->boolean('show_district_before_booking')->default(true);
                $table->timestamps();

                $table->index(['country_id', 'city_id']);
                $table->index(['city_id', 'district_id']);
                $table->index(['approximate_latitude', 'approximate_longitude'], 'property_addresses_approx_point_index');
            });
        }

        if (! Schema::hasTable('property_amenities')) {
            Schema::create('property_amenities', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->string('amenity_key');
                $table->boolean('available')->default(false);
                $table->text('description')->nullable();
                $table->boolean('visible_to_guest')->default(true);
                $table->timestamps();

                $table->index(['property_id', 'amenity_key']);
                $table->index('amenity_key');
                $table->index('available');
            });
        }

        $this->addPropertyRulesColumns();
        $this->addPropertyAccessColumns();
        $this->addRoomComfortColumns();
        $this->addSleepingPlacePhysicalColumns();
        $this->addSleepingPlaceComfortColumns();
        $this->addSleepingPlaceStorageColumns();
        $this->addSleepingPlacePositionColumns();
        $this->addPhotoColumns('property_photos', 'property_id');
        $this->addPhotoColumns('room_photos', 'room_id');
        $this->addPhotoColumns('sleeping_place_photos', 'sleeping_place_id');
        $this->createWorkflowTables();
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_creation_drafts');
        Schema::dropIfExists('host_listing_suggestions');
        Schema::dropIfExists('listing_readiness_checks');
        Schema::dropIfExists('sleeping_place_templates');
        Schema::dropIfExists('room_templates');
        Schema::dropIfExists('sleeping_place_creation_batches');
        Schema::dropIfExists('property_amenities');
        Schema::dropIfExists('property_addresses');

        $this->dropColumns('property_rules', [
            'allowed',
            'starts_at_time',
            'ends_at_time',
            'description',
            'strict',
            'visible_to_guest',
        ]);
        $this->dropColumns('property_access_details', [
            'entry_type',
            'has_smart_lock',
            'host_meeting_required',
            'representative_meeting_available',
            'entry_24_7',
            'night_entry_restrictions',
            'key_pickup_instruction',
            'key_return_instruction',
            'check_in_instruction',
            'night_entry_instruction',
            'door_code_encrypted',
            'intercom_code_encrypted',
            'key_safe_code_encrypted',
            'show_access_details_after_booking',
        ]);
        $this->dropColumns('room_comfort_details', [
            'has_window',
            'windows_count',
            'view_from_window',
            'sun_side',
            'has_lockable_door',
            'has_room_key',
            'has_wardrobe',
            'has_shared_wardrobe',
            'has_personal_lockers',
            'has_desk',
            'has_chairs',
            'has_mirror',
            'has_balcony',
            'can_work_at_night',
            'can_eat_in_room',
            'can_store_food_in_room',
        ]);
        $this->dropColumns('sleeping_place_physical_details', [
            'place_type',
            'bed_type',
            'mattress_type',
            'mattress_firmness',
            'mattress_condition',
            'mattress_age_months',
            'has_mattress_protector',
            'suitable_for_tall_guest',
            'suitable_for_heavy_guest',
            'suitable_for_couple',
            'single_guest_only',
        ]);
        $this->dropColumns('sleeping_place_comfort_details', [
            'has_privacy_curtain',
            'has_personal_lamp',
            'has_socket',
            'has_usb_charger',
            'has_shelf',
            'has_hook',
            'has_phone_place',
            'has_shoe_place',
            'has_luggage_place',
            'privacy_level',
            'noise_level',
        ]);
        $this->dropColumns('sleeping_place_storage_details', [
            'has_locker',
            'has_lockable_locker',
            'locker_number',
            'can_store_valuables',
            'has_luggage_storage',
            'storage_note',
        ]);
        $this->dropColumns('sleeping_place_position_details', [
            'near_socket',
            'top_bunk',
            'bottom_bunk',
            'position_note',
        ]);
        $this->dropColumns('property_photos', ['uploaded_by_user_id', 'thumbnail_path', 'caption', 'is_main', 'visibility']);
        $this->dropColumns('room_photos', ['uploaded_by_user_id', 'thumbnail_path', 'caption', 'is_main', 'visibility']);
        $this->dropColumns('sleeping_place_photos', ['uploaded_by_user_id', 'thumbnail_path', 'caption', 'is_main', 'visibility']);
    }

    private function addPropertyRulesColumns(): void
    {
        Schema::table('property_rules', function (Blueprint $table): void {
            $this->booleanColumn($table, 'property_rules', 'allowed', false);
            $this->stringColumn($table, 'property_rules', 'starts_at_time');
            $this->stringColumn($table, 'property_rules', 'ends_at_time');
            $this->textColumn($table, 'property_rules', 'description');
            $this->booleanColumn($table, 'property_rules', 'strict', false);
            $this->booleanColumn($table, 'property_rules', 'visible_to_guest', true);
        });

        $this->safeIndex('property_rules', ['property_id', 'rule_key']);
        $this->safeIndex('property_rules', ['rule_key']);
        $this->safeIndex('property_rules', ['allowed']);
    }

    private function addPropertyAccessColumns(): void
    {
        Schema::table('property_access_details', function (Blueprint $table): void {
            $this->stringColumn($table, 'property_access_details', 'entry_type');
            $this->booleanColumn($table, 'property_access_details', 'has_smart_lock', false);
            $this->booleanColumn($table, 'property_access_details', 'host_meeting_required', false);
            $this->booleanColumn($table, 'property_access_details', 'representative_meeting_available', false);
            $this->booleanColumn($table, 'property_access_details', 'entry_24_7', false);
            $this->booleanColumn($table, 'property_access_details', 'night_entry_restrictions', false);
            $this->textColumn($table, 'property_access_details', 'key_pickup_instruction');
            $this->textColumn($table, 'property_access_details', 'key_return_instruction');
            $this->textColumn($table, 'property_access_details', 'check_in_instruction');
            $this->textColumn($table, 'property_access_details', 'night_entry_instruction');
            $this->textColumn($table, 'property_access_details', 'door_code_encrypted');
            $this->textColumn($table, 'property_access_details', 'intercom_code_encrypted');
            $this->textColumn($table, 'property_access_details', 'key_safe_code_encrypted');
            $this->booleanColumn($table, 'property_access_details', 'show_access_details_after_booking', true);
        });

        $this->safeIndex('property_access_details', ['self_check_in_available']);
        $this->safeIndex('property_access_details', ['has_key_safe']);
        $this->safeIndex('property_access_details', ['has_smart_lock']);
    }

    private function addRoomComfortColumns(): void
    {
        Schema::table('room_comfort_details', function (Blueprint $table): void {
            $this->booleanColumn($table, 'room_comfort_details', 'has_window', false);
            $this->unsignedTinyIntegerColumn($table, 'room_comfort_details', 'windows_count');
            $this->stringColumn($table, 'room_comfort_details', 'view_from_window');
            $this->stringColumn($table, 'room_comfort_details', 'sun_side');
            $this->booleanColumn($table, 'room_comfort_details', 'has_lockable_door', false);
            $this->booleanColumn($table, 'room_comfort_details', 'has_room_key', false);
            $this->booleanColumn($table, 'room_comfort_details', 'has_wardrobe', false);
            $this->booleanColumn($table, 'room_comfort_details', 'has_shared_wardrobe', false);
            $this->booleanColumn($table, 'room_comfort_details', 'has_personal_lockers', false);
            $this->booleanColumn($table, 'room_comfort_details', 'has_desk', false);
            $this->booleanColumn($table, 'room_comfort_details', 'has_chairs', false);
            $this->booleanColumn($table, 'room_comfort_details', 'has_mirror', false);
            $this->booleanColumn($table, 'room_comfort_details', 'has_balcony', false);
            $this->booleanColumn($table, 'room_comfort_details', 'can_work_at_night', false);
            $this->booleanColumn($table, 'room_comfort_details', 'can_eat_in_room', false);
            $this->booleanColumn($table, 'room_comfort_details', 'can_store_food_in_room', false);
        });

        $this->safeIndex('room_comfort_details', ['has_lockable_door']);
        $this->safeIndex('room_comfort_details', ['has_desk']);
        $this->safeIndex('room_comfort_details', ['has_air_conditioning']);
        $this->safeIndex('room_comfort_details', ['noise_level']);
    }

    private function addSleepingPlacePhysicalColumns(): void
    {
        Schema::table('sleeping_place_physical_details', function (Blueprint $table): void {
            $this->stringColumn($table, 'sleeping_place_physical_details', 'place_type', nullable: false, default: 'single_bed');
            $this->stringColumn($table, 'sleeping_place_physical_details', 'bed_type');
            $this->stringColumn($table, 'sleeping_place_physical_details', 'mattress_type');
            $this->stringColumn($table, 'sleeping_place_physical_details', 'mattress_firmness');
            $this->stringColumn($table, 'sleeping_place_physical_details', 'mattress_condition');
            $this->unsignedIntegerColumn($table, 'sleeping_place_physical_details', 'mattress_age_months');
            $this->booleanColumn($table, 'sleeping_place_physical_details', 'has_mattress_protector', false);
            $this->booleanColumn($table, 'sleeping_place_physical_details', 'suitable_for_tall_guest', false);
            $this->booleanColumn($table, 'sleeping_place_physical_details', 'suitable_for_heavy_guest', false);
            $this->booleanColumn($table, 'sleeping_place_physical_details', 'suitable_for_couple', false);
            $this->booleanColumn($table, 'sleeping_place_physical_details', 'single_guest_only', true);
        });

        $this->safeIndex('sleeping_place_physical_details', ['place_type']);
        $this->safeIndex('sleeping_place_physical_details', ['bed_type']);
        $this->safeIndex('sleeping_place_physical_details', ['suitable_for_tall_guest']);
        $this->safeIndex('sleeping_place_physical_details', ['suitable_for_couple']);
    }

    private function addSleepingPlaceComfortColumns(): void
    {
        Schema::table('sleeping_place_comfort_details', function (Blueprint $table): void {
            $this->booleanColumn($table, 'sleeping_place_comfort_details', 'has_privacy_curtain', false);
            $this->booleanColumn($table, 'sleeping_place_comfort_details', 'has_personal_lamp', false);
            $this->booleanColumn($table, 'sleeping_place_comfort_details', 'has_socket', false);
            $this->booleanColumn($table, 'sleeping_place_comfort_details', 'has_usb_charger', false);
            $this->booleanColumn($table, 'sleeping_place_comfort_details', 'has_shelf', false);
            $this->booleanColumn($table, 'sleeping_place_comfort_details', 'has_hook', false);
            $this->booleanColumn($table, 'sleeping_place_comfort_details', 'has_phone_place', false);
            $this->booleanColumn($table, 'sleeping_place_comfort_details', 'has_shoe_place', false);
            $this->booleanColumn($table, 'sleeping_place_comfort_details', 'has_luggage_place', false);
            $this->stringColumn($table, 'sleeping_place_comfort_details', 'privacy_level');
            $this->stringColumn($table, 'sleeping_place_comfort_details', 'noise_level');
        });

        $this->safeIndex('sleeping_place_comfort_details', ['has_socket']);
        $this->safeIndex('sleeping_place_comfort_details', ['has_privacy_curtain']);
    }

    private function addSleepingPlaceStorageColumns(): void
    {
        Schema::table('sleeping_place_storage_details', function (Blueprint $table): void {
            $this->booleanColumn($table, 'sleeping_place_storage_details', 'has_locker', false);
            $this->booleanColumn($table, 'sleeping_place_storage_details', 'has_lockable_locker', false);
            $this->stringColumn($table, 'sleeping_place_storage_details', 'locker_number');
            $this->booleanColumn($table, 'sleeping_place_storage_details', 'can_store_valuables', false);
            $this->booleanColumn($table, 'sleeping_place_storage_details', 'has_luggage_storage', false);
            $this->textColumn($table, 'sleeping_place_storage_details', 'storage_note');
        });

        $this->safeIndex('sleeping_place_storage_details', ['has_locker']);
        $this->safeIndex('sleeping_place_storage_details', ['has_lockable_locker']);
    }

    private function addSleepingPlacePositionColumns(): void
    {
        Schema::table('sleeping_place_position_details', function (Blueprint $table): void {
            $this->booleanColumn($table, 'sleeping_place_position_details', 'near_socket', false);
            $this->booleanColumn($table, 'sleeping_place_position_details', 'top_bunk', false);
            $this->booleanColumn($table, 'sleeping_place_position_details', 'bottom_bunk', false);
            $this->textColumn($table, 'sleeping_place_position_details', 'position_note');
        });

        $this->safeIndex('sleeping_place_position_details', ['near_door']);
        $this->safeIndex('sleeping_place_position_details', ['near_window']);
        $this->safeIndex('sleeping_place_position_details', ['near_passage']);
    }

    private function addPhotoColumns(string $tableName, string $ownerColumn): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'uploaded_by_user_id')) {
                $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            $this->stringColumn($table, $tableName, 'thumbnail_path');
            $this->textColumn($table, $tableName, 'caption');
            $this->booleanColumn($table, $tableName, 'is_main', false);
            $this->stringColumn($table, $tableName, 'visibility', nullable: false, default: 'public');
        });

        $this->safeIndex($tableName, [$ownerColumn]);
        $this->safeIndex($tableName, ['uploaded_by_user_id']);
        $this->safeIndex($tableName, ['is_main']);
        $this->safeIndex($tableName, ['visibility']);
    }

    private function createWorkflowTables(): void
    {
        if (! Schema::hasTable('sleeping_place_creation_batches')) {
            Schema::create('sleeping_place_creation_batches', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->constrained()->cascadeOnDelete();
                $table->string('batch_name')->nullable();
                $table->unsignedInteger('places_count');
                $table->text('template_json')->nullable();
                $table->string('status')->default('draft');
                $table->timestamps();

                $table->index('room_id');
                $table->index(['user_id', 'status']);
                $table->index(['property_id', 'status']);
            });
        }

        if (! Schema::hasTable('room_templates')) {
            Schema::create('room_templates', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('room_type')->nullable();
                $table->text('template_json')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'is_default']);
                $table->index('room_type');
            });
        }

        if (! Schema::hasTable('sleeping_place_templates')) {
            Schema::create('sleeping_place_templates', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('place_type')->nullable();
                $table->text('template_json')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'is_default']);
                $table->index('place_type');
            });
        }

        if (! Schema::hasTable('listing_readiness_checks')) {
            Schema::create('listing_readiness_checks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('check_key');
                $table->string('status')->default('missing');
                $table->boolean('required')->default(true);
                $table->string('message_key');
                $table->text('message_params_json')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('property_id');
                $table->index('room_id');
                $table->index('sleeping_place_id');
                $table->index('check_key');
                $table->index('status');
                $table->index('required');
            });
        }

        if (! Schema::hasTable('host_listing_suggestions')) {
            Schema::create('host_listing_suggestions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('suggestion_key');
                $table->string('severity')->default('info');
                $table->string('message_key');
                $table->string('action_key')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['property_id', 'status']);
                $table->index(['room_id', 'status']);
                $table->index(['sleeping_place_id', 'status']);
                $table->index('suggestion_key');
            });
        }

        if (! Schema::hasTable('listing_creation_drafts')) {
            Schema::create('listing_creation_drafts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('draft_type');
                $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
                $table->string('current_step');
                $table->text('draft_data_json')->nullable();
                $table->text('completed_steps_json')->nullable();
                $table->timestamp('last_saved_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'draft_type']);
                $table->index('property_id');
                $table->index('room_id');
                $table->index('sleeping_place_id');
                $table->index('last_saved_at');
            });
        }
    }

    private function booleanColumn(Blueprint $table, string $tableName, string $column, bool $default): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->boolean($column)->default($default);
        }
    }

    private function stringColumn(Blueprint $table, string $tableName, string $column, bool $nullable = true, ?string $default = null): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $definition = $table->string($column);

            if ($nullable) {
                $definition->nullable();
            }

            if ($default !== null) {
                $definition->default($default);
            }
        }
    }

    private function textColumn(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->text($column)->nullable();
        }
    }

    private function unsignedTinyIntegerColumn(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->unsignedTinyInteger($column)->nullable();
        }
    }

    private function unsignedIntegerColumn(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->unsignedInteger($column)->nullable();
        }
    }

    private function safeIndex(string $table, array $columns): void
    {
        if (Schema::hasTable($table) && ! Schema::hasIndex($table, $columns)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns));
        }
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropColumns(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($table, $column),
        ));

        if ($existing !== []) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn($existing));
        }
    }
};
