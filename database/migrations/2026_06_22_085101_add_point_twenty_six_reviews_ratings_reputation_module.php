<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createReviewPolicies();
        $this->createReviewRequests();
        $this->extendReviews();
        $this->createReviewDetails();
        $this->createRatingTables();
        $this->createReviewAuditTables();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_events');
        Schema::dropIfExists('review_status_logs');
        Schema::dropIfExists('property_rating_snapshots');
        Schema::dropIfExists('room_rating_snapshots');
        Schema::dropIfExists('sleeping_place_rating_snapshots');
        Schema::dropIfExists('guest_reputation_snapshots');
        Schema::dropIfExists('host_reputation_snapshots');
        Schema::dropIfExists('rating_aggregates');
        Schema::dropIfExists('rating_events');
        Schema::dropIfExists('roommate_experience_reviews');
        Schema::dropIfExists('review_responses');
        Schema::dropIfExists('review_media');
        Schema::dropIfExists('review_scores');
        Schema::dropIfExists('review_requests');
        Schema::dropIfExists('review_policies');

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                foreach ([
                    'review_number',
                    'review_request_id',
                    'booking_stay_id',
                    'booking_check_out_id',
                    'author_user_id',
                    'author_type',
                    'target_user_id',
                    'target_type',
                    'review_subject_type',
                    'title',
                    'public_comment',
                    'private_comment',
                    'what_liked',
                    'what_disliked',
                    'advice_to_future_guests',
                    'is_public',
                    'is_anonymous_future',
                    'is_double_blind',
                    'is_published_after_window',
                    'submitted_at',
                    'published_at',
                    'hidden_at',
                    'expired_at',
                    'edited_at',
                    'edit_deadline_at',
                    'edit_count',
                    'language_locale',
                ] as $column) {
                    if (Schema::hasColumn('reviews', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function createReviewPolicies(): void
    {
        if (Schema::hasTable('review_policies')) {
            return;
        }

        Schema::create('review_policies', function (Blueprint $table): void {
            $table->id();
            $table->string('scope_type')->default('global');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->unsignedInteger('review_window_days')->default(14);
            $table->unsignedInteger('edit_window_hours')->default(24);
            $table->boolean('double_blind_enabled')->default(true);
            $table->boolean('publish_after_both_submitted')->default(true);
            $table->boolean('publish_after_window_expired')->default(true);
            $table->boolean('allow_review_photos')->default(true);
            $table->boolean('allow_host_response')->default(true);
            $table->boolean('allow_guest_response_future')->default(false);
            $table->unsignedInteger('minimum_stay_nights_for_review')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('scope_type', 'review_policies_scope_type_idx');
            $table->index('scope_id', 'review_policies_scope_id_idx');
            $table->index('active', 'review_policies_active_idx');
        });
    }

    private function createReviewRequests(): void
    {
        if (Schema::hasTable('review_requests')) {
            return;
        }

        Schema::create('review_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('review_request_number')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->constrained('booking_check_outs')->nullOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('request_type');
            $table->string('status')->default('created');
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reviewer_type');
            $table->string('review_subject_type');
            $table->foreignId('review_subject_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index('booking_id', 'review_requests_booking_idx');
            $table->index('booking_stay_id', 'review_requests_stay_idx');
            $table->index('booking_check_out_id', 'review_requests_checkout_idx');
            $table->index('guest_user_id', 'review_requests_guest_idx');
            $table->index('host_user_id', 'review_requests_host_idx');
            $table->index(['reviewer_user_id', 'status'], 'review_requests_reviewer_status_idx');
            $table->index('reviewer_type', 'review_requests_reviewer_type_idx');
            $table->index('review_subject_type', 'review_requests_subject_type_idx');
            $table->index('status', 'review_requests_status_idx');
            $table->index('due_at', 'review_requests_due_at_idx');
            $table->index('submitted_at', 'review_requests_submitted_at_idx');
        });
    }

    private function extendReviews(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table): void {
            if (! Schema::hasColumn('reviews', 'review_number')) {
                $table->string('review_number')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('reviews', 'review_request_id')) {
                $table->foreignId('review_request_id')->nullable()->after('review_number')->constrained('review_requests')->nullOnDelete();
            }

            if (! Schema::hasColumn('reviews', 'booking_stay_id')) {
                $table->foreignId('booking_stay_id')->nullable()->after('booking_id')->constrained('booking_stays')->nullOnDelete();
            }

            if (! Schema::hasColumn('reviews', 'booking_check_out_id')) {
                $table->foreignId('booking_check_out_id')->nullable()->after('booking_stay_id')->constrained('booking_check_outs')->nullOnDelete();
            }

            if (! Schema::hasColumn('reviews', 'author_user_id')) {
                $table->foreignId('author_user_id')->nullable()->after('reviewee_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('reviews', 'author_type')) {
                $table->string('author_type')->nullable()->after('author_user_id');
            }

            if (! Schema::hasColumn('reviews', 'target_user_id')) {
                $table->foreignId('target_user_id')->nullable()->after('author_type')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('reviews', 'target_type')) {
                $table->string('target_type')->nullable()->after('target_user_id');
            }

            if (! Schema::hasColumn('reviews', 'review_subject_type')) {
                $table->string('review_subject_type')->nullable()->after('target_type');
            }

            if (! Schema::hasColumn('reviews', 'title')) {
                $table->string('title')->nullable()->after('overall_rating');
            }

            if (! Schema::hasColumn('reviews', 'public_comment')) {
                $table->text('public_comment')->nullable()->after('title');
            }

            if (! Schema::hasColumn('reviews', 'private_comment')) {
                $table->text('private_comment')->nullable()->after('public_comment');
            }

            if (! Schema::hasColumn('reviews', 'what_liked')) {
                $table->text('what_liked')->nullable()->after('private_comment');
            }

            if (! Schema::hasColumn('reviews', 'what_disliked')) {
                $table->text('what_disliked')->nullable()->after('what_liked');
            }

            if (! Schema::hasColumn('reviews', 'advice_to_future_guests')) {
                $table->text('advice_to_future_guests')->nullable()->after('what_disliked');
            }

            if (! Schema::hasColumn('reviews', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('status');
            }

            if (! Schema::hasColumn('reviews', 'is_anonymous_future')) {
                $table->boolean('is_anonymous_future')->default(false)->after('is_public');
            }

            if (! Schema::hasColumn('reviews', 'is_double_blind')) {
                $table->boolean('is_double_blind')->default(true)->after('is_anonymous_future');
            }

            if (! Schema::hasColumn('reviews', 'is_published_after_window')) {
                $table->boolean('is_published_after_window')->default(false)->after('is_double_blind');
            }

            foreach ([
                'submitted_at',
                'published_at',
                'hidden_at',
                'expired_at',
                'edited_at',
                'edit_deadline_at',
            ] as $column) {
                if (! Schema::hasColumn('reviews', $column)) {
                    $table->timestamp($column)->nullable();
                }
            }

            if (! Schema::hasColumn('reviews', 'edit_count')) {
                $table->unsignedInteger('edit_count')->default(0);
            }

            if (! Schema::hasColumn('reviews', 'language_locale')) {
                $table->string('language_locale')->nullable();
            }
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->index('review_request_id', 'reviews_review_request_idx');
            $table->index('booking_stay_id', 'reviews_stay_idx');
            $table->index(['author_user_id', 'status'], 'reviews_author_status_idx');
            $table->index('target_user_id', 'reviews_target_user_idx');
            $table->index('target_type', 'reviews_target_type_idx');
            $table->index('review_subject_type', 'reviews_subject_type_idx');
            $table->index('overall_rating', 'reviews_overall_rating_idx');
            $table->index('is_public', 'reviews_is_public_idx');
            $table->index('published_at', 'reviews_published_at_idx');
        });
    }

    private function createReviewDetails(): void
    {
        if (! Schema::hasTable('review_scores')) {
            Schema::create('review_scores', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('review_id')->constrained()->cascadeOnDelete();
                $table->string('score_key');
                $table->decimal('score_value', 4, 2);
                $table->decimal('max_score', 4, 2)->default(5);
                $table->decimal('weight', 8, 2)->default(1);
                $table->boolean('is_public')->default(true);
                $table->timestamps();

                $table->index('review_id', 'review_scores_review_idx');
                $table->index('score_key', 'review_scores_score_key_idx');
                $table->index('is_public', 'review_scores_public_idx');
            });
        }

        if (! Schema::hasTable('review_media')) {
            Schema::create('review_media', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('review_id')->constrained()->cascadeOnDelete();
                $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('media_type')->default('photo');
                $table->string('media_role')->default('other');
                $table->string('path');
                $table->string('thumbnail_path')->nullable();
                $table->text('caption')->nullable();
                $table->string('visibility')->default('public');
                $table->boolean('approved_for_public_display')->default(false);
                $table->timestamp('public_display_at')->nullable();
                $table->timestamps();

                $table->index('review_id', 'review_media_review_idx');
                $table->index('uploaded_by_user_id', 'review_media_uploader_idx');
                $table->index('media_role', 'review_media_role_idx');
                $table->index('visibility', 'review_media_visibility_idx');
                $table->index('approved_for_public_display', 'review_media_public_idx');
            });
        }

        if (! Schema::hasTable('review_responses')) {
            Schema::create('review_responses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('review_id')->constrained()->cascadeOnDelete();
                $table->foreignId('responder_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('responder_type');
                $table->string('status')->default('submitted');
                $table->text('response_text');
                $table->boolean('is_public')->default(true);
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamp('hidden_at')->nullable();
                $table->timestamps();

                $table->index('review_id', 'review_responses_review_idx');
                $table->index('responder_user_id', 'review_responses_responder_idx');
                $table->index('status', 'review_responses_status_idx');
                $table->index('is_public', 'review_responses_public_idx');
                $table->index('published_at', 'review_responses_published_idx');
            });
        }

        if (! Schema::hasTable('roommate_experience_reviews')) {
            Schema::create('roommate_experience_reviews', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('review_id')->constrained()->cascadeOnDelete();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
                $table->boolean('quiet_roommates')->nullable();
                $table->boolean('clean_roommates')->nullable();
                $table->boolean('friendly_roommates')->nullable();
                $table->boolean('roommates_disturbed_sleep')->nullable();
                $table->boolean('roommates_broke_rules')->nullable();
                $table->boolean('conflict_happened')->nullable();
                $table->decimal('roommate_experience_rating', 4, 2)->nullable();
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->index('review_id', 'roommate_reviews_review_idx');
                $table->index('booking_id', 'roommate_reviews_booking_idx');
                $table->index('room_id', 'roommate_reviews_room_idx');
                $table->index('property_id', 'roommate_reviews_property_idx');
                $table->index('sleeping_place_id', 'roommate_reviews_place_idx');
                $table->index('roommate_experience_rating', 'roommate_reviews_rating_idx');
            });
        }
    }

    private function createRatingTables(): void
    {
        if (! Schema::hasTable('rating_events')) {
            Schema::create('rating_events', function (Blueprint $table): void {
                $table->id();
                $table->string('rating_event_number')->unique();
                $table->string('source_type');
                $table->unsignedBigInteger('source_id');
                $table->string('event_key');
                $table->string('event_type')->default('system');
                $table->string('target_type');
                $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('booking_stay_id')->nullable()->constrained('booking_stays')->nullOnDelete();
                $table->string('metric_key');
                $table->string('impact_direction')->default('neutral');
                $table->decimal('impact_value', 8, 2)->default(0);
                $table->decimal('weight', 8, 2)->default(1);
                $table->boolean('confirmed')->default(false);
                $table->boolean('frozen')->default(false);
                $table->boolean('ignored')->default(false);
                $table->string('reason_key')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['source_type', 'source_id'], 'rating_events_source_idx');
                $table->index('event_key', 'rating_events_event_key_idx');
                $table->index('target_type', 'rating_events_target_type_idx');
                $table->index('target_user_id', 'rating_events_target_user_idx');
                $table->index('property_id', 'rating_events_property_idx');
                $table->index('room_id', 'rating_events_room_idx');
                $table->index('sleeping_place_id', 'rating_events_place_idx');
                $table->index('booking_id', 'rating_events_booking_idx');
                $table->index('metric_key', 'rating_events_metric_idx');
                $table->index('confirmed', 'rating_events_confirmed_idx');
                $table->index('frozen', 'rating_events_frozen_idx');
                $table->index('ignored', 'rating_events_ignored_idx');
                $table->index('created_at', 'rating_events_created_idx');
            });
        }

        if (! Schema::hasTable('rating_aggregates')) {
            Schema::create('rating_aggregates', function (Blueprint $table): void {
                $table->id();
                $table->string('target_type');
                $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('sleeping_place_id')->nullable()->constrained()->nullOnDelete();
                $table->string('metric_key');
                $table->decimal('rating_average', 8, 2)->default(0);
                $table->decimal('rating_weighted_average', 8, 2)->nullable();
                $table->unsignedInteger('rating_count')->default(0);
                $table->decimal('rating_sum', 10, 2)->default(0);
                $table->decimal('rating_weight_sum', 10, 2)->nullable();
                $table->foreignId('last_review_id')->nullable()->constrained('reviews')->nullOnDelete();
                $table->foreignId('last_rating_event_id')->nullable()->constrained('rating_events')->nullOnDelete();
                $table->timestamp('last_recalculated_at')->nullable();
                $table->timestamps();

                $table->index('target_type', 'rating_aggregates_target_type_idx');
                $table->index('target_user_id', 'rating_aggregates_target_user_idx');
                $table->index('property_id', 'rating_aggregates_property_idx');
                $table->index('room_id', 'rating_aggregates_room_idx');
                $table->index('sleeping_place_id', 'rating_aggregates_place_idx');
                $table->index('metric_key', 'rating_aggregates_metric_idx');
                $table->index('rating_count', 'rating_aggregates_count_idx');
                $table->index('last_recalculated_at', 'rating_aggregates_recalc_idx');
            });
        }

        $this->createReputationSnapshots();
        $this->createPlaceSnapshots();
    }

    private function createReputationSnapshots(): void
    {
        if (! Schema::hasTable('host_reputation_snapshots')) {
            Schema::create('host_reputation_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('host_user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->decimal('overall_rating', 8, 2)->default(0);
                $table->decimal('response_speed_rating', 8, 2)->default(0);
                $table->decimal('description_accuracy_rating', 8, 2)->default(0);
                $table->decimal('cleanliness_rating', 8, 2)->default(0);
                $table->decimal('problem_resolution_rating', 8, 2)->default(0);
                $table->decimal('honesty_rating', 8, 2)->default(0);
                $table->decimal('hospitality_rating', 8, 2)->default(0);
                $table->decimal('check_in_quality_rating', 8, 2)->default(0);
                $table->decimal('checkout_quality_rating', 8, 2)->default(0);
                $table->unsignedInteger('reviews_count')->default(0);
                $table->unsignedInteger('completed_stays_count')->default(0);
                $table->unsignedInteger('successful_check_ins_count')->default(0);
                $table->unsignedInteger('host_cancellations_count')->default(0);
                $table->unsignedInteger('confirmed_host_unresponsive_count')->default(0);
                $table->unsignedInteger('confirmed_complaints_count')->default(0);
                $table->unsignedInteger('resolved_complaints_count')->default(0);
                $table->unsignedInteger('average_response_minutes')->nullable();
                $table->boolean('verified_host')->default(false);
                $table->boolean('trusted_host_future')->default(false);
                $table->timestamp('last_recalculated_at')->nullable();
                $table->timestamps();

                $table->index('overall_rating', 'host_reputation_overall_idx');
                $table->index('reviews_count', 'host_reputation_reviews_idx');
                $table->index('verified_host', 'host_reputation_verified_idx');
                $table->index('last_recalculated_at', 'host_reputation_recalc_idx');
            });
        }

        if (! Schema::hasTable('guest_reputation_snapshots')) {
            Schema::create('guest_reputation_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('guest_user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->decimal('overall_rating', 8, 2)->default(0);
                $table->decimal('rules_respect_rating', 8, 2)->default(0);
                $table->decimal('cleanliness_rating', 8, 2)->default(0);
                $table->decimal('communication_rating', 8, 2)->default(0);
                $table->decimal('punctuality_rating', 8, 2)->default(0);
                $table->decimal('respect_for_roommates_rating', 8, 2)->default(0);
                $table->decimal('care_for_property_rating', 8, 2)->default(0);
                $table->decimal('payment_reliability_rating', 8, 2)->default(0);
                $table->unsignedInteger('reviews_count')->default(0);
                $table->unsignedInteger('completed_stays_count')->default(0);
                $table->unsignedInteger('confirmed_no_show_count')->default(0);
                $table->unsignedInteger('guest_cancellations_count')->default(0);
                $table->unsignedInteger('confirmed_deposit_deductions_count')->default(0);
                $table->unsignedInteger('confirmed_complaints_count')->default(0);
                $table->unsignedInteger('resolved_complaints_count')->default(0);
                $table->unsignedInteger('recommended_by_hosts_count')->default(0);
                $table->unsignedInteger('not_recommended_by_hosts_count')->default(0);
                $table->timestamp('last_recalculated_at')->nullable();
                $table->timestamps();

                $table->index('overall_rating', 'guest_reputation_overall_idx');
                $table->index('reviews_count', 'guest_reputation_reviews_idx');
                $table->index('completed_stays_count', 'guest_reputation_stays_idx');
                $table->index('last_recalculated_at', 'guest_reputation_recalc_idx');
            });
        }
    }

    private function createPlaceSnapshots(): void
    {
        if (! Schema::hasTable('sleeping_place_rating_snapshots')) {
            Schema::create('sleeping_place_rating_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('sleeping_place_id')->unique()->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('overall_rating', 8, 2)->default(0);
                $table->decimal('cleanliness_rating', 8, 2)->default(0);
                $table->decimal('safety_rating', 8, 2)->default(0);
                $table->decimal('location_rating', 8, 2)->default(0);
                $table->decimal('description_accuracy_rating', 8, 2)->default(0);
                $table->decimal('sleeping_place_quality_rating', 8, 2)->default(0);
                $table->decimal('mattress_quality_rating', 8, 2)->default(0);
                $table->decimal('noise_level_rating', 8, 2)->default(0);
                $table->decimal('amenities_rating', 8, 2)->default(0);
                $table->decimal('internet_rating', 8, 2)->default(0);
                $table->decimal('value_for_money_rating', 8, 2)->default(0);
                $table->decimal('problem_resolution_rating', 8, 2)->default(0);
                $table->unsignedInteger('reviews_count')->default(0);
                $table->unsignedInteger('published_reviews_count')->default(0);
                $table->unsignedInteger('photo_reviews_count')->default(0);
                $table->unsignedInteger('completed_stays_count')->default(0);
                $table->unsignedInteger('confirmed_mismatch_count')->default(0);
                $table->unsignedInteger('confirmed_maintenance_issues_count')->default(0);
                $table->unsignedInteger('confirmed_cleanliness_complaints_count')->default(0);
                $table->timestamp('last_review_at')->nullable();
                $table->timestamp('last_recalculated_at')->nullable();
                $table->timestamps();

                $table->index('room_id', 'place_rating_room_idx');
                $table->index('property_id', 'place_rating_property_idx');
                $table->index('host_user_id', 'place_rating_host_idx');
                $table->index('overall_rating', 'place_rating_overall_idx');
                $table->index('reviews_count', 'place_rating_reviews_idx');
                $table->index('last_review_at', 'place_rating_last_review_idx');
                $table->index('last_recalculated_at', 'place_rating_recalc_idx');
            });
        }

        if (! Schema::hasTable('room_rating_snapshots')) {
            Schema::create('room_rating_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('room_id')->unique()->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('overall_rating', 8, 2)->default(0);
                $table->decimal('cleanliness_rating', 8, 2)->default(0);
                $table->decimal('safety_rating', 8, 2)->default(0);
                $table->decimal('noise_level_rating', 8, 2)->default(0);
                $table->decimal('roommate_experience_rating', 8, 2)->default(0);
                $table->decimal('roommate_cleanliness_rating', 8, 2)->default(0);
                $table->decimal('roommate_friendliness_rating', 8, 2)->default(0);
                $table->decimal('roommate_quietness_rating', 8, 2)->default(0);
                $table->unsignedInteger('reviews_count')->default(0);
                $table->unsignedInteger('completed_stays_count')->default(0);
                $table->unsignedInteger('confirmed_roommate_complaints_count')->default(0);
                $table->unsignedInteger('confirmed_noise_complaints_count')->default(0);
                $table->timestamp('last_recalculated_at')->nullable();
                $table->timestamps();

                $table->index('property_id', 'room_rating_property_idx');
                $table->index('host_user_id', 'room_rating_host_idx');
                $table->index('overall_rating', 'room_rating_overall_idx');
                $table->index('reviews_count', 'room_rating_reviews_idx');
                $table->index('last_recalculated_at', 'room_rating_recalc_idx');
            });
        }

        if (! Schema::hasTable('property_rating_snapshots')) {
            Schema::create('property_rating_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('property_id')->unique()->constrained()->cascadeOnDelete();
                $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('overall_rating', 8, 2)->default(0);
                $table->decimal('cleanliness_rating', 8, 2)->default(0);
                $table->decimal('safety_rating', 8, 2)->default(0);
                $table->decimal('location_rating', 8, 2)->default(0);
                $table->decimal('kitchen_rating', 8, 2)->default(0);
                $table->decimal('bathroom_rating', 8, 2)->default(0);
                $table->decimal('internet_rating', 8, 2)->default(0);
                $table->decimal('amenities_rating', 8, 2)->default(0);
                $table->decimal('description_accuracy_rating', 8, 2)->default(0);
                $table->decimal('problem_resolution_rating', 8, 2)->default(0);
                $table->unsignedInteger('reviews_count')->default(0);
                $table->unsignedInteger('completed_stays_count')->default(0);
                $table->unsignedInteger('confirmed_property_complaints_count')->default(0);
                $table->timestamp('last_recalculated_at')->nullable();
                $table->timestamps();

                $table->index('host_user_id', 'property_rating_host_idx');
                $table->index('overall_rating', 'property_rating_overall_idx');
                $table->index('reviews_count', 'property_rating_reviews_idx');
                $table->index('last_recalculated_at', 'property_rating_recalc_idx');
            });
        }
    }

    private function createReviewAuditTables(): void
    {
        if (! Schema::hasTable('review_status_logs')) {
            Schema::create('review_status_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('review_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('old_status')->nullable();
                $table->string('new_status');
                $table->string('reason_key')->nullable();
                $table->text('note')->nullable();
                $table->text('context_json')->nullable();
                $table->timestamps();

                $table->index('review_id', 'review_status_logs_review_idx');
                $table->index('user_id', 'review_status_logs_user_idx');
                $table->index('new_status', 'review_status_logs_new_status_idx');
                $table->index('created_at', 'review_status_logs_created_idx');
            });
        }

        if (! Schema::hasTable('review_events')) {
            Schema::create('review_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('review_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('review_request_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('event_key');
                $table->string('event_type')->default('system');
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamp('occurred_at');
                $table->text('context_json')->nullable();
                $table->timestamps();

                $table->index('review_id', 'review_events_review_idx');
                $table->index('review_request_id', 'review_events_request_idx');
                $table->index('booking_id', 'review_events_booking_idx');
                $table->index('event_key', 'review_events_event_key_idx');
                $table->index('event_type', 'review_events_event_type_idx');
                $table->index(['source_type', 'source_id'], 'review_events_source_idx');
                $table->index('occurred_at', 'review_events_occurred_idx');
            });
        }
    }
};
