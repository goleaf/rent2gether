<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendSleepingPlaces();
        $this->extendSleepingPlaceTranslations();
        $this->createPhysicalDetails();
        $this->createComfortDetails();
        $this->createStorageDetails();
        $this->createPositionDetails();
        $this->createConditionDetails();
    }

    public function down(): void
    {
        Schema::dropIfExists('sleeping_place_condition_details');
        Schema::dropIfExists('sleeping_place_position_details');
        Schema::dropIfExists('sleeping_place_storage_details');
        Schema::dropIfExists('sleeping_place_comfort_details');
        Schema::dropIfExists('sleeping_place_physical_details');

        $this->dropIndexes('sleeping_places', [
            ['room_id', 'sort_order'],
            ['sleeping_place_type', 'status'],
            ['bunk_level', 'status'],
            ['base_price_per_night'],
            ['instant_booking_enabled'],
            ['requires_host_approval'],
            ['can_extend'],
            ['min_nights'],
            ['max_nights'],
        ]);

        $this->dropColumns('sleeping_places', [
            'sleeping_place_type',
            'sleeping_place_subtype',
            'internal_name',
            'height_cm',
            'is_top_bunk',
            'is_bottom_bunk',
            'is_single',
            'is_double',
            'is_for_one_person',
            'is_for_couple',
            'sort_order',
            'can_extend',
            'early_check_in_allowed',
            'late_check_out_allowed',
            'second_guest_allowed',
            'second_guest_fee',
            'cancellation_policy',
        ]);

        $this->dropColumns('sleeping_place_translations', [
            'short_description',
            'full_description',
            'main_pros',
            'important_cons',
            'special_notes',
            'what_is_included',
            'what_guest_should_bring',
            'storage_instructions',
            'safety_notes',
        ]);
    }

    private function extendSleepingPlaces(): void
    {
        $this->addColumns('sleeping_places', [
            ['sleeping_place_type', fn (Blueprint $table): ColumnDefinition => $table->string('sleeping_place_type')->nullable()->after('type')],
            ['sleeping_place_subtype', fn (Blueprint $table): ColumnDefinition => $table->string('sleeping_place_subtype')->nullable()->after('sleeping_place_type')],
            ['internal_name', fn (Blueprint $table): ColumnDefinition => $table->string('internal_name')->nullable()->after('display_name')],
            ['height_cm', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('height_cm')->nullable()->after('width_cm')],
            ['is_top_bunk', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_top_bunk')->default(false)->after('bunk_level')],
            ['is_bottom_bunk', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_bottom_bunk')->default(false)->after('is_top_bunk')],
            ['is_single', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_single')->default(true)->after('is_bottom_bunk')],
            ['is_double', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_double')->default(false)->after('is_single')],
            ['is_for_one_person', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_for_one_person')->default(true)->after('is_double')],
            ['is_for_couple', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_for_couple')->default(false)->after('is_for_one_person')],
            ['sort_order', fn (Blueprint $table): ColumnDefinition => $table->unsignedInteger('sort_order')->default(0)->after('max_guest_age')],
            ['can_extend', fn (Blueprint $table): ColumnDefinition => $table->boolean('can_extend')->default(true)->after('extensions_allowed')],
            ['early_check_in_allowed', fn (Blueprint $table): ColumnDefinition => $table->boolean('early_check_in_allowed')->default(false)->after('can_extend')],
            ['late_check_out_allowed', fn (Blueprint $table): ColumnDefinition => $table->boolean('late_check_out_allowed')->default(false)->after('early_check_in_allowed')],
            ['second_guest_allowed', fn (Blueprint $table): ColumnDefinition => $table->boolean('second_guest_allowed')->default(false)->after('late_check_out_allowed')],
            ['second_guest_fee', fn (Blueprint $table): ColumnDefinition => $table->decimal('second_guest_fee', 10, 2)->nullable()->after('second_guest_allowed')],
            ['cancellation_policy', fn (Blueprint $table): ColumnDefinition => $table->string('cancellation_policy')->nullable()->after('second_guest_fee')],
        ]);

        $this->addIndexes('sleeping_places', [
            [['room_id', 'sort_order']],
            [['sleeping_place_type', 'status']],
            [['bunk_level', 'status']],
            [['base_price_per_night']],
            [['instant_booking_enabled']],
            [['requires_host_approval']],
            [['can_extend']],
            [['min_nights']],
            [['max_nights']],
        ]);
    }

    private function extendSleepingPlaceTranslations(): void
    {
        $this->addColumns('sleeping_place_translations', [
            ['short_description', fn (Blueprint $table): ColumnDefinition => $table->text('short_description')->nullable()->after('title')],
            ['full_description', fn (Blueprint $table): ColumnDefinition => $table->text('full_description')->nullable()->after('short_description')],
            ['main_pros', fn (Blueprint $table): ColumnDefinition => $table->text('main_pros')->nullable()->after('accessibility_notes')],
            ['important_cons', fn (Blueprint $table): ColumnDefinition => $table->text('important_cons')->nullable()->after('main_pros')],
            ['special_notes', fn (Blueprint $table): ColumnDefinition => $table->text('special_notes')->nullable()->after('important_cons')],
            ['what_is_included', fn (Blueprint $table): ColumnDefinition => $table->text('what_is_included')->nullable()->after('special_notes')],
            ['what_guest_should_bring', fn (Blueprint $table): ColumnDefinition => $table->text('what_guest_should_bring')->nullable()->after('what_is_included')],
            ['storage_instructions', fn (Blueprint $table): ColumnDefinition => $table->text('storage_instructions')->nullable()->after('what_guest_should_bring')],
            ['safety_notes', fn (Blueprint $table): ColumnDefinition => $table->text('safety_notes')->nullable()->after('storage_instructions')],
        ]);
    }

    private function createPhysicalDetails(): void
    {
        if (Schema::hasTable('sleeping_place_physical_details')) {
            return;
        }

        Schema::create('sleeping_place_physical_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('length_cm')->nullable();
            $table->unsignedSmallInteger('width_cm')->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('height_from_floor_cm')->nullable();
            $table->unsignedSmallInteger('clearance_above_cm')->nullable();
            $table->boolean('ladder_available')->nullable();
            $table->string('ladder_comfort_level')->nullable();
            $table->boolean('safety_rail_available')->nullable();
            $table->unsignedSmallInteger('safety_rail_height_cm')->nullable();
            $table->unsignedSmallInteger('max_weight_kg')->nullable();
            $table->boolean('suitable_for_tall_person')->nullable();
            $table->boolean('suitable_for_heavy_person')->nullable();
            $table->boolean('suitable_for_elderly')->nullable();
            $table->boolean('suitable_for_limited_mobility')->nullable();
            $table->boolean('not_suitable_for_limited_mobility')->nullable();
            $table->string('frame_material')->nullable();
            $table->string('frame_stability_level')->nullable();
            $table->string('squeak_level')->nullable();
            $table->timestamps();

            $table->index('length_cm');
            $table->index('width_cm');
            $table->index('max_weight_kg');
            $table->index('suitable_for_tall_person');
            $table->index('suitable_for_heavy_person');
            $table->index('suitable_for_limited_mobility');
        });
    }

    private function createComfortDetails(): void
    {
        if (Schema::hasTable('sleeping_place_comfort_details')) {
            return;
        }

        Schema::create('sleeping_place_comfort_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('mattress_type')->nullable();
            $table->string('mattress_firmness')->nullable();
            $table->unsignedSmallInteger('mattress_thickness_cm')->nullable();
            $table->string('mattress_condition')->nullable();
            $table->string('mattress_newness')->nullable();
            $table->date('mattress_purchase_date')->nullable();
            $table->boolean('has_mattress_protector')->nullable();
            $table->boolean('waterproof_mattress_protector')->nullable();
            $table->boolean('mattress_clean')->nullable();
            $table->boolean('mattress_has_stains')->nullable();
            $table->boolean('mattress_has_smell')->nullable();
            $table->boolean('mattress_sags')->nullable();
            $table->boolean('has_pillow')->nullable();
            $table->unsignedTinyInteger('pillows_count')->nullable();
            $table->string('pillow_type')->nullable();
            $table->boolean('has_blanket')->nullable();
            $table->string('blanket_type')->nullable();
            $table->boolean('has_extra_blanket')->nullable();
            $table->boolean('has_bedding')->nullable();
            $table->boolean('bedding_included')->nullable();
            $table->decimal('bedding_extra_fee', 10, 2)->nullable();
            $table->boolean('bedding_changed_before_guest')->nullable();
            $table->boolean('has_towel')->nullable();
            $table->boolean('towel_included')->nullable();
            $table->decimal('towel_extra_fee', 10, 2)->nullable();
            $table->boolean('has_extra_towel')->nullable();
            $table->boolean('has_bedspread')->nullable();
            $table->boolean('has_plaid')->nullable();
            $table->boolean('has_earplugs')->nullable();
            $table->boolean('has_sleep_mask')->nullable();
            $table->timestamps();

            $table->index('mattress_type');
            $table->index('mattress_firmness');
            $table->index('mattress_condition');
            $table->index('has_bedding');
            $table->index('has_towel');
        });
    }

    private function createStorageDetails(): void
    {
        if (Schema::hasTable('sleeping_place_storage_details')) {
            return;
        }

        Schema::create('sleeping_place_storage_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('has_shoe_space')->nullable();
            $table->boolean('has_luggage_space')->nullable();
            $table->boolean('has_backpack_space')->nullable();
            $table->boolean('has_under_bed_storage')->nullable();
            $table->boolean('has_under_bed_drawer')->nullable();
            $table->boolean('has_personal_locker')->nullable();
            $table->boolean('locker_has_lock')->nullable();
            $table->boolean('lock_provided')->nullable();
            $table->boolean('guest_should_bring_lock')->nullable();
            $table->boolean('can_store_valuables')->nullable();
            $table->boolean('can_store_documents')->nullable();
            $table->boolean('can_store_laptop')->nullable();
            $table->string('locker_size')->nullable();
            $table->unsignedSmallInteger('locker_width_cm')->nullable();
            $table->unsignedSmallInteger('locker_height_cm')->nullable();
            $table->unsignedSmallInteger('locker_depth_cm')->nullable();
            $table->boolean('has_shared_storage_area')->nullable();
            $table->boolean('can_leave_luggage_before_checkin')->nullable();
            $table->boolean('can_leave_luggage_after_checkout')->nullable();
            $table->text('storage_responsibility_note')->nullable();
            $table->timestamps();

            $table->index('has_personal_locker');
            $table->index('locker_has_lock');
            $table->index('has_luggage_space');
            $table->index('can_store_valuables');
        });
    }

    private function createPositionDetails(): void
    {
        if (Schema::hasTable('sleeping_place_position_details')) {
            return;
        }

        Schema::create('sleeping_place_position_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('privacy_level')->nullable();
            $table->boolean('has_curtain')->nullable();
            $table->boolean('curtain_full_cover')->nullable();
            $table->boolean('curtain_partial_cover')->nullable();
            $table->boolean('has_partition')->nullable();
            $table->boolean('has_side_wall')->nullable();
            $table->boolean('capsule_style')->nullable();
            $table->boolean('visible_from_door')->nullable();
            $table->boolean('visible_from_passage')->nullable();
            $table->boolean('visible_from_other_beds')->nullable();
            $table->boolean('can_block_light')->nullable();
            $table->boolean('has_personal_lamp')->nullable();
            $table->boolean('lamp_adjustable')->nullable();
            $table->boolean('has_power_socket')->nullable();
            $table->unsignedTinyInteger('power_sockets_count')->nullable();
            $table->boolean('socket_near_head')->nullable();
            $table->boolean('socket_near_feet')->nullable();
            $table->boolean('has_usb_charger')->nullable();
            $table->boolean('has_usb_c_charger')->nullable();
            $table->boolean('has_extension_cord')->nullable();
            $table->boolean('has_shelf')->nullable();
            $table->boolean('has_hook')->nullable();
            $table->boolean('has_phone_holder')->nullable();
            $table->boolean('has_small_table')->nullable();
            $table->boolean('near_door')->nullable();
            $table->boolean('near_window')->nullable();
            $table->boolean('near_radiator')->nullable();
            $table->boolean('near_air_conditioner')->nullable();
            $table->boolean('near_power_socket')->nullable();
            $table->boolean('near_passage')->nullable();
            $table->boolean('near_wardrobe')->nullable();
            $table->boolean('near_desk')->nullable();
            $table->boolean('near_balcony')->nullable();
            $table->boolean('near_bathroom')->nullable();
            $table->boolean('near_kitchen')->nullable();
            $table->boolean('in_room_corner')->nullable();
            $table->boolean('in_room_center')->nullable();
            $table->boolean('near_wall')->nullable();
            $table->boolean('between_two_beds')->nullable();
            $table->boolean('narrow_passage_nearby')->nullable();
            $table->string('noise_level_near_place')->nullable();
            $table->string('light_level_near_place')->nullable();
            $table->boolean('morning_light')->nullable();
            $table->boolean('corridor_light_reaches')->nullable();
            $table->boolean('draft_nearby')->nullable();
            $table->timestamps();

            $table->index('privacy_level');
            $table->index('has_curtain');
            $table->index('has_power_socket');
            $table->index('has_usb_charger');
            $table->index('near_door');
            $table->index('near_window');
            $table->index('noise_level_near_place');
        });
    }

    private function createConditionDetails(): void
    {
        if (Schema::hasTable('sleeping_place_condition_details')) {
            return;
        }

        Schema::create('sleeping_place_condition_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('condition_state')->nullable();
            $table->string('frame_condition')->nullable();
            $table->string('mattress_condition')->nullable();
            $table->string('bedding_condition')->nullable();
            $table->string('pillow_condition')->nullable();
            $table->string('blanket_condition')->nullable();
            $table->string('curtain_condition')->nullable();
            $table->string('lamp_condition')->nullable();
            $table->string('socket_condition')->nullable();
            $table->string('locker_condition')->nullable();
            $table->string('lock_condition')->nullable();
            $table->boolean('has_damage')->nullable();
            $table->boolean('has_stains')->nullable();
            $table->boolean('has_smell')->nullable();
            $table->boolean('squeaks')->nullable();
            $table->boolean('needs_repair')->nullable();
            $table->boolean('needs_mattress_replacement')->nullable();
            $table->boolean('needs_bedding_replacement')->nullable();
            $table->timestamp('last_cleaned_at')->nullable();
            $table->timestamp('last_bedding_changed_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_repaired_at')->nullable();
            $table->text('host_condition_note')->nullable();
            $table->timestamps();

            $table->index('condition_state');
            $table->index('mattress_condition');
            $table->index('has_damage');
            $table->index('needs_repair');
            $table->index('last_checked_at');
        });
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
