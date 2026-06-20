<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendUsers();
        $this->extendUserProfiles();
        $this->createGuestProfiles();
        $this->extendHostProfiles();
        $this->createHostRepresentatives();
        $this->createUserVerifications();
        $this->createUserDocuments();
        $this->createUserLanguages();
        $this->createUserPrivacySettings();
        $this->createUserSavedPreferences();
        $this->createUserActivitySummaries();
        $this->extendGuestCompatibilityProfiles();
        $this->extendBookingGuestIntakes();
        $this->createUserNotificationPreferences();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('user_activity_summaries');
        Schema::dropIfExists('user_saved_preferences');
        Schema::dropIfExists('user_privacy_settings');
        Schema::dropIfExists('user_languages');
        Schema::dropIfExists('user_documents');
        Schema::dropIfExists('user_verifications');
        Schema::dropIfExists('host_representatives');
        Schema::dropIfExists('guest_profiles');

        $this->dropBookingGuestIntakeExtensions();
        $this->dropGuestCompatibilityProfileExtensions();
        $this->dropHostProfileExtensions();
        $this->dropUserProfileExtensions();
        $this->dropUserExtensions();
    }

    private function extendUsers(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('last_active_at')->index();
            }

            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('last_seen_at')->index();
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status')->index();
            }
        });
    }

    private function extendUserProfiles(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_profiles', 'first_name')) {
                $table->string('first_name')->nullable()->after('display_name');
                $table->string('last_name')->nullable()->after('first_name');
                $table->string('public_name')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('user_profiles', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('avatar_path');
                $table->string('age_range_public')->nullable()->after('birth_date');
            }

            if (! Schema::hasColumn('user_profiles', 'gender_public')) {
                $table->boolean('gender_public')->default(false)->after('gender');
            }

            if (! Schema::hasColumn('user_profiles', 'public_city_name')) {
                $table->string('public_city_name')->nullable()->after('city_id')->index();
            }

            if (! Schema::hasColumn('user_profiles', 'education')) {
                $table->string('education')->nullable()->after('occupation');
                $table->string('languages_text')->nullable()->after('education');
                $table->timestamp('profile_completed_at')->nullable()->after('languages_text');
            }

            if (! Schema::hasIndex('user_profiles', ['city_id'])) {
                $table->index('city_id');
            }
        });
    }

    private function createGuestProfiles(): void
    {
        if (Schema::hasTable('guest_profiles')) {
            return;
        }

        Schema::create('guest_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('travel_purpose_default')->nullable();
            $table->string('preferred_check_in_time')->nullable();
            $table->string('preferred_check_out_time')->nullable();
            $table->boolean('has_large_luggage')->default(false);
            $table->boolean('needs_luggage_storage')->default(false);
            $table->boolean('needs_quiet_place')->default(false)->index();
            $table->boolean('needs_desk')->default(false)->index();
            $table->boolean('needs_fast_wifi')->default(false)->index();
            $table->boolean('needs_registration')->default(false);
            $table->boolean('needs_work_documents')->default(false);
            $table->boolean('smokes')->default(false)->index();
            $table->boolean('travels_with_pet')->default(false)->index();
            $table->text('pet_description')->nullable();
            $table->boolean('prefers_private_room')->default(false);
            $table->boolean('accepts_shared_room')->default(true)->index();
            $table->boolean('accepts_living_with_strangers')->default(true);
            $table->unsignedTinyInteger('max_people_in_room_preference')->nullable();
            $table->boolean('long_stay_interested')->default(false)->index();
            $table->boolean('short_stay_interested')->default(true);
            $table->string('night_schedule')->nullable();
            $table->boolean('early_wakeup')->default(false);
            $table->boolean('late_sleep')->default(false);
            $table->boolean('works_remotely')->default(false);
            $table->boolean('studies')->default(false);
            $table->boolean('often_at_home')->default(false);
            $table->boolean('rarely_at_home')->default(false);
            $table->string('sociability_level')->nullable();
            $table->string('cleanliness_expectation')->nullable();
            $table->boolean('ready_to_join_cleaning')->default(false);
            $table->timestamps();
        });
    }

    private function extendHostProfiles(): void
    {
        Schema::table('host_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('host_profiles', 'host_display_name')) {
                $table->string('host_display_name')->nullable()->after('display_name');
            }

            if (! Schema::hasColumn('host_profiles', 'host_type')) {
                $table->string('host_type')->default('individual')->after('host_display_name');
                $table->text('about_host')->nullable()->after('about');
                $table->decimal('acceptance_rate', 5, 2)->nullable()->after('response_rate');
                $table->unsignedInteger('successful_check_ins_count')->default(0)->after('acceptance_rate');
                $table->unsignedInteger('host_cancellations_count')->default(0)->after('successful_check_ins_count');
                $table->unsignedInteger('complaints_count')->default(0)->after('host_cancellations_count');
                $table->boolean('verified_host')->default(false)->after('complaints_count');
                $table->date('hosting_since')->nullable()->after('verified_host');
                $table->string('default_currency', 3)->nullable()->after('hosting_since');
                $table->string('default_language', 10)->nullable()->after('default_currency');
                $table->boolean('public_phone_visible')->default(false)->after('default_language');
                $table->boolean('public_email_visible')->default(false)->after('public_phone_visible');
                $table->string('emergency_contact_name')->nullable()->after('public_email_visible');
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
                $table->string('representative_name')->nullable()->after('emergency_contact_phone');
                $table->string('representative_contact')->nullable()->after('representative_name');
                $table->boolean('representative_visible_to_guest')->default(false)->after('representative_contact');
            }

            if (! Schema::hasIndex('host_profiles', ['verified_host'])) {
                $table->index('verified_host');
            }
            if (! Schema::hasIndex('host_profiles', ['response_rate'])) {
                $table->index('response_rate');
            }
            if (! Schema::hasIndex('host_profiles', ['acceptance_rate'])) {
                $table->index('acceptance_rate');
            }
        });
    }

    private function createHostRepresentatives(): void
    {
        if (Schema::hasTable('host_representatives')) {
            return;
        }

        Schema::create('host_representatives', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('representative_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('role_description')->nullable();
            $table->boolean('can_help_with_check_in')->default(false)->index();
            $table->boolean('can_help_with_keys')->default(false);
            $table->boolean('can_help_with_cleaning_coordination')->default(false);
            $table->boolean('can_be_contacted_by_guest')->default(false);
            $table->boolean('visible_after_booking_only')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['host_user_id', 'active']);
            $table->index('representative_user_id');
        });
    }

    private function createUserVerifications(): void
    {
        if (Schema::hasTable('user_verifications')) {
            return;
        }

        Schema::create('user_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('verification_type');
            $table->string('status')->default('not_required');
            $table->string('provider')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'verification_type']);
            $table->index(['user_id', 'status']);
            $table->index(['verification_type', 'status']);
        });
    }

    private function createUserDocuments(): void
    {
        if (Schema::hasTable('user_documents')) {
            return;
        }

        Schema::create('user_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('status')->default('pending');
            $table->string('file_path');
            $table->boolean('encrypted')->default(true);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'document_type']);
            $table->index(['user_id', 'status']);
        });
    }

    private function createUserLanguages(): void
    {
        if (Schema::hasTable('user_languages')) {
            return;
        }

        Schema::create('user_languages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('language_code', 10);
            $table->string('level')->default('basic');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'language_code']);
            $table->index('language_code');
        });
    }

    private function createUserPrivacySettings(): void
    {
        if (Schema::hasTable('user_privacy_settings')) {
            return;
        }

        Schema::create('user_privacy_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('show_real_name')->default(false);
            $table->boolean('show_avatar')->default(true);
            $table->boolean('show_age_range')->default(true);
            $table->boolean('show_gender')->default(false);
            $table->boolean('show_city')->default(true);
            $table->boolean('show_languages')->default(true);
            $table->boolean('show_rating')->default(true);
            $table->boolean('show_completed_stays_count')->default(true);
            $table->boolean('show_reviews_count')->default(true);
            $table->boolean('show_phone_after_booking')->default(true);
            $table->boolean('show_email_after_booking')->default(false);
            $table->boolean('show_identity_verified_badge')->default(true);
            $table->boolean('allow_host_to_see_guest_profile')->default(true);
            $table->boolean('allow_guest_to_see_host_contact_after_booking')->default(true);
            $table->timestamps();
        });
    }

    private function createUserSavedPreferences(): void
    {
        if (Schema::hasTable('user_saved_preferences')) {
            return;
        }

        Schema::create('user_saved_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('preferred_currency', 3)->nullable();
            $table->string('preferred_locale', 10)->default('en');
            $table->string('preferred_timezone')->nullable();
            $table->string('distance_unit')->default('km');
            $table->string('price_display_mode')->default('both');
            $table->string('date_format')->nullable();
            $table->string('time_format')->nullable();
            $table->boolean('mobile_compact_mode')->default(true);
            $table->boolean('show_total_price_with_deposit')->default(true);
            $table->boolean('show_total_price_without_deposit')->default(true);
            $table->timestamps();
        });
    }

    private function createUserActivitySummaries(): void
    {
        if (Schema::hasTable('user_activity_summaries')) {
            return;
        }

        Schema::create('user_activity_summaries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('completed_stays_as_guest')->default(0)->index();
            $table->unsignedInteger('completed_stays_as_host')->default(0)->index();
            $table->unsignedInteger('cancelled_by_guest_count')->default(0);
            $table->unsignedInteger('cancelled_by_host_count')->default(0);
            $table->unsignedInteger('no_show_count')->default(0);
            $table->unsignedInteger('complaints_submitted_count')->default(0);
            $table->unsignedInteger('complaints_received_count')->default(0);
            $table->unsignedInteger('confirmed_complaints_count')->default(0);
            $table->unsignedInteger('reviews_received_count')->default(0);
            $table->unsignedInteger('reviews_left_count')->default(0);
            $table->decimal('average_guest_rating', 3, 2)->nullable()->index();
            $table->decimal('average_host_rating', 3, 2)->nullable()->index();
            $table->unsignedInteger('average_response_time_minutes')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }

    private function extendGuestCompatibilityProfiles(): void
    {
        Schema::table('guest_compatibility_profiles', function (Blueprint $table): void {
            $columns = [
                'i_smoke',
                'i_do_not_smoke',
                'i_wake_up_early',
                'i_sleep_late',
                'i_work_at_night',
                'i_study',
                'i_work_remotely',
                'i_often_stay_home',
                'i_rarely_stay_home',
                'i_like_quiet',
                'i_am_ok_with_noise',
                'i_am_social',
                'i_prefer_not_to_socialize',
                'i_like_cleanliness',
                'i_am_ready_to_help_cleaning',
                'i_accept_living_with_strangers',
                'i_do_not_want_many_people',
                'i_want_private_room',
                'i_accept_shared_room',
                'i_need_desk',
                'i_need_fast_internet',
                'i_need_locker',
                'i_need_quiet_at_night',
                'i_need_late_entry',
                'i_travel_with_pet',
            ];

            foreach ($columns as $column) {
                if (! Schema::hasColumn('guest_compatibility_profiles', $column)) {
                    $table->boolean($column)->default(in_array($column, [
                        'i_accept_living_with_strangers',
                        'i_accept_shared_room',
                    ], true));
                }
            }

            foreach ([
                'i_smoke',
                'i_like_quiet',
                'i_work_remotely',
                'i_need_fast_internet',
                'i_travel_with_pet',
            ] as $indexedColumn) {
                if (! Schema::hasIndex('guest_compatibility_profiles', [$indexedColumn])) {
                    $table->index($indexedColumn);
                }
            }
        });
    }

    private function extendBookingGuestIntakes(): void
    {
        Schema::table('booking_guest_intakes', function (Blueprint $table): void {
            if (! Schema::hasColumn('booking_guest_intakes', 'booking_quote_id')) {
                $table->unsignedBigInteger('booking_quote_id')->nullable()->after('id')->index();
            }

            if (! Schema::hasColumn('booking_guest_intakes', 'booking_request_id')) {
                $table->unsignedBigInteger('booking_request_id')->nullable()->after('booking_quote_id')->index();
            }

            if (! Schema::hasColumn('booking_guest_intakes', 'guest_user_id')) {
                $table->foreignId('guest_user_id')->nullable()->after('booking_request_id')->constrained('users')->nullOnDelete();
                $table->index('guest_user_id');
            }

            if (! Schema::hasColumn('booking_guest_intakes', 'needs_early_check_in')) {
                $table->boolean('needs_early_check_in')->default(false)->after('planned_departure_time');
            }

            if (! Schema::hasColumn('booking_guest_intakes', 'needs_late_check_out')) {
                $table->boolean('needs_late_check_out')->default(false)->after('needs_early_check_in');
            }

            if (! Schema::hasColumn('booking_guest_intakes', 'luggage_amount')) {
                $table->string('luggage_amount')->nullable()->after('needs_late_check_out');
            }

            if (! Schema::hasColumn('booking_guest_intakes', 'needs_desk')) {
                $table->boolean('needs_desk')->default(false)->after('needs_quiet');
            }

            if (! Schema::hasColumn('booking_guest_intakes', 'message_to_host')) {
                $table->text('message_to_host')->nullable()->after('special_requests');
            }
        });
    }

    private function createUserNotificationPreferences(): void
    {
        if (Schema::hasTable('user_notification_preferences')) {
            return;
        }

        Schema::create('user_notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('channel');
            $table->boolean('enabled')->default(true);
            $table->boolean('urgent_allowed')->default(true);
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->string('quiet_hours_from')->nullable();
            $table->string('quiet_hours_to')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'channel']);
            $table->index(['user_id', 'category', 'channel']);
        });
    }

    private function dropUserExtensions(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['last_seen_at', 'last_login_at', 'is_active'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function dropUserProfileExtensions(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            $columns = [
                'first_name',
                'last_name',
                'public_name',
                'birth_date',
                'age_range_public',
                'gender_public',
                'public_city_name',
                'education',
                'languages_text',
                'profile_completed_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('user_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function dropHostProfileExtensions(): void
    {
        Schema::table('host_profiles', function (Blueprint $table): void {
            $columns = [
                'host_display_name',
                'host_type',
                'about_host',
                'acceptance_rate',
                'successful_check_ins_count',
                'host_cancellations_count',
                'complaints_count',
                'verified_host',
                'hosting_since',
                'default_currency',
                'default_language',
                'public_phone_visible',
                'public_email_visible',
                'emergency_contact_name',
                'emergency_contact_phone',
                'representative_name',
                'representative_contact',
                'representative_visible_to_guest',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('host_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function dropGuestCompatibilityProfileExtensions(): void
    {
        Schema::table('guest_compatibility_profiles', function (Blueprint $table): void {
            foreach ([
                'i_smoke',
                'i_do_not_smoke',
                'i_wake_up_early',
                'i_sleep_late',
                'i_work_at_night',
                'i_study',
                'i_work_remotely',
                'i_often_stay_home',
                'i_rarely_stay_home',
                'i_like_quiet',
                'i_am_ok_with_noise',
                'i_am_social',
                'i_prefer_not_to_socialize',
                'i_like_cleanliness',
                'i_am_ready_to_help_cleaning',
                'i_accept_living_with_strangers',
                'i_do_not_want_many_people',
                'i_want_private_room',
                'i_accept_shared_room',
                'i_need_desk',
                'i_need_fast_internet',
                'i_need_locker',
                'i_need_quiet_at_night',
                'i_need_late_entry',
                'i_travel_with_pet',
            ] as $column) {
                if (Schema::hasColumn('guest_compatibility_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function dropBookingGuestIntakeExtensions(): void
    {
        Schema::table('booking_guest_intakes', function (Blueprint $table): void {
            foreach ([
                'booking_quote_id',
                'booking_request_id',
                'guest_user_id',
                'needs_early_check_in',
                'needs_late_check_out',
                'luggage_amount',
                'needs_desk',
                'message_to_host',
            ] as $column) {
                if (Schema::hasColumn('booking_guest_intakes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
