<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createGeoTables();
        $this->createUserProfileTables();
        $this->extendExistingListingTables();
        $this->createSleepingPlaceTables();
        $this->createAmenityAndRuleTables();
        $this->createBookingAndMoneyTables();
        $this->createSocialTables();
    }

    public function down(): void
    {
        $this->dropExistingTableExtensions();

        Schema::dropIfExists('waitlist_items');
        Schema::dropIfExists('message_threads');
        Schema::dropIfExists('refund_requests');
        Schema::dropIfExists('deposit_records');
        Schema::dropIfExists('payment_records');
        Schema::dropIfExists('booking_status_histories');
        Schema::dropIfExists('booking_price_lines');
        Schema::dropIfExists('booking_guests');
        Schema::dropIfExists('discount_rules');
        Schema::dropIfExists('price_rules');
        Schema::dropIfExists('availability_days');
        Schema::dropIfExists('media_items');
        Schema::dropIfExists('sleeping_place_rule');
        Schema::dropIfExists('room_rule');
        Schema::dropIfExists('property_rule');
        Schema::dropIfExists('sleeping_place_amenity');
        Schema::dropIfExists('room_amenity');
        Schema::dropIfExists('property_amenity');
        Schema::dropIfExists('rule_translations');
        Schema::dropIfExists('rules');
        Schema::dropIfExists('amenity_translations');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('sleeping_place_translations');
        Schema::dropIfExists('sleeping_places');
        Schema::dropIfExists('room_translations');
        Schema::dropIfExists('property_translations');
        Schema::dropIfExists('host_profiles');
        Schema::dropIfExists('guest_preferences');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('countries');
    }

    private function dropExistingTableExtensions(): void
    {
        if (Schema::hasColumn('messages', 'thread_id')) {
            Schema::table('messages', function (Blueprint $table): void {
                $table->dropIndex(['thread_id', 'created_at']);
                $table->dropConstrainedForeignId('thread_id');
                $table->dropColumn('attachments_json');
            });
        }

        if (Schema::hasColumn('reviews', 'sleeping_place_id')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('sleeping_place_id');
            });
        }

        if (Schema::hasColumn('complaints', 'sleeping_place_id')) {
            Schema::table('complaints', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('sleeping_place_id');
            });
        }

        if (Schema::hasColumn('favorites', 'sleeping_place_id')) {
            Schema::table('favorites', function (Blueprint $table): void {
                $table->dropIndex(['user_id', 'sleeping_place_id']);
                $table->dropConstrainedForeignId('sleeping_place_id');
            });
        }

        if (Schema::hasColumn('saved_searches', 'city_id')) {
            Schema::table('saved_searches', function (Blueprint $table): void {
                $table->dropIndex(['locale']);
                $table->dropIndex(['user_id', 'locale']);
                $table->dropIndex(['city_id', 'is_active']);
                $table->dropConstrainedForeignId('city_id');
                $table->dropColumn(['locale', 'filters_json']);
            });
        }

        if (Schema::hasColumn('notifications', 'user_id')) {
            Schema::table('notifications', function (Blueprint $table): void {
                $table->dropIndex(['channel']);
                $table->dropIndex(['status']);
                $table->dropIndex(['user_id', 'read_at', 'created_at']);
                $table->dropConstrainedForeignId('user_id');
                $table->dropColumn(['title_key', 'body_key', 'action_url', 'channel', 'status']);
            });
        }

        if (Schema::hasColumn('bookings', 'guest_user_id')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->dropIndex(['sleeping_place_id', 'check_in_date', 'check_out_date']);
                $table->dropIndex(['status', 'check_in_date']);
                $table->dropIndex(['guest_user_id', 'status']);
                $table->dropIndex(['host_user_id', 'status']);
                $table->dropConstrainedForeignId('guest_user_id');
                $table->dropConstrainedForeignId('host_user_id');
                $table->dropConstrainedForeignId('sleeping_place_id');
                $table->dropColumn([
                    'check_in_date',
                    'check_out_date',
                    'nights_count',
                    'subtotal_amount',
                    'cleaning_fee_amount',
                    'service_fee_amount',
                    'deposit_amount',
                    'total_amount',
                    'refundable_amount',
                    'non_refundable_amount',
                    'host_response',
                    'cancelled_at',
                    'cancellation_reason',
                    'checked_in_at',
                    'checked_out_at',
                ]);
            });
        }

        if (Schema::hasColumn('rooms', 'type')) {
            Schema::table('rooms', function (Blueprint $table): void {
                $table->dropIndex(['type']);
                $table->dropIndex(['gender_policy']);
                $table->dropColumn([
                    'type',
                    'room_number',
                    'floor',
                    'area',
                    'beds_count',
                    'max_guests',
                    'occupied_places_count',
                    'available_places_count',
                    'gender_policy',
                    'min_guest_age',
                    'max_guest_age',
                    'window_view',
                    'has_chair',
                    'has_mirror',
                    'has_air_conditioning',
                    'has_curtains',
                    'has_blackout_curtains',
                    'noise_level',
                    'light_level',
                    'can_eat',
                    'can_work_at_night',
                    'can_turn_light_at_night',
                ]);
            });
        }

        if (Schema::hasColumn('properties', 'host_user_id')) {
            Schema::table('properties', function (Blueprint $table): void {
                $table->dropIndex(['city_id', 'status']);
                $table->dropIndex(['host_user_id', 'status']);
                $table->dropConstrainedForeignId('host_user_id');
                $table->dropConstrainedForeignId('country_id');
                $table->dropConstrainedForeignId('region_id');
                $table->dropConstrainedForeignId('city_id');
                $table->dropColumn([
                    'address_line_1',
                    'address_line_2',
                    'house_number',
                    'apartment_number',
                    'total_floors',
                    'latitude',
                    'longitude',
                    'approximate_latitude',
                    'approximate_longitude',
                    'show_exact_address_before_booking',
                    'show_exact_address_after_payment',
                    'distance_to_center_meters',
                    'total_area',
                    'rooms_count',
                    'bathrooms_count',
                    'showers_count',
                    'kitchens_count',
                    'balconies_count',
                    'max_guests',
                    'current_guests_count',
                    'noise_level',
                    'cleanliness_level',
                    'safety_level',
                    'repair_state',
                    'has_heating',
                    'has_air_conditioning',
                    'has_hot_water',
                    'has_parking',
                    'has_security',
                    'has_cctv_common_areas',
                    'emergency_contact_name',
                    'emergency_contact_phone',
                ]);
            });
        }
    }

    private function createGeoTables(): void
    {
        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('iso3', 3)->nullable()->unique();
            $table->string('name');
            $table->string('name_normalized')->index();
            $table->string('currency_code', 3)->nullable();
            $table->string('phone_code', 12)->nullable();
            $table->string('source')->default('iso_3166');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('name_normalized')->index();
            $table->string('source')->default('geonames');
            $table->string('source_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['country_id', 'code']);
            $table->index(['country_id', 'name_normalized']);
        });

        Schema::create('cities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('name_normalized')->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('population')->nullable();
            $table->string('timezone')->nullable();
            $table->string('source')->default('geonames');
            $table->string('source_id')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['country_id', 'name_normalized']);
            $table->index(['region_id', 'name_normalized']);
        });
    }

    private function createUserProfileTables(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('display_name');
            $table->string('avatar_path')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->index();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->text('about')->nullable();
            $table->json('languages_json')->nullable();
            $table->string('occupation')->nullable();
            $table->string('travel_purpose')->nullable();
            $table->boolean('smokes')->default(false);
            $table->boolean('has_pets')->default(false);
            $table->text('allergies')->nullable();
            $table->boolean('prefers_quiet')->default(false);
            $table->string('sleep_schedule')->nullable();
            $table->string('social_level')->nullable();
            $table->timestamp('identity_verified_at')->nullable();
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('complaints_count')->default(0);
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['city_id', 'status']);
        });

        Schema::create('guest_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('preferred_budget_min', 10, 2)->nullable();
            $table->decimal('preferred_budget_max', 10, 2)->nullable();
            $table->string('preferred_currency', 3)->default('EUR');
            $table->string('preferred_room_type')->nullable();
            $table->string('preferred_sleeping_place_type')->nullable();
            $table->boolean('wants_wifi')->default(false);
            $table->boolean('wants_kitchen')->default(false);
            $table->boolean('wants_washing_machine')->default(false);
            $table->boolean('wants_locker')->default(false);
            $table->boolean('wants_lower_bunk')->default(false);
            $table->boolean('avoids_mixed_room')->default(false);
            $table->boolean('avoids_smoking')->default(false);
            $table->boolean('avoids_pets')->default(false);
            $table->boolean('needs_late_check_in')->default(false);
            $table->boolean('needs_workspace')->default(false);
            $table->boolean('needs_quiet_hours')->default(false);
            $table->json('accessibility_needs_json')->nullable();
            $table->timestamps();
        });

        Schema::create('host_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('display_name');
            $table->string('avatar_path')->nullable();
            $table->text('about')->nullable();
            $table->json('languages_json')->nullable();
            $table->unsignedInteger('response_time_minutes')->nullable();
            $table->unsignedTinyInteger('response_rate')->default(0);
            $table->boolean('lives_in_property')->default(false);
            $table->boolean('lives_nearby')->default(false);
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('cancellations_count')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    private function extendExistingListingTables(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'host_user_id')) {
                $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('properties', 'country_id')) {
                $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('properties', 'region_id')) {
                $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('properties', 'city_id')) {
                $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('properties', 'address_line_1')) {
                $table->string('address_line_1')->nullable();
                $table->string('address_line_2')->nullable();
                $table->string('house_number')->nullable();
                $table->string('apartment_number')->nullable();
                $table->unsignedTinyInteger('total_floors')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->decimal('approximate_latitude', 10, 7)->nullable();
                $table->decimal('approximate_longitude', 10, 7)->nullable();
                $table->boolean('show_exact_address_before_booking')->default(false);
                $table->boolean('show_exact_address_after_payment')->default(true);
                $table->unsignedInteger('distance_to_center_meters')->nullable();
                $table->decimal('total_area', 8, 2)->nullable();
                $table->unsignedTinyInteger('rooms_count')->default(0);
                $table->unsignedTinyInteger('bathrooms_count')->default(0);
                $table->unsignedTinyInteger('showers_count')->default(0);
                $table->unsignedTinyInteger('kitchens_count')->default(0);
                $table->unsignedTinyInteger('balconies_count')->default(0);
                $table->unsignedSmallInteger('max_guests')->default(1);
                $table->unsignedSmallInteger('current_guests_count')->default(0);
                $table->string('noise_level')->nullable();
                $table->string('cleanliness_level')->nullable();
                $table->string('safety_level')->nullable();
                $table->string('repair_state')->nullable();
                $table->boolean('has_heating')->default(true);
                $table->boolean('has_air_conditioning')->default(false);
                $table->boolean('has_hot_water')->default(true);
                $table->boolean('has_parking')->default(false);
                $table->boolean('has_security')->default(false);
                $table->boolean('has_cctv_common_areas')->default(false);
                $table->string('emergency_contact_name')->nullable();
                $table->string('emergency_contact_phone')->nullable();
            }

            $table->index(['city_id', 'status']);
            $table->index(['host_user_id', 'status']);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            if (! Schema::hasColumn('rooms', 'type')) {
                $table->string('type')->default('shared')->index();
                $table->string('room_number')->nullable();
                $table->unsignedTinyInteger('floor')->nullable();
                $table->decimal('area', 6, 2)->nullable();
                $table->unsignedTinyInteger('beds_count')->default(0);
                $table->unsignedTinyInteger('max_guests')->default(1);
                $table->unsignedTinyInteger('occupied_places_count')->default(0);
                $table->unsignedTinyInteger('available_places_count')->default(0);
                $table->string('gender_policy')->default('mixed')->index();
                $table->unsignedTinyInteger('min_guest_age')->nullable();
                $table->unsignedTinyInteger('max_guest_age')->nullable();
                $table->string('window_view')->nullable();
                $table->boolean('has_chair')->default(false);
                $table->boolean('has_mirror')->default(false);
                $table->boolean('has_air_conditioning')->default(false);
                $table->boolean('has_curtains')->default(false);
                $table->boolean('has_blackout_curtains')->default(false);
                $table->string('noise_level')->nullable();
                $table->string('light_level')->nullable();
                $table->boolean('can_eat')->default(false);
                $table->boolean('can_work_at_night')->default(false);
                $table->boolean('can_turn_light_at_night')->default(false);
            }
        });
    }

    private function createSleepingPlaceTables(): void
    {
        Schema::create('sleeping_places', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('status')->default('active')->index();
            $table->string('place_number')->nullable();
            $table->string('bunk_level')->nullable();
            $table->unsignedSmallInteger('length_cm')->nullable();
            $table->unsignedSmallInteger('width_cm')->nullable();
            $table->string('mattress_type')->nullable();
            $table->string('mattress_firmness')->nullable();
            $table->boolean('has_pillow')->default(true);
            $table->boolean('has_blanket')->default(true);
            $table->boolean('has_bedding')->default(true);
            $table->boolean('has_towel')->default(false);
            $table->boolean('has_curtain')->default(false);
            $table->boolean('has_lamp')->default(false);
            $table->boolean('has_power_socket')->default(false);
            $table->boolean('has_usb')->default(false);
            $table->boolean('has_shelf')->default(false);
            $table->boolean('has_locker')->default(false);
            $table->boolean('locker_has_lock')->default(false);
            $table->boolean('has_luggage_space')->default(false);
            $table->boolean('is_accessible')->default(false);
            $table->boolean('suitable_for_tall_person')->default(false);
            $table->boolean('suitable_for_elderly')->default(false);
            $table->unsignedTinyInteger('max_guests')->default(1);
            $table->unsignedTinyInteger('min_guest_age')->nullable();
            $table->unsignedTinyInteger('max_guest_age')->nullable();
            $table->decimal('base_price_per_night', 10, 2);
            $table->decimal('weekly_price', 10, 2)->nullable();
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->decimal('weekend_price', 10, 2)->nullable();
            $table->decimal('holiday_price', 10, 2)->nullable();
            $table->decimal('cleaning_fee', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->unsignedSmallInteger('min_nights')->default(1);
            $table->unsignedSmallInteger('max_nights')->nullable();
            $table->boolean('instant_booking_enabled')->default(false);
            $table->boolean('requires_host_approval')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['room_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['status', 'base_price_per_night']);
        });

        Schema::create('property_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->text('neighborhood_description')->nullable();
            $table->text('getting_there')->nullable();
            $table->text('what_guests_like')->nullable();
            $table->text('what_to_know')->nullable();
            $table->text('suitable_for')->nullable();
            $table->text('not_suitable_for')->nullable();
            $table->text('check_in_instructions')->nullable();
            $table->text('check_out_instructions')->nullable();
            $table->text('house_rules_text')->nullable();
            $table->text('safety_notes')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'locale']);
            $table->index('locale');
        });

        Schema::create('room_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->text('sleeping_arrangement')->nullable();
            $table->text('privacy_notes')->nullable();
            $table->timestamps();

            $table->unique(['room_id', 'locale']);
            $table->index('locale');
        });

        Schema::create('sleeping_place_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->text('privacy_notes')->nullable();
            $table->text('accessibility_notes')->nullable();
            $table->timestamps();

            $table->unique(['sleeping_place_id', 'locale']);
            $table->index('locale');
        });

        Schema::create('availability_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('available')->index();
            $table->decimal('price_override', 10, 2)->nullable();
            $table->unsignedSmallInteger('min_nights_override')->nullable();
            $table->unsignedSmallInteger('max_nights_override')->nullable();
            $table->boolean('check_in_allowed')->default(true);
            $table->boolean('check_out_allowed')->default(true);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['sleeping_place_id', 'date']);
            $table->index(['sleeping_place_id', 'date', 'status']);
        });

        Schema::create('price_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->unsignedSmallInteger('min_nights')->nullable();
            $table->json('days_of_week_json')->nullable();
            $table->unsignedTinyInteger('priority')->default(0);
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['sleeping_place_id', 'starts_on', 'ends_on']);
        });

        Schema::create('discount_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->unsignedSmallInteger('min_nights')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['sleeping_place_id', 'starts_on', 'ends_on']);
        });

        Schema::create('media_items', function (Blueprint $table): void {
            $table->id();
            $table->morphs('mediable');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('collection')->default('gallery')->index();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id', 'collection']);
        });
    }

    private function createAmenityAndRuleTables(): void
    {
        Schema::create('amenities', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_normalized')->index();
            $table->string('category')->nullable()->index();
            $table->string('icon')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('amenity_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('name_normalized')->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['amenity_id', 'locale']);
        });

        Schema::create('rules', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_normalized')->index();
            $table->string('category')->nullable()->index();
            $table->boolean('requires_confirmation')->default(false);
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('rule_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rule_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('name_normalized')->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['rule_id', 'locale']);
        });

        $owners = [
            'property' => 'properties',
            'room' => 'rooms',
            'sleeping_place' => 'sleeping_places',
        ];

        foreach ($owners as $owner => $tableName) {
            Schema::create($owner.'_amenity', function (Blueprint $table) use ($owner, $tableName): void {
                $table->id();
                $table->foreignId($owner.'_id')->constrained($tableName)->cascadeOnDelete();
                $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique([$owner.'_id', 'amenity_id']);
            });

            Schema::create($owner.'_rule', function (Blueprint $table) use ($owner, $tableName): void {
                $table->id();
                $table->foreignId($owner.'_id')->constrained($tableName)->cascadeOnDelete();
                $table->foreignId('rule_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique([$owner.'_id', 'rule_id']);
            });
        }
    }

    private function createBookingAndMoneyTables(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (! Schema::hasColumn('bookings', 'guest_user_id')) {
                $table->foreignId('guest_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
                $table->date('check_in_date')->nullable();
                $table->date('check_out_date')->nullable();
                $table->unsignedSmallInteger('nights_count')->default(1);
                $table->decimal('subtotal_amount', 10, 2)->default(0);
                $table->decimal('cleaning_fee_amount', 10, 2)->default(0);
                $table->decimal('service_fee_amount', 10, 2)->default(0);
                $table->decimal('deposit_amount', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->decimal('refundable_amount', 10, 2)->default(0);
                $table->decimal('non_refundable_amount', 10, 2)->default(0);
                $table->text('host_response')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->text('cancellation_reason')->nullable();
                $table->timestamp('checked_in_at')->nullable();
                $table->timestamp('checked_out_at')->nullable();
            }

            $table->index(['sleeping_place_id', 'check_in_date', 'check_out_date']);
            $table->index(['status', 'check_in_date']);
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
        });

        Schema::create('booking_guests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('document_type')->nullable();
            $table->string('document_last_four')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'user_id']);
        });

        Schema::create('booking_price_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('label_key');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_refundable')->default(false);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status')->index();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'created_at']);
        });

        Schema::create('payment_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider')->default('manual');
            $table->string('provider_reference')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('deposit_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('held')->index();
            $table->timestamp('held_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->decimal('withheld_amount', 10, 2)->default(0);
            $table->text('withhold_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('refund_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('reason')->nullable();
            $table->text('details')->nullable();
            $table->string('status')->default('requested')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    private function createSocialTables(): void
    {
        Schema::table('favorites', function (Blueprint $table): void {
            if (! Schema::hasColumn('favorites', 'sleeping_place_id')) {
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
            }

            $table->index(['user_id', 'sleeping_place_id']);
        });

        Schema::table('saved_searches', function (Blueprint $table): void {
            if (! Schema::hasColumn('saved_searches', 'city_id')) {
                $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
                $table->string('locale', 10)->default('en')->index();
                $table->json('filters_json')->nullable();
            }

            $table->index(['user_id', 'locale']);
            $table->index(['city_id', 'is_active']);
        });

        Schema::create('waitlist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->date('desired_check_in')->nullable();
            $table->date('desired_check_out')->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->boolean('ready_to_book')->default(false);
            $table->boolean('auto_request')->default(false);
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->string('status')->default('waiting')->index();
            $table->timestamps();

            $table->unique(['user_id', 'sleeping_place_id']);
        });

        Schema::create('message_threads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->string('status')->default('open')->index();
            $table->timestamps();

            $table->unique(['guest_user_id', 'host_user_id', 'booking_id']);
            $table->index(['guest_user_id', 'last_message_at']);
            $table->index(['host_user_id', 'last_message_at']);
        });

        Schema::table('messages', function (Blueprint $table): void {
            if (! Schema::hasColumn('messages', 'thread_id')) {
                $table->foreignId('thread_id')->nullable()->constrained('message_threads')->nullOnDelete();
                $table->json('attachments_json')->nullable();
            }

            $table->index(['thread_id', 'created_at']);
        });

        Schema::table('reviews', function (Blueprint $table): void {
            if (! Schema::hasColumn('reviews', 'sleeping_place_id')) {
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
            }
        });

        Schema::table('complaints', function (Blueprint $table): void {
            if (! Schema::hasColumn('complaints', 'sleeping_place_id')) {
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
            }
        });

        Schema::table('notifications', function (Blueprint $table): void {
            if (! Schema::hasColumn('notifications', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('title_key')->nullable();
                $table->string('body_key')->nullable();
                $table->string('action_url')->nullable();
                $table->string('channel')->default('database')->index();
                $table->string('status')->default('unread')->index();
            }

            $table->index(['user_id', 'read_at', 'created_at']);
        });
    }
};
