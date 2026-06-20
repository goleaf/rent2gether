<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saved_searches', function (Blueprint $table): void {
            if (! Schema::hasColumn('saved_searches', 'title')) {
                $table->string('title')->nullable()->after('name');
            }

            if (! Schema::hasColumn('saved_searches', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (! Schema::hasColumn('saved_searches', 'status')) {
                $table->string('status')->default('active')->after('description');
            }

            if (! Schema::hasColumn('saved_searches', 'location_text')) {
                $table->string('location_text')->nullable()->after('district');
            }

            if (! Schema::hasColumn('saved_searches', 'radius_meters')) {
                $table->unsignedInteger('radius_meters')->nullable()->after('location_text');
            }

            if (! Schema::hasColumn('saved_searches', 'check_in_date')) {
                $table->date('check_in_date')->nullable()->after('check_out');
            }

            if (! Schema::hasColumn('saved_searches', 'check_out_date')) {
                $table->date('check_out_date')->nullable()->after('check_in_date');
            }

            if (! Schema::hasColumn('saved_searches', 'nights_count')) {
                $table->unsignedSmallInteger('nights_count')->nullable()->after('check_out_date');
            }

            if (! Schema::hasColumn('saved_searches', 'calendar_days_count')) {
                $table->unsignedSmallInteger('calendar_days_count')->nullable()->after('nights_count');
            }

            if (! Schema::hasColumn('saved_searches', 'guests_count')) {
                $table->unsignedTinyInteger('guests_count')->default(1)->after('calendar_days_count');
            }

            if (! Schema::hasColumn('saved_searches', 'flexible_days')) {
                $table->unsignedTinyInteger('flexible_days')->nullable()->after('flexible_dates');
            }

            if (! Schema::hasColumn('saved_searches', 'budget_min')) {
                $table->decimal('budget_min', 10, 2)->nullable()->after('price_max');
            }

            if (! Schema::hasColumn('saved_searches', 'budget_max')) {
                $table->decimal('budget_max', 10, 2)->nullable()->after('budget_min');
            }

            if (! Schema::hasColumn('saved_searches', 'total_budget_max')) {
                $table->decimal('total_budget_max', 10, 2)->nullable()->after('budget_max');
            }

            if (! Schema::hasColumn('saved_searches', 'property_types_json')) {
                $table->text('property_types_json')->nullable()->after('currency');
            }

            if (! Schema::hasColumn('saved_searches', 'room_types_json')) {
                $table->text('room_types_json')->nullable()->after('property_types_json');
            }

            if (! Schema::hasColumn('saved_searches', 'sleeping_place_types_json')) {
                $table->text('sleeping_place_types_json')->nullable()->after('room_types_json');
            }

            if (! Schema::hasColumn('saved_searches', 'room_gender_policy')) {
                $table->string('room_gender_policy')->nullable()->after('sleeping_place_types_json');
            }

            if (! Schema::hasColumn('saved_searches', 'required_amenity_ids_json')) {
                $table->text('required_amenity_ids_json')->nullable()->after('room_gender_policy');
            }

            if (! Schema::hasColumn('saved_searches', 'preferred_amenity_ids_json')) {
                $table->text('preferred_amenity_ids_json')->nullable()->after('required_amenity_ids_json');
            }

            if (! Schema::hasColumn('saved_searches', 'excluded_rule_ids_json')) {
                $table->text('excluded_rule_ids_json')->nullable()->after('preferred_amenity_ids_json');
            }

            if (! Schema::hasColumn('saved_searches', 'excluded_conditions_json')) {
                $table->text('excluded_conditions_json')->nullable()->after('excluded_rule_ids_json');
            }

            foreach ($this->booleanFilters() as $column => $after) {
                if (! Schema::hasColumn('saved_searches', $column)) {
                    $table->boolean($column)->default(false)->after($after);
                }
            }

            if (! Schema::hasColumn('saved_searches', 'max_deposit')) {
                $table->decimal('max_deposit', 10, 2)->nullable()->after('no_deposit_only');
            }

            if (! Schema::hasColumn('saved_searches', 'min_rating')) {
                $table->decimal('min_rating', 3, 2)->nullable()->after('max_deposit');
            }

            if (! Schema::hasColumn('saved_searches', 'min_cleanliness_rating')) {
                $table->decimal('min_cleanliness_rating', 3, 2)->nullable()->after('min_rating');
            }

            if (! Schema::hasColumn('saved_searches', 'min_safety_rating')) {
                $table->decimal('min_safety_rating', 3, 2)->nullable()->after('min_cleanliness_rating');
            }

            if (! Schema::hasColumn('saved_searches', 'min_host_rating')) {
                $table->decimal('min_host_rating', 3, 2)->nullable()->after('min_safety_rating');
            }

            if (! Schema::hasColumn('saved_searches', 'max_people_in_room')) {
                $table->unsignedTinyInteger('max_people_in_room')->nullable()->after('min_host_rating');
            }

            foreach ($this->notificationBooleans() as $column => $default) {
                if (! Schema::hasColumn('saved_searches', $column)) {
                    $table->boolean($column)->default($default)->after('avoid_mixed_room');
                }
            }

            if (! Schema::hasColumn('saved_searches', 'notification_frequency')) {
                $table->string('notification_frequency')->default('on_visit')->after('notify_better_match');
            }

            if (! Schema::hasColumn('saved_searches', 'quiet_hours_enabled')) {
                $table->boolean('quiet_hours_enabled')->default(true)->after('notification_frequency');
            }

            if (! Schema::hasColumn('saved_searches', 'quiet_hours_start')) {
                $table->string('quiet_hours_start', 5)->nullable()->after('quiet_hours_enabled');
            }

            if (! Schema::hasColumn('saved_searches', 'quiet_hours_end')) {
                $table->string('quiet_hours_end', 5)->nullable()->after('quiet_hours_start');
            }

            if (! Schema::hasColumn('saved_searches', 'last_checked_at')) {
                $table->timestamp('last_checked_at')->nullable()->after('quiet_hours_end');
            }

            if (! Schema::hasColumn('saved_searches', 'last_notified_at')) {
                $table->timestamp('last_notified_at')->nullable()->after('last_checked_at');
            }

            if (! Schema::hasColumn('saved_searches', 'next_check_at')) {
                $table->timestamp('next_check_at')->nullable()->after('last_notified_at');
            }

            if (! Schema::hasColumn('saved_searches', 'new_matches_count')) {
                $table->unsignedInteger('new_matches_count')->default(0)->after('next_check_at');
            }

            if (! Schema::hasColumn('saved_searches', 'price_drops_count')) {
                $table->unsignedInteger('price_drops_count')->default(0)->after('new_matches_count');
            }

            if (! Schema::hasColumn('saved_searches', 'available_again_count')) {
                $table->unsignedInteger('available_again_count')->default(0)->after('price_drops_count');
            }

            if (! Schema::hasColumn('saved_searches', 'last_results_hash')) {
                $table->string('last_results_hash')->nullable()->after('available_again_count');
            }
        });

        $this->addSavedSearchIndexes();

        if (! Schema::hasTable('saved_search_results')) {
            Schema::create('saved_search_results', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('saved_search_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->constrained()->cascadeOnDelete();
                $table->timestamp('first_seen_at')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('last_matched_at')->nullable();
                $table->string('status')->default('matched');
                $table->unsignedTinyInteger('match_score')->nullable();
                $table->decimal('price_per_night_snapshot', 10, 2)->nullable();
                $table->decimal('total_price_snapshot', 10, 2)->nullable();
                $table->decimal('current_price_per_night', 10, 2)->nullable();
                $table->decimal('current_total_price', 10, 2)->nullable();
                $table->decimal('deposit_snapshot', 10, 2)->nullable();
                $table->decimal('current_deposit', 10, 2)->nullable();
                $table->boolean('price_changed')->default(false);
                $table->decimal('price_change_amount', 10, 2)->nullable();
                $table->decimal('price_change_percent', 8, 2)->nullable();
                $table->boolean('became_unavailable')->default(false);
                $table->boolean('became_available_again')->default(false);
                $table->boolean('is_new_match')->default(true);
                $table->boolean('is_notified')->default(false);
                $table->timestamp('notified_at')->nullable();
                $table->timestamps();

                $table->unique(['saved_search_id', 'sleeping_place_id'], 'saved_search_results_search_place_unique');
                $table->index(['saved_search_id', 'status'], 'saved_search_results_search_status_index');
                $table->index(['saved_search_id', 'is_new_match'], 'saved_search_results_search_new_index');
                $table->index(['saved_search_id', 'price_changed'], 'saved_search_results_search_price_index');
                $table->index(['saved_search_id', 'became_available_again'], 'saved_search_results_search_available_index');
                $table->index('sleeping_place_id', 'saved_search_results_place_index');
                $table->index('property_id', 'saved_search_results_property_index');
                $table->index('room_id', 'saved_search_results_room_index');
                $table->index('last_matched_at', 'saved_search_results_last_matched_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_search_results');

        if (! Schema::hasTable('saved_searches')) {
            return;
        }

        Schema::table('saved_searches', function (Blueprint $table): void {
            foreach ([
                'saved_searches_user_status_index',
                'saved_searches_user_created_index',
                'saved_searches_city_status_index',
                'saved_searches_dates_index',
                'saved_searches_next_check_index',
                'saved_searches_last_checked_index',
                'saved_searches_frequency_index',
                'saved_searches_notify_new_index',
                'saved_searches_notify_price_drops_index',
                'saved_searches_notify_available_index',
            ] as $index) {
                if (Schema::hasIndex('saved_searches', $index)) {
                    $table->dropIndex($index);
                }
            }

            foreach ($this->addedColumns() as $column) {
                if (Schema::hasColumn('saved_searches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    private function booleanFilters(): array
    {
        return [
            'only_verified_hosts' => 'excluded_conditions_json',
            'only_verified_places' => 'only_verified_hosts',
            'only_instant_booking' => 'only_verified_places',
            'only_with_reviews' => 'only_instant_booking',
            'free_cancellation_only' => 'only_with_reviews',
            'no_deposit_only' => 'free_cancellation_only',
            'lower_bunk_only' => 'no_deposit_only',
            'exclude_upper_bunk' => 'lower_bunk_only',
            'exclude_sofa' => 'exclude_upper_bunk',
            'exclude_mattress' => 'exclude_sofa',
            'require_locker' => 'exclude_mattress',
            'require_workspace' => 'require_locker',
            'require_wifi' => 'require_workspace',
            'require_kitchen' => 'require_wifi',
            'require_washing_machine' => 'require_kitchen',
            'require_late_check_in' => 'require_washing_machine',
            'avoid_smoking' => 'require_late_check_in',
            'avoid_pets' => 'avoid_smoking',
            'avoid_mixed_room' => 'avoid_pets',
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function notificationBooleans(): array
    {
        return [
            'notify_new_matches' => true,
            'notify_price_drops' => true,
            'notify_price_increases' => false,
            'notify_available_again' => true,
            'notify_better_match' => true,
        ];
    }

    private function addSavedSearchIndexes(): void
    {
        Schema::table('saved_searches', function (Blueprint $table): void {
            if (! Schema::hasIndex('saved_searches', 'saved_searches_user_status_index')) {
                $table->index(['user_id', 'status'], 'saved_searches_user_status_index');
            }

            if (! Schema::hasIndex('saved_searches', 'saved_searches_user_created_index')) {
                $table->index(['user_id', 'created_at'], 'saved_searches_user_created_index');
            }

            if (! Schema::hasIndex('saved_searches', 'saved_searches_city_status_index')) {
                $table->index(['city_id', 'status'], 'saved_searches_city_status_index');
            }

            if (! Schema::hasIndex('saved_searches', 'saved_searches_dates_index')) {
                $table->index(['check_in_date', 'check_out_date'], 'saved_searches_dates_index');
            }

            if (! Schema::hasIndex('saved_searches', 'saved_searches_next_check_index')) {
                $table->index('next_check_at', 'saved_searches_next_check_index');
            }

            if (! Schema::hasIndex('saved_searches', 'saved_searches_last_checked_index')) {
                $table->index('last_checked_at', 'saved_searches_last_checked_index');
            }

            if (! Schema::hasIndex('saved_searches', 'saved_searches_frequency_index')) {
                $table->index('notification_frequency', 'saved_searches_frequency_index');
            }

            if (! Schema::hasIndex('saved_searches', 'saved_searches_notify_new_index')) {
                $table->index('notify_new_matches', 'saved_searches_notify_new_index');
            }

            if (! Schema::hasIndex('saved_searches', 'saved_searches_notify_price_drops_index')) {
                $table->index('notify_price_drops', 'saved_searches_notify_price_drops_index');
            }

            if (! Schema::hasIndex('saved_searches', 'saved_searches_notify_available_index')) {
                $table->index('notify_available_again', 'saved_searches_notify_available_index');
            }
        });
    }

    /**
     * @return list<string>
     */
    private function addedColumns(): array
    {
        return [
            'last_results_hash',
            'available_again_count',
            'price_drops_count',
            'new_matches_count',
            'next_check_at',
            'last_notified_at',
            'last_checked_at',
            'quiet_hours_end',
            'quiet_hours_start',
            'quiet_hours_enabled',
            'notification_frequency',
            'notify_better_match',
            'notify_available_again',
            'notify_price_increases',
            'notify_price_drops',
            'notify_new_matches',
            'avoid_mixed_room',
            'avoid_pets',
            'avoid_smoking',
            'require_late_check_in',
            'require_washing_machine',
            'require_kitchen',
            'require_wifi',
            'require_workspace',
            'require_locker',
            'exclude_mattress',
            'exclude_sofa',
            'exclude_upper_bunk',
            'lower_bunk_only',
            'max_people_in_room',
            'min_host_rating',
            'min_safety_rating',
            'min_cleanliness_rating',
            'min_rating',
            'max_deposit',
            'no_deposit_only',
            'free_cancellation_only',
            'only_with_reviews',
            'only_instant_booking',
            'only_verified_places',
            'only_verified_hosts',
            'excluded_conditions_json',
            'excluded_rule_ids_json',
            'preferred_amenity_ids_json',
            'required_amenity_ids_json',
            'room_gender_policy',
            'sleeping_place_types_json',
            'room_types_json',
            'property_types_json',
            'total_budget_max',
            'budget_max',
            'budget_min',
            'flexible_days',
            'guests_count',
            'calendar_days_count',
            'nights_count',
            'check_out_date',
            'check_in_date',
            'radius_meters',
            'location_text',
            'status',
            'description',
            'title',
        ];
    }
};
