<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendRooms();
        $this->extendRoomTranslations();
        $this->createLayoutDetails();
        $this->createComfortDetails();
        $this->createAccessDetails();
        $this->createConditionDetails();
    }

    public function down(): void
    {
        Schema::dropIfExists('room_condition_details');
        Schema::dropIfExists('room_access_details');
        Schema::dropIfExists('room_comfort_details');
        Schema::dropIfExists('room_layout_details');

        $this->dropIndexes('rooms', [
            ['property_id', 'status'],
            ['property_id', 'sort_order'],
            ['room_type', 'status'],
            ['gender_policy', 'status'],
            ['free_sleeping_places_count'],
            ['occupied_sleeping_places_count'],
            ['can_book_entire_room'],
            ['can_book_individual_places'],
        ]);

        $this->dropColumns('rooms', [
            'room_type',
            'living_format',
            'internal_name',
            'is_shared',
            'is_for_one_person',
            'is_for_couples',
            'is_for_groups',
            'is_for_long_stay',
            'is_for_short_stay',
            'sleeping_places_count',
            'active_sleeping_places_count',
            'occupied_sleeping_places_count',
            'free_sleeping_places_count',
            'unavailable_sleeping_places_count',
            'current_guests_count',
            'permanent_residents_count',
            'short_term_guests_count',
            'can_book_entire_room',
            'can_book_individual_places',
            'sort_order',
        ]);

        $this->dropColumns('room_translations', [
            'short_description',
            'full_description',
            'work_study_instructions',
            'food_rules_text',
            'conflict_instructions',
            'special_notes',
        ]);
    }

    private function extendRooms(): void
    {
        $this->addColumns('rooms', [
            ['room_type', fn (Blueprint $table): ColumnDefinition => $table->string('room_type')->nullable()->after('type')],
            ['living_format', fn (Blueprint $table): ColumnDefinition => $table->string('living_format')->nullable()->after('room_type')],
            ['internal_name', fn (Blueprint $table): ColumnDefinition => $table->string('internal_name')->nullable()->after('room_number')],
            ['is_shared', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_shared')->default(true)->after('is_private')],
            ['is_for_one_person', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_for_one_person')->default(false)->after('is_pass_through')],
            ['is_for_couples', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_for_couples')->default(false)->after('is_for_one_person')],
            ['is_for_groups', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_for_groups')->default(false)->after('is_for_couples')],
            ['is_for_long_stay', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_for_long_stay')->default(true)->after('is_for_groups')],
            ['is_for_short_stay', fn (Blueprint $table): ColumnDefinition => $table->boolean('is_for_short_stay')->default(true)->after('is_for_long_stay')],
            ['sleeping_places_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('sleeping_places_count')->default(0)->after('beds_count')],
            ['active_sleeping_places_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('active_sleeping_places_count')->default(0)->after('sleeping_places_count')],
            ['occupied_sleeping_places_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('occupied_sleeping_places_count')->default(0)->after('occupied_places_count')],
            ['free_sleeping_places_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('free_sleeping_places_count')->default(0)->after('occupied_sleeping_places_count')],
            ['unavailable_sleeping_places_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('unavailable_sleeping_places_count')->default(0)->after('free_sleeping_places_count')],
            ['current_guests_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('current_guests_count')->default(0)->after('max_guests')],
            ['permanent_residents_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('permanent_residents_count')->default(0)->after('current_guests_count')],
            ['short_term_guests_count', fn (Blueprint $table): ColumnDefinition => $table->unsignedSmallInteger('short_term_guests_count')->default(0)->after('permanent_residents_count')],
            ['can_book_entire_room', fn (Blueprint $table): ColumnDefinition => $table->boolean('can_book_entire_room')->default(false)->after('max_guest_age')],
            ['can_book_individual_places', fn (Blueprint $table): ColumnDefinition => $table->boolean('can_book_individual_places')->default(true)->after('can_book_entire_room')],
            ['sort_order', fn (Blueprint $table): ColumnDefinition => $table->unsignedInteger('sort_order')->default(0)->after('can_book_individual_places')],
        ]);

        $this->addIndexes('rooms', [
            [['property_id', 'status']],
            [['property_id', 'sort_order']],
            [['room_type', 'status']],
            [['gender_policy', 'status']],
            [['free_sleeping_places_count']],
            [['occupied_sleeping_places_count']],
            [['can_book_entire_room']],
            [['can_book_individual_places']],
        ]);
    }

    private function extendRoomTranslations(): void
    {
        $this->addColumns('room_translations', [
            ['short_description', fn (Blueprint $table): ColumnDefinition => $table->text('short_description')->nullable()->after('title')],
            ['full_description', fn (Blueprint $table): ColumnDefinition => $table->text('full_description')->nullable()->after('short_description')],
            ['work_study_instructions', fn (Blueprint $table): ColumnDefinition => $table->text('work_study_instructions')->nullable()->after('storage_instructions')],
            ['food_rules_text', fn (Blueprint $table): ColumnDefinition => $table->text('food_rules_text')->nullable()->after('quiet_hours_text')],
            ['conflict_instructions', fn (Blueprint $table): ColumnDefinition => $table->text('conflict_instructions')->nullable()->after('food_rules_text')],
            ['special_notes', fn (Blueprint $table): ColumnDefinition => $table->text('special_notes')->nullable()->after('conflict_instructions')],
        ]);
    }

    private function createLayoutDetails(): void
    {
        if (Schema::hasTable('room_layout_details')) {
            return;
        }

        Schema::create('room_layout_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('area', 8, 2)->nullable();
            $table->decimal('length_meters', 8, 2)->nullable();
            $table->decimal('width_meters', 8, 2)->nullable();
            $table->decimal('ceiling_height_meters', 8, 2)->nullable();
            $table->unsignedTinyInteger('windows_count')->nullable();
            $table->string('window_size')->nullable();
            $table->string('window_view')->nullable();
            $table->boolean('windows_face_yard')->nullable();
            $table->boolean('windows_face_street')->nullable();
            $table->boolean('windows_face_quiet_side')->nullable();
            $table->boolean('windows_face_noisy_road')->nullable();
            $table->string('cardinal_direction')->nullable();
            $table->boolean('has_balcony')->nullable();
            $table->boolean('balcony_accessible')->nullable();
            $table->boolean('has_free_passage_space')->nullable();
            $table->boolean('narrow_passages')->nullable();
            $table->boolean('has_many_free_space')->nullable();
            $table->boolean('has_little_free_space')->nullable();
            $table->timestamps();

            $table->index('area');
            $table->index('windows_count');
            $table->index('cardinal_direction');
        });
    }

    private function createComfortDetails(): void
    {
        if (Schema::hasTable('room_comfort_details')) {
            return;
        }

        Schema::create('room_comfort_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('has_heating')->nullable();
            $table->boolean('heating_adjustable')->nullable();
            $table->boolean('has_air_conditioning')->nullable();
            $table->boolean('has_fan')->nullable();
            $table->boolean('has_humidifier')->nullable();
            $table->boolean('has_dehumidifier')->nullable();
            $table->string('winter_temperature_level')->nullable();
            $table->string('summer_temperature_level')->nullable();
            $table->string('ventilation_level')->nullable();
            $table->boolean('can_open_window')->nullable();
            $table->boolean('can_close_window')->nullable();
            $table->boolean('has_mosquito_net')->nullable();
            $table->boolean('has_draft')->nullable();
            $table->string('smell_level')->nullable();
            $table->boolean('has_damp_smell')->nullable();
            $table->boolean('has_tobacco_smell')->nullable();
            $table->boolean('has_pet_smell')->nullable();
            $table->string('light_level')->nullable();
            $table->boolean('has_main_light')->nullable();
            $table->boolean('has_night_light')->nullable();
            $table->boolean('has_personal_lamps')->nullable();
            $table->boolean('has_curtains')->nullable();
            $table->boolean('has_blackout_curtains')->nullable();
            $table->boolean('can_turn_light_at_night')->nullable();
            $table->boolean('can_use_personal_lamp_at_night')->nullable();
            $table->string('noise_level')->nullable();
            $table->string('street_noise_level')->nullable();
            $table->string('neighbor_noise_level')->nullable();
            $table->string('corridor_noise_level')->nullable();
            $table->string('kitchen_noise_level')->nullable();
            $table->string('bathroom_noise_level')->nullable();
            $table->string('soundproofing_level')->nullable();
            $table->boolean('quiet_hours_enabled')->nullable();
            $table->string('quiet_hours_start', 5)->nullable();
            $table->string('quiet_hours_end', 5)->nullable();
            $table->timestamps();

            $table->index('noise_level');
            $table->index('light_level');
            $table->index('quiet_hours_enabled');
            $table->index('has_air_conditioning');
            $table->index('has_heating');
        });
    }

    private function createAccessDetails(): void
    {
        if (Schema::hasTable('room_access_details')) {
            return;
        }

        Schema::create('room_access_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('has_door')->nullable();
            $table->boolean('has_lock')->nullable();
            $table->boolean('has_key')->nullable();
            $table->boolean('key_given_to_guest')->nullable();
            $table->boolean('shared_key')->nullable();
            $table->boolean('key_only_with_host')->nullable();
            $table->boolean('can_lock_from_inside')->nullable();
            $table->boolean('can_lock_from_outside')->nullable();
            $table->boolean('has_latch')->nullable();
            $table->boolean('glass_door')->nullable();
            $table->string('privacy_level')->nullable();
            $table->text('host_entry_rules')->nullable();
            $table->text('other_guests_entry_rules')->nullable();
            $table->boolean('has_wardrobe')->nullable();
            $table->boolean('has_shared_wardrobe')->nullable();
            $table->boolean('has_personal_lockers')->nullable();
            $table->unsignedSmallInteger('personal_lockers_count')->nullable();
            $table->boolean('lockers_have_locks')->nullable();
            $table->boolean('has_shelves')->nullable();
            $table->boolean('has_hangers')->nullable();
            $table->boolean('has_luggage_space')->nullable();
            $table->boolean('has_shoe_space')->nullable();
            $table->boolean('has_coat_space')->nullable();
            $table->boolean('has_bedside_table')->nullable();
            $table->boolean('has_drawer_unit')->nullable();
            $table->boolean('has_desk')->nullable();
            $table->unsignedTinyInteger('desks_count')->nullable();
            $table->boolean('has_chairs')->nullable();
            $table->unsignedTinyInteger('chairs_count')->nullable();
            $table->boolean('has_mirror')->nullable();
            $table->boolean('has_hooks')->nullable();
            $table->boolean('has_drying_rack')->nullable();
            $table->boolean('can_store_food')->nullable();
            $table->string('food_storage_allowed_type')->nullable();
            $table->timestamps();

            $table->index('has_lock');
            $table->index('has_key');
            $table->index('has_personal_lockers');
            $table->index('has_desk');
            $table->index('can_store_food');
        });
    }

    private function createConditionDetails(): void
    {
        if (Schema::hasTable('room_condition_details')) {
            return;
        }

        Schema::create('room_condition_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('condition_state')->nullable();
            $table->string('repair_state')->nullable();
            $table->string('cleanliness_level')->nullable();
            $table->string('floor_condition')->nullable();
            $table->string('walls_condition')->nullable();
            $table->string('ceiling_condition')->nullable();
            $table->string('window_condition')->nullable();
            $table->string('door_condition')->nullable();
            $table->string('lock_condition')->nullable();
            $table->string('furniture_condition')->nullable();
            $table->string('wardrobe_condition')->nullable();
            $table->string('desk_condition')->nullable();
            $table->string('chairs_condition')->nullable();
            $table->string('balcony_condition')->nullable();
            $table->boolean('has_dust')->nullable();
            $table->boolean('has_bad_smell')->nullable();
            $table->boolean('has_damp_marks')->nullable();
            $table->boolean('has_mold')->nullable();
            $table->boolean('has_insects')->nullable();
            $table->boolean('has_damage')->nullable();
            $table->boolean('needs_repair')->nullable();
            $table->boolean('recently_renovated')->nullable();
            $table->boolean('needs_refresh')->nullable();
            $table->timestamp('last_cleaned_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_repaired_at')->nullable();
            $table->text('host_condition_note')->nullable();
            $table->timestamps();

            $table->index('cleanliness_level');
            $table->index('repair_state');
            $table->index('has_mold');
            $table->index('has_insects');
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
