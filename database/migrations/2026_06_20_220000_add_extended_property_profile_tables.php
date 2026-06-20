<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendProperties();
        $this->createLocationDetails();
        $this->createConditionDetails();
        $this->createAccessDetails();
        $this->extendPropertyTranslations();
    }

    public function down(): void
    {
        Schema::dropIfExists('property_access_details');
        Schema::dropIfExists('property_condition_details');
        Schema::dropIfExists('property_location_details');

        $this->dropIndexes('properties', [
            ['property_type', 'status'],
            ['district', 'city_id'],
            ['has_elevator'],
            ['free_sleeping_places_count'],
            ['occupied_sleeping_places_count'],
        ]);

        $this->dropColumns('properties', [
            'property_type',
            'property_subtype',
            'entrance',
            'postal_code',
            'show_exact_address_after_confirmation',
            'show_only_approximate_location',
            'living_area',
            'bedrooms_count',
            'shared_rooms_count',
            'pass_through_rooms_count',
            'max_residents',
            'current_residents_count',
            'permanent_residents_count',
            'short_term_guests_count',
            'active_rooms_count',
            'active_sleeping_places_count',
            'free_sleeping_places_count',
            'occupied_sleeping_places_count',
            'unavailable_sleeping_places_count',
            'can_book_whole_property',
            'can_book_private_room',
            'can_book_sleeping_place',
        ]);

        $this->dropColumns('property_translations', [
            'location_description',
            'transport_description',
            'parking_description',
            'condition_description',
            'access_description',
            'self_check_in_instructions',
            'delivery_instructions',
            'guest_visitor_rules_text',
            'courier_rules_text',
            'important_notes',
        ]);
    }

    private function extendProperties(): void
    {
        $this->addColumns('properties', [
            ['property_type', fn (Blueprint $table): ColumnDefinition => $table->string('property_type')->nullable()->after('type')],
            ['property_subtype', fn (Blueprint $table): ColumnDefinition => $table->string('property_subtype')->nullable()->after('property_type')],
            ['entrance', fn (Blueprint $table): ColumnDefinition => $table->string('entrance', 50)->nullable()->after('building')],
            ['postal_code', fn (Blueprint $table): ColumnDefinition => $table->string('postal_code', 32)->nullable()->after('apartment_number')],
            ['show_exact_address_after_confirmation', fn (Blueprint $table): ColumnDefinition => $table->boolean('show_exact_address_after_confirmation')->default(true)->after('show_exact_address_before_booking')],
            ['show_only_approximate_location', fn (Blueprint $table): ColumnDefinition => $table->boolean('show_only_approximate_location')->default(true)->after('show_exact_address_after_payment')],
            ['living_area', fn (Blueprint $table): ColumnDefinition => $table->decimal('living_area', 8, 2)->nullable()->after('total_area')],
            ['bedrooms_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('bedrooms_count')->nullable()->after('rooms_count')],
            ['shared_rooms_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('shared_rooms_count')->nullable()->after('bedrooms_count')],
            ['pass_through_rooms_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('pass_through_rooms_count')->nullable()->after('shared_rooms_count')],
            ['max_residents', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('max_residents')->nullable()->after('max_guests')],
            ['current_residents_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('current_residents_count')->default(0)->after('current_guests_count')],
            ['permanent_residents_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('permanent_residents_count')->default(0)->after('current_residents_count')],
            ['short_term_guests_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('short_term_guests_count')->default(0)->after('permanent_residents_count')],
            ['active_rooms_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('active_rooms_count')->default(0)->after('short_term_guests_count')],
            ['active_sleeping_places_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('active_sleeping_places_count')->default(0)->after('active_rooms_count')],
            ['free_sleeping_places_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('free_sleeping_places_count')->default(0)->after('active_sleeping_places_count')],
            ['occupied_sleeping_places_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('occupied_sleeping_places_count')->default(0)->after('free_sleeping_places_count')],
            ['unavailable_sleeping_places_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('unavailable_sleeping_places_count')->default(0)->after('occupied_sleeping_places_count')],
            ['can_book_whole_property', fn (Blueprint $table): ColumnDefinition => $table->boolean('can_book_whole_property')->default(false)->after('unavailable_sleeping_places_count')],
            ['can_book_private_room', fn (Blueprint $table): ColumnDefinition => $table->boolean('can_book_private_room')->default(true)->after('can_book_whole_property')],
            ['can_book_sleeping_place', fn (Blueprint $table): ColumnDefinition => $table->boolean('can_book_sleeping_place')->default(true)->after('can_book_private_room')],
        ]);

        $this->addIndexes('properties', [
            [['property_type', 'status']],
            [['district', 'city_id']],
            [['has_elevator']],
            [['free_sleeping_places_count']],
            [['occupied_sleeping_places_count']],
        ]);
    }

    private function createLocationDetails(): void
    {
        if (Schema::hasTable('property_location_details')) {
            return;
        }

        Schema::create('property_location_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nearest_metro')->nullable();
            $table->unsignedInteger('nearest_metro_distance_meters')->nullable();
            $table->unsignedSmallInteger('nearest_metro_walk_minutes')->nullable();
            $table->string('nearest_bus_stop')->nullable();
            $table->unsignedInteger('nearest_bus_stop_distance_meters')->nullable();
            $table->unsignedSmallInteger('nearest_bus_stop_walk_minutes')->nullable();
            $table->string('nearest_tram_stop')->nullable();
            $table->string('nearest_train_station')->nullable();
            $table->string('nearest_railway_station')->nullable();
            $table->unsignedInteger('railway_station_distance_meters')->nullable();
            $table->string('nearest_airport')->nullable();
            $table->unsignedInteger('airport_distance_meters')->nullable();
            $table->unsignedSmallInteger('airport_transport_minutes')->nullable();
            $table->string('nearest_shop')->nullable();
            $table->string('nearest_supermarket')->nullable();
            $table->string('nearest_pharmacy')->nullable();
            $table->string('nearest_hospital')->nullable();
            $table->string('nearest_clinic')->nullable();
            $table->string('nearest_university')->nullable();
            $table->string('nearest_school')->nullable();
            $table->string('nearest_gym')->nullable();
            $table->string('nearest_park')->nullable();
            $table->string('nearest_mall')->nullable();
            $table->string('nearest_cafe')->nullable();
            $table->string('nearest_laundry')->nullable();
            $table->string('nearest_atm')->nullable();
            $table->string('nearest_coworking')->nullable();
            $table->unsignedInteger('distance_to_center_meters')->nullable();
            $table->unsignedSmallInteger('walk_minutes_to_center')->nullable();
            $table->unsignedSmallInteger('transport_minutes_to_center')->nullable();
            $table->unsignedSmallInteger('car_minutes_to_center')->nullable();
            $table->string('transport_convenience_level')->nullable();
            $table->boolean('has_night_transport')->nullable();
            $table->boolean('easy_to_reach_with_luggage')->nullable();
            $table->string('district_noise_level')->nullable();
            $table->string('district_safety_level')->nullable();
            $table->string('street_lighting_level')->nullable();
            $table->boolean('street_busy_at_night')->nullable();
            $table->boolean('has_street_noise')->nullable();
            $table->boolean('has_bar_noise')->nullable();
            $table->boolean('has_train_noise')->nullable();
            $table->boolean('has_construction_nearby')->nullable();
            $table->boolean('has_parking_nearby')->nullable();
            $table->boolean('has_free_parking')->nullable();
            $table->boolean('has_paid_parking')->nullable();
            $table->boolean('has_private_parking')->nullable();
            $table->boolean('has_yard_parking')->nullable();
            $table->boolean('has_underground_parking')->nullable();
            $table->boolean('has_bicycle_parking')->nullable();
            $table->boolean('parking_permit_required')->nullable();
            $table->boolean('parking_usually_full')->nullable();
            $table->timestamps();

            $table->index('distance_to_center_meters');
            $table->index('transport_minutes_to_center');
            $table->index('district_noise_level');
            $table->index('district_safety_level');
            $table->index('has_free_parking');
            $table->index('has_paid_parking');
            $table->index('has_parking_nearby');
        });
    }

    private function createConditionDetails(): void
    {
        if (Schema::hasTable('property_condition_details')) {
            return;
        }

        Schema::create('property_condition_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('repair_state')->nullable();
            $table->string('cleanliness_level')->nullable();
            $table->string('smell_level')->nullable();
            $table->boolean('has_tobacco_smell')->nullable();
            $table->boolean('has_pet_smell')->nullable();
            $table->boolean('has_damp_smell')->nullable();
            $table->boolean('has_kitchen_smell')->nullable();
            $table->string('ventilation_level')->nullable();
            $table->boolean('has_ventilation')->nullable();
            $table->boolean('has_kitchen_hood')->nullable();
            $table->string('humidity_level')->nullable();
            $table->string('winter_temperature_level')->nullable();
            $table->string('summer_temperature_level')->nullable();
            $table->boolean('has_heating')->nullable();
            $table->boolean('heating_adjustable')->nullable();
            $table->boolean('has_air_conditioning')->nullable();
            $table->boolean('has_fan')->nullable();
            $table->boolean('has_hot_water')->nullable();
            $table->boolean('has_heating_problems')->nullable();
            $table->boolean('has_hot_water_problems')->nullable();
            $table->string('indoor_noise_level')->nullable();
            $table->string('street_noise_level')->nullable();
            $table->string('neighbor_noise_level')->nullable();
            $table->string('soundproofing_level')->nullable();
            $table->string('light_level')->nullable();
            $table->boolean('windows_face_yard')->nullable();
            $table->boolean('windows_face_street')->nullable();
            $table->boolean('has_blackout_curtains')->nullable();
            $table->boolean('has_insects')->nullable();
            $table->text('insects_note')->nullable();
            $table->boolean('has_mold')->nullable();
            $table->text('mold_note')->nullable();
            $table->boolean('has_damp_marks')->nullable();
            $table->boolean('regular_pest_control')->nullable();
            $table->string('furniture_condition')->nullable();
            $table->string('beds_condition')->nullable();
            $table->string('mattresses_condition')->nullable();
            $table->string('wardrobes_condition')->nullable();
            $table->string('tables_condition')->nullable();
            $table->string('chairs_condition')->nullable();
            $table->string('floor_condition')->nullable();
            $table->string('walls_condition')->nullable();
            $table->string('ceiling_condition')->nullable();
            $table->string('windows_condition')->nullable();
            $table->string('doors_condition')->nullable();
            $table->string('locks_condition')->nullable();
            $table->string('plumbing_condition')->nullable();
            $table->string('electricity_condition')->nullable();
            $table->string('kitchen_condition')->nullable();
            $table->string('bathroom_condition')->nullable();
            $table->string('toilet_condition')->nullable();
            $table->string('shower_condition')->nullable();
            $table->string('fridge_condition')->nullable();
            $table->string('stove_condition')->nullable();
            $table->string('washing_machine_condition')->nullable();
            $table->timestamp('last_cleaned_at')->nullable();
            $table->timestamp('last_repaired_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_safety_checked_at')->nullable();
            $table->timestamp('last_plumbing_checked_at')->nullable();
            $table->timestamp('last_electricity_checked_at')->nullable();
            $table->timestamp('last_internet_checked_at')->nullable();
            $table->text('owner_check_note')->nullable();
            $table->timestamps();

            $table->index('repair_state');
            $table->index('cleanliness_level');
            $table->index('indoor_noise_level');
            $table->index('has_insects');
            $table->index('has_mold');
            $table->index('last_checked_at');
        });
    }

    private function createAccessDetails(): void
    {
        if (Schema::hasTable('property_access_details')) {
            return;
        }

        Schema::create('property_access_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('entrance_type')->nullable();
            $table->boolean('has_private_entrance')->nullable();
            $table->boolean('has_shared_entrance')->nullable();
            $table->boolean('entrance_through_yard')->nullable();
            $table->boolean('entrance_through_reception')->nullable();
            $table->boolean('has_intercom')->nullable();
            $table->boolean('has_intercom_code')->nullable();
            $table->boolean('has_door_code')->nullable();
            $table->boolean('has_gate_code')->nullable();
            $table->boolean('has_key')->nullable();
            $table->boolean('has_keycard')->nullable();
            $table->boolean('has_electronic_lock')->nullable();
            $table->boolean('has_key_safe')->nullable();
            $table->text('key_safe_location_note')->nullable();
            $table->boolean('code_visible_after_confirmation')->default(true);
            $table->boolean('code_visible_after_payment')->default(true);
            $table->boolean('code_visible_on_checkin_day')->default(false);
            $table->boolean('code_changes_after_guest')->nullable();
            $table->unsignedSmallInteger('key_sets_count')->nullable();
            $table->string('key_pickup_method')->nullable();
            $table->string('key_pickup_contact_type')->nullable();
            $table->boolean('meet_host_required')->nullable();
            $table->boolean('meet_host_representative_required')->nullable();
            $table->boolean('self_check_in_available')->nullable();
            $table->boolean('self_check_in_available_at_night')->nullable();
            $table->boolean('check_in_instruction_available')->nullable();
            $table->boolean('entrance_photo_available')->nullable();
            $table->boolean('door_photo_available')->nullable();
            $table->boolean('key_safe_photo_available')->nullable();
            $table->boolean('emergency_contact_available')->nullable();
            $table->text('what_if_code_fails')->nullable();
            $table->text('what_if_key_does_not_work')->nullable();
            $table->boolean('access_24_7')->nullable();
            $table->boolean('can_return_at_night')->nullable();
            $table->boolean('has_night_entry_restrictions')->nullable();
            $table->text('night_entry_restriction_text')->nullable();
            $table->boolean('must_be_quiet_at_night_entry')->nullable();
            $table->boolean('guest_visitors_allowed')->nullable();
            $table->boolean('guest_visitors_need_approval')->nullable();
            $table->boolean('courier_rules_enabled')->nullable();
            $table->boolean('delivery_allowed')->nullable();
            $table->string('delivery_dropoff_location')->nullable();
            $table->boolean('courier_can_enter_building')->nullable();
            $table->boolean('courier_can_come_to_door')->nullable();
            $table->boolean('courier_must_leave_at_entrance')->nullable();
            $table->boolean('parcels_allowed')->nullable();
            $table->string('parcel_pickup_location')->nullable();
            $table->text('delivery_responsibility_note')->nullable();
            $table->timestamps();

            $table->index('self_check_in_available');
            $table->index('access_24_7');
            $table->index('has_intercom');
            $table->index('has_electronic_lock');
            $table->index('has_key_safe');
            $table->index('delivery_allowed');
        });
    }

    private function extendPropertyTranslations(): void
    {
        $this->addColumns('property_translations', [
            ['location_description', fn (Blueprint $table): ColumnDefinition => $table->text('location_description')->nullable()->after('full_description')],
            ['transport_description', fn (Blueprint $table): ColumnDefinition => $table->text('transport_description')->nullable()->after('location_description')],
            ['parking_description', fn (Blueprint $table): ColumnDefinition => $table->text('parking_description')->nullable()->after('neighborhood_description')],
            ['condition_description', fn (Blueprint $table): ColumnDefinition => $table->text('condition_description')->nullable()->after('parking_description')],
            ['access_description', fn (Blueprint $table): ColumnDefinition => $table->text('access_description')->nullable()->after('condition_description')],
            ['self_check_in_instructions', fn (Blueprint $table): ColumnDefinition => $table->text('self_check_in_instructions')->nullable()->after('access_description')],
            ['delivery_instructions', fn (Blueprint $table): ColumnDefinition => $table->text('delivery_instructions')->nullable()->after('night_entry_instructions')],
            ['guest_visitor_rules_text', fn (Blueprint $table): ColumnDefinition => $table->text('guest_visitor_rules_text')->nullable()->after('delivery_instructions')],
            ['courier_rules_text', fn (Blueprint $table): ColumnDefinition => $table->text('courier_rules_text')->nullable()->after('guest_visitor_rules_text')],
            ['important_notes', fn (Blueprint $table): ColumnDefinition => $table->text('important_notes')->nullable()->after('courier_rules_text')],
        ]);
    }

    /**
     * @param  list<array{0:string,1:Closure(Blueprint):ColumnDefinition}>  $columns
     */
    private function addColumns(string $table, array $columns): void
    {
        Schema::table($table, function (Blueprint $schema) use ($table, $columns): void {
            foreach ($columns as [$column, $definition]) {
                if (! Schema::hasColumn($table, $column)) {
                    $definition($schema);
                }
            }
        });
    }

    /**
     * @param  list<array{0:list<string>}>  $indexes
     */
    private function addIndexes(string $table, array $indexes): void
    {
        Schema::table($table, function (Blueprint $schema) use ($table, $indexes): void {
            foreach ($indexes as [$columns]) {
                if (! Schema::hasIndex($table, $columns)) {
                    $schema->index($columns);
                }
            }
        });
    }

    /**
     * @param  list<list<string>>  $indexes
     */
    private function dropIndexes(string $table, array $indexes): void
    {
        Schema::table($table, function (Blueprint $schema) use ($table, $indexes): void {
            foreach ($indexes as $columns) {
                if (Schema::hasIndex($table, $columns)) {
                    $schema->dropIndex($columns);
                }
            }
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function dropColumns(string $table, array $columns): void
    {
        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($table, $column),
        ));

        if ($existing === []) {
            return;
        }

        Schema::table($table, function (Blueprint $schema) use ($existing): void {
            $schema->dropColumn($existing);
        });
    }
};
