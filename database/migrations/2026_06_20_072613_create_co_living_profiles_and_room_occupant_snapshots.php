<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('co_living_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('public_alias')->nullable();
            $table->string('age_range')->nullable();
            $table->string('gender_for_room_policy')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->json('languages_json')->nullable();
            $table->string('stay_purpose')->nullable();
            $table->string('guest_type')->nullable();
            $table->boolean('tourist')->nullable();
            $table->boolean('student')->nullable();
            $table->boolean('working')->nullable();
            $table->boolean('remote_worker')->nullable();
            $table->boolean('long_term_guest')->nullable();
            $table->boolean('short_term_guest')->nullable();
            $table->string('sleep_schedule')->nullable();
            $table->string('wake_schedule')->nullable();
            $table->string('home_presence_level')->nullable();
            $table->boolean('smokes')->nullable();
            $table->string('smoking_location')->nullable();
            $table->boolean('has_pet')->nullable();
            $table->string('social_level')->nullable();
            $table->boolean('prefers_quiet')->nullable();
            $table->string('cleanliness_level')->nullable();
            $table->boolean('participates_in_cleaning')->nullable();
            $table->boolean('respects_personal_space')->nullable();
            $table->decimal('roommate_rating_average', 3, 2)->nullable();
            $table->unsignedInteger('roommate_reviews_count')->default(0);
            $table->unsignedInteger('roommate_complaints_count')->default(0);
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamps();

            $table->index('country_id', 'co_living_profiles_country_index');
            $table->index('city_id', 'co_living_profiles_city_index');
            $table->index('stay_purpose', 'co_living_profiles_stay_purpose_index');
            $table->index('guest_type', 'co_living_profiles_guest_type_index');
            $table->index('sleep_schedule', 'co_living_profiles_sleep_schedule_index');
            $table->index('home_presence_level', 'co_living_profiles_home_presence_index');
            $table->index('smokes', 'co_living_profiles_smokes_index');
            $table->index('prefers_quiet', 'co_living_profiles_prefers_quiet_index');
            $table->index('roommate_rating_average', 'co_living_profiles_rating_index');
        });

        Schema::create('co_living_visibility_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('show_public_alias')->default(true);
            $table->boolean('show_real_first_name')->default(false);
            $table->boolean('show_avatar')->default(false);
            $table->boolean('show_age_range')->default(true);
            $table->boolean('show_gender_if_room_policy')->default(true);
            $table->boolean('show_country')->default(false);
            $table->boolean('show_city')->default(false);
            $table->boolean('show_languages')->default(true);
            $table->boolean('show_stay_purpose')->default(true);
            $table->boolean('show_guest_type')->default(true);
            $table->boolean('show_sleep_schedule')->default(true);
            $table->boolean('show_wake_schedule')->default(false);
            $table->boolean('show_home_presence')->default(true);
            $table->boolean('show_smoking_status')->default(true);
            $table->boolean('show_pet_status')->default(false);
            $table->boolean('show_social_level')->default(true);
            $table->boolean('show_quiet_preference')->default(true);
            $table->boolean('show_cleanliness_level')->default(false);
            $table->boolean('show_roommate_rating')->default(true);
            $table->boolean('show_checkout_date_to_future_roommates')->default(true);
            $table->boolean('allow_profile_in_prebooking_summary')->default(true);
            $table->boolean('allow_profile_after_confirmed_booking')->default(true);
            $table->timestamps();
        });

        Schema::create('room_occupant_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('upcoming');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('public_alias_snapshot')->nullable();
            $table->string('age_range_snapshot')->nullable();
            $table->string('gender_for_room_policy_snapshot')->nullable();
            $table->string('country_label_snapshot')->nullable();
            $table->string('city_label_snapshot')->nullable();
            $table->json('languages_json_snapshot')->nullable();
            $table->string('stay_purpose_snapshot')->nullable();
            $table->string('guest_type_snapshot')->nullable();
            $table->boolean('tourist_snapshot')->nullable();
            $table->boolean('student_snapshot')->nullable();
            $table->boolean('working_snapshot')->nullable();
            $table->boolean('remote_worker_snapshot')->nullable();
            $table->boolean('long_term_guest_snapshot')->nullable();
            $table->boolean('short_term_guest_snapshot')->nullable();
            $table->string('sleep_schedule_snapshot')->nullable();
            $table->string('wake_schedule_snapshot')->nullable();
            $table->string('home_presence_level_snapshot')->nullable();
            $table->boolean('smokes_snapshot')->nullable();
            $table->string('social_level_snapshot')->nullable();
            $table->boolean('prefers_quiet_snapshot')->nullable();
            $table->decimal('roommate_rating_average_snapshot', 3, 2)->nullable();
            $table->unsignedInteger('roommate_reviews_count_snapshot')->default(0);
            $table->string('privacy_level')->nullable();
            $table->boolean('can_show_before_booking')->default(true);
            $table->boolean('can_show_after_booking')->default(true);
            $table->timestamps();

            $table->index(['room_id', 'status'], 'room_occupant_snapshots_room_status_index');
            $table->index(['room_id', 'check_in_date', 'check_out_date'], 'room_occupant_snapshots_room_dates_index');
            $table->index('booking_id', 'room_occupant_snapshots_booking_index');
            $table->index('user_id', 'room_occupant_snapshots_user_index');
            $table->index('sleeping_place_id', 'room_occupant_snapshots_place_index');
            $table->index(['status', 'check_out_date'], 'room_occupant_snapshots_status_checkout_index');
            $table->index('can_show_before_booking', 'room_occupant_snapshots_before_booking_index');
            $table->index('can_show_after_booking', 'room_occupant_snapshots_after_booking_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_occupant_snapshots');
        Schema::dropIfExists('co_living_visibility_settings');
        Schema::dropIfExists('co_living_profiles');
    }
};
