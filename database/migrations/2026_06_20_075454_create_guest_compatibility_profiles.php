<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_compatibility_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('smokes')->nullable();
            $table->string('smoking_preference')->nullable();
            $table->string('tobacco_smell_sensitivity')->nullable();
            $table->boolean('wakes_up_early')->nullable();
            $table->boolean('wakes_up_late')->nullable();
            $table->boolean('sleeps_early')->nullable();
            $table->boolean('sleeps_late')->nullable();
            $table->boolean('works_at_night')->nullable();
            $table->boolean('studies_at_night')->nullable();
            $table->boolean('returns_late')->nullable();
            $table->boolean('needs_late_entry')->nullable();
            $table->boolean('needs_quiet_at_night')->nullable();
            $table->boolean('sensitive_to_light_at_night')->nullable();
            $table->boolean('sensitive_to_noise_at_night')->nullable();
            $table->boolean('student')->nullable();
            $table->boolean('working')->nullable();
            $table->boolean('remote_worker')->nullable();
            $table->boolean('needs_workspace')->nullable();
            $table->boolean('needs_fast_wifi')->nullable();
            $table->boolean('needs_power_socket')->nullable();
            $table->boolean('needs_online_calls')->nullable();
            $table->boolean('often_home')->nullable();
            $table->boolean('rarely_home')->nullable();
            $table->boolean('mostly_only_sleeps')->nullable();
            $table->boolean('cooks_often')->nullable();
            $table->boolean('needs_kitchen')->nullable();
            $table->boolean('needs_fridge_shelf')->nullable();
            $table->boolean('needs_washing_machine')->nullable();
            $table->string('social_level')->nullable();
            $table->boolean('prefers_private_space')->nullable();
            $table->boolean('comfortable_with_strangers')->nullable();
            $table->string('cleanliness_expectation')->nullable();
            $table->boolean('ready_to_join_cleaning')->nullable();
            $table->boolean('wants_private_room')->nullable();
            $table->boolean('comfortable_with_shared_room')->nullable();
            $table->unsignedTinyInteger('max_people_in_room')->nullable();
            $table->boolean('wants_female_room')->nullable();
            $table->boolean('wants_male_room')->nullable();
            $table->boolean('comfortable_with_mixed_room')->nullable();
            $table->boolean('wants_lower_bunk')->nullable();
            $table->boolean('avoids_upper_bunk')->nullable();
            $table->boolean('avoids_sofa')->nullable();
            $table->boolean('avoids_floor_mattress')->nullable();
            $table->boolean('needs_locker')->nullable();
            $table->boolean('needs_locker_lock')->nullable();
            $table->boolean('needs_luggage_space')->nullable();
            $table->boolean('needs_bedding')->nullable();
            $table->boolean('needs_towel')->nullable();
            $table->boolean('needs_curtain')->nullable();
            $table->boolean('travelling_with_pet')->nullable();
            $table->boolean('avoids_pets')->nullable();
            $table->boolean('has_pet_allergy')->nullable();
            $table->boolean('needs_self_check_in')->nullable();
            $table->boolean('needs_24_7_access')->nullable();
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamps();

            $table->index('smokes');
            $table->index('needs_quiet_at_night');
            $table->index('remote_worker');
            $table->index('needs_workspace');
            $table->index('needs_fast_wifi');
            $table->index('max_people_in_room');
            $table->index('wants_private_room');
            $table->index('comfortable_with_shared_room');
            $table->index('avoids_upper_bunk');
            $table->index('needs_locker');
            $table->index('has_pet_allergy');
            $table->index('travelling_with_pet');
        });

        Schema::create('guest_compatibility_visibility_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('show_smoking_preference')->default(false);
            $table->boolean('show_sleep_schedule')->default(true);
            $table->boolean('show_work_study_status')->default(true);
            $table->boolean('show_home_presence')->default(false);
            $table->boolean('show_social_level')->default(true);
            $table->boolean('show_cleanliness_preference')->default(false);
            $table->boolean('show_room_preferences')->default(true);
            $table->boolean('show_workspace_needs')->default(true);
            $table->boolean('show_pet_preference')->default(false);
            $table->boolean('allow_use_for_matching')->default(true);
            $table->boolean('allow_show_to_hosts')->default(false);
            $table->boolean('allow_show_to_future_roommates')->default(false);
            $table->timestamps();
        });

        Schema::create('room_compatibility_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('gender_policy')->nullable();
            $table->boolean('is_private')->nullable();
            $table->boolean('is_shared')->nullable();
            $table->unsignedTinyInteger('max_people_in_room')->nullable();
            $table->unsignedTinyInteger('current_people_count')->nullable();
            $table->unsignedTinyInteger('typical_people_count')->nullable();
            $table->string('noise_level')->nullable();
            $table->string('light_level')->nullable();
            $table->boolean('quiet_hours_enabled')->nullable();
            $table->string('quiet_hours_start')->nullable();
            $table->string('quiet_hours_end')->nullable();
            $table->boolean('can_turn_light_at_night')->nullable();
            $table->boolean('can_work_at_night')->nullable();
            $table->boolean('can_eat')->nullable();
            $table->boolean('can_store_food')->nullable();
            $table->boolean('has_workspace')->nullable();
            $table->boolean('has_desk')->nullable();
            $table->boolean('has_chair')->nullable();
            $table->boolean('has_personal_lockers')->nullable();
            $table->boolean('has_lock')->nullable();
            $table->boolean('has_window')->nullable();
            $table->boolean('has_air_conditioning')->nullable();
            $table->boolean('has_heating')->nullable();
            $table->boolean('can_open_window')->nullable();
            $table->boolean('smoking_allowed')->nullable();
            $table->boolean('pets_present')->nullable();
            $table->boolean('pets_allowed')->nullable();
            $table->boolean('kitchen_night_use_allowed')->nullable();
            $table->boolean('washing_machine_available')->nullable();
            $table->boolean('long_stay_allowed')->nullable();
            $table->boolean('short_stay_allowed')->nullable();
            $table->boolean('late_entry_allowed')->nullable();
            $table->timestamps();

            $table->index('gender_policy');
            $table->index('is_private');
            $table->index('is_shared');
            $table->index('max_people_in_room');
            $table->index('noise_level');
            $table->index('quiet_hours_enabled');
            $table->index('can_work_at_night');
            $table->index('has_workspace');
            $table->index('has_personal_lockers');
            $table->index('smoking_allowed');
            $table->index('pets_present');
            $table->index('pets_allowed');
            $table->index('long_stay_allowed');
            $table->index('late_entry_allowed');
        });

        Schema::create('sleeping_place_compatibility_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('sleeping_place_type')->nullable();
            $table->boolean('is_top_bunk')->nullable();
            $table->boolean('is_bottom_bunk')->nullable();
            $table->boolean('is_sofa')->nullable();
            $table->boolean('is_floor_mattress')->nullable();
            $table->boolean('is_for_one_person')->nullable();
            $table->boolean('is_for_couple')->nullable();
            $table->boolean('has_curtain')->nullable();
            $table->boolean('has_locker')->nullable();
            $table->boolean('locker_has_lock')->nullable();
            $table->boolean('has_power_socket')->nullable();
            $table->boolean('has_usb_charger')->nullable();
            $table->boolean('has_personal_lamp')->nullable();
            $table->boolean('has_shelf')->nullable();
            $table->boolean('has_luggage_space')->nullable();
            $table->boolean('has_bedding')->nullable();
            $table->boolean('has_towel')->nullable();
            $table->string('privacy_level')->nullable();
            $table->string('noise_level_near_place')->nullable();
            $table->string('light_level_near_place')->nullable();
            $table->boolean('suitable_for_tall_person')->nullable();
            $table->boolean('suitable_for_heavy_person')->nullable();
            $table->boolean('suitable_for_limited_mobility')->nullable();
            $table->unsignedSmallInteger('min_nights')->nullable();
            $table->unsignedSmallInteger('max_nights')->nullable();
            $table->boolean('can_extend')->nullable();
            $table->boolean('instant_booking_enabled')->nullable();
            $table->timestamps();

            $table->index('sleeping_place_type');
            $table->index('is_top_bunk');
            $table->index('is_bottom_bunk');
            $table->index('has_curtain');
            $table->index('has_locker');
            $table->index('locker_has_lock');
            $table->index('has_power_socket');
            $table->index('has_bedding');
            $table->index('has_towel');
            $table->index('privacy_level');
            $table->index('noise_level_near_place');
            $table->index('min_nights');
            $table->index('max_nights');
            $table->index('can_extend');
        });

        Schema::create('compatibility_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->unsignedSmallInteger('nights_count')->nullable();
            $table->unsignedTinyInteger('compatibility_score');
            $table->string('fit_status');
            $table->json('positive_reasons_json')->nullable();
            $table->json('warning_reasons_json')->nullable();
            $table->json('blocking_reasons_json')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('property_id');
            $table->index(['user_id', 'sleeping_place_id']);
            $table->index(['user_id', 'room_id']);
            $table->index(['sleeping_place_id', 'calculated_at']);
            $table->index(['room_id', 'calculated_at']);
            $table->index(['check_in_date', 'check_out_date']);
            $table->index('fit_status');
            $table->index('compatibility_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compatibility_results');
        Schema::dropIfExists('sleeping_place_compatibility_profiles');
        Schema::dropIfExists('room_compatibility_profiles');
        Schema::dropIfExists('guest_compatibility_visibility_settings');
        Schema::dropIfExists('guest_compatibility_profiles');
    }
};
