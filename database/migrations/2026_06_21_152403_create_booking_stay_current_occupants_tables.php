<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_stays', function (Blueprint $table): void {
            $table->id();
            $table->string('stay_number')->unique();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->date('check_in_date');
            $table->string('check_in_time')->nullable();
            $table->timestamp('actual_check_in_at')->nullable();
            $table->date('planned_check_out_date');
            $table->string('planned_check_out_time')->nullable();
            $table->timestamp('actual_check_out_at')->nullable();
            $table->unsignedInteger('nights_count')->default(0);
            $table->unsignedInteger('calendar_presence_days_count')->default(0);
            $table->unsignedInteger('nights_passed')->default(0);
            $table->unsignedInteger('nights_remaining')->default(0);
            $table->string('payment_status')->nullable();
            $table->string('deposit_status')->nullable();
            $table->string('cleaning_status')->nullable();
            $table->string('inspection_status')->nullable();
            $table->boolean('has_open_complaint')->default(false);
            $table->boolean('has_open_maintenance')->default(false);
            $table->boolean('has_neighbor_problem')->default(false);
            $table->boolean('has_payment_problem')->default(false);
            $table->boolean('has_deposit_issue')->default(false);
            $table->boolean('extension_requested')->default(false);
            $table->boolean('relocation_requested')->default(false);
            $table->boolean('checkout_soon')->default(false);
            $table->boolean('checkout_required')->default(false);
            $table->text('guest_note')->nullable();
            $table->text('host_note')->nullable();
            $table->text('internal_host_note')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
            $table->index('check_in_date');
            $table->index('planned_check_out_date');
            $table->index('actual_check_out_at');
            $table->index('status');
            $table->index('has_open_complaint');
            $table->index('has_open_maintenance');
            $table->index('extension_requested');
            $table->index('relocation_requested');
            $table->index('checkout_soon');
        });

        Schema::create('booking_stay_occupants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_stay_id')->constrained('booking_stays')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('occupant_name');
            $table->string('occupant_type')->default('main_guest');
            $table->boolean('is_main_guest')->default(false);
            $table->string('age_range')->nullable();
            $table->string('gender')->nullable();
            $table->boolean('public_gender_visible')->default(false);
            $table->string('city_name')->nullable();
            $table->string('country_name')->nullable();
            $table->json('languages_json')->nullable();
            $table->string('stay_purpose')->nullable();
            $table->string('sleep_schedule')->nullable();
            $table->string('smoking_status')->nullable();
            $table->string('sociability_level')->nullable();
            $table->decimal('neighbor_rating_snapshot', 3, 2)->nullable();
            $table->string('public_visibility')->default('roommates_only');
            $table->timestamps();

            $table->index('booking_stay_id');
            $table->index('booking_id');
            $table->index('user_id');
            $table->index('occupant_type');
            $table->index('is_main_guest');
            $table->index('public_visibility');
            $table->index('stay_purpose');
        });

        Schema::create('room_current_occupancy_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('current_occupants_count')->default(0);
            $table->unsignedInteger('current_bookings_count')->default(0);
            $table->unsignedInteger('occupied_sleeping_places_count')->default(0);
            $table->unsignedInteger('free_sleeping_places_count')->default(0);
            $table->unsignedInteger('male_occupants_count')->nullable();
            $table->unsignedInteger('female_occupants_count')->nullable();
            $table->unsignedInteger('unknown_gender_occupants_count')->nullable();
            $table->unsignedInteger('students_count')->default(0);
            $table->unsignedInteger('workers_count')->default(0);
            $table->unsignedInteger('tourists_count')->default(0);
            $table->unsignedInteger('long_term_residents_count')->default(0);
            $table->unsignedInteger('short_term_guests_count')->default(0);
            $table->unsignedInteger('early_wakeup_count')->default(0);
            $table->unsignedInteger('late_sleep_count')->default(0);
            $table->unsignedInteger('night_work_count')->default(0);
            $table->unsignedInteger('smokers_count')->default(0);
            $table->unsignedInteger('non_smokers_count')->default(0);
            $table->unsignedInteger('quiet_preferring_count')->default(0);
            $table->unsignedInteger('social_count')->default(0);
            $table->unsignedInteger('checkout_today_count')->default(0);
            $table->unsignedInteger('checkin_today_count')->default(0);
            $table->unsignedInteger('checkout_this_week_count')->default(0);
            $table->boolean('has_open_complaints')->default(false);
            $table->boolean('has_open_maintenance')->default(false);
            $table->boolean('has_noise_warning')->default(false);
            $table->boolean('has_cleanliness_warning')->default(false);
            $table->timestamp('last_recalculated_at')->nullable();
            $table->timestamps();

            $table->index('property_id');
            $table->index('host_user_id');
            $table->index('current_occupants_count');
            $table->index('free_sleeping_places_count');
            $table->index('has_open_complaints');
            $table->index('has_open_maintenance');
            $table->index('last_recalculated_at');
        });

        Schema::create('property_current_occupancy_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('current_occupants_count')->default(0);
            $table->unsignedInteger('current_bookings_count')->default(0);
            $table->unsignedInteger('occupied_rooms_count')->default(0);
            $table->unsignedInteger('occupied_sleeping_places_count')->default(0);
            $table->unsignedInteger('free_sleeping_places_count')->default(0);
            $table->unsignedInteger('checkout_today_count')->default(0);
            $table->unsignedInteger('checkin_today_count')->default(0);
            $table->unsignedInteger('checkout_this_week_count')->default(0);
            $table->boolean('has_open_complaints')->default(false);
            $table->boolean('has_open_maintenance')->default(false);
            $table->boolean('has_cleaning_needed')->default(false);
            $table->boolean('has_inspection_needed')->default(false);
            $table->timestamp('last_recalculated_at')->nullable();
            $table->timestamps();

            $table->index('host_user_id');
            $table->index('current_occupants_count');
            $table->index('free_sleeping_places_count');
            $table->index('has_open_complaints');
            $table->index('has_open_maintenance');
            $table->index('last_recalculated_at');
        });

        Schema::create('stay_visibility_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_stay_id')->constrained('booking_stays')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('show_public_name')->default(true);
            $table->boolean('show_age_range')->default(true);
            $table->boolean('show_gender_if_room_policy_relevant')->default(true);
            $table->boolean('show_city')->default(true);
            $table->boolean('show_languages')->default(true);
            $table->boolean('show_stay_purpose')->default(true);
            $table->boolean('show_sleep_schedule')->default(false);
            $table->boolean('show_smoking_status')->default(false);
            $table->boolean('show_sociability_level')->default(false);
            $table->boolean('show_checkout_date')->default(true);
            $table->timestamps();

            $table->unique(['booking_stay_id', 'user_id'], 'stay_visibility_preferences_stay_user_unique');
            $table->index('booking_stay_id');
            $table->index('user_id');
        });

        Schema::create('booking_stay_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_stay_id')->constrained('booking_stays')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_stay_id');
            $table->index('booking_id');
            $table->index('new_status');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('booking_stay_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_stay_id')->constrained('booking_stays')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('note_type');
            $table->string('visibility')->default('host_only');
            $table->text('note');
            $table->timestamps();

            $table->index('booking_stay_id');
            $table->index('booking_id');
            $table->index('user_id');
            $table->index('note_type');
            $table->index('visibility');
        });

        Schema::create('booking_stay_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_stay_id')->constrained('booking_stays')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_stay_id');
            $table->index('booking_id');
            $table->index('event_key');
            $table->index('event_type');
            $table->index(['source_type', 'source_id']);
            $table->index('user_id');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_stay_events');
        Schema::dropIfExists('booking_stay_notes');
        Schema::dropIfExists('booking_stay_status_logs');
        Schema::dropIfExists('stay_visibility_preferences');
        Schema::dropIfExists('property_current_occupancy_snapshots');
        Schema::dropIfExists('room_current_occupancy_snapshots');
        Schema::dropIfExists('booking_stay_occupants');
        Schema::dropIfExists('booking_stays');
    }
};
