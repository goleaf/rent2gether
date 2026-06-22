<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'review_number',
        'review_request_id',
        'booking_stay_id',
        'booking_check_out_id',
        'reviewer_id',
        'reviewee_id',
        'author_user_id',
        'author_type',
        'target_user_id',
        'target_type',
        'guest_user_id',
        'host_user_id',
        'type',
        'review_subject_type',
        'bed_id',
        'sleeping_place_id',
        'room_id',
        'property_id',
        'overall_rating',
        'title',
        'public_comment',
        'private_comment',
        'what_liked',
        'what_disliked',
        'advice_to_future_guests',
        'cleanliness_rating',
        'safety_rating',
        'location_rating',
        'accuracy_rating',
        'bed_comfort_rating',
        'sleeping_place_comfort_rating',
        'amenities_rating',
        'communication_rating',
        'host_communication_rating',
        'neighbors_rating',
        'value_rating',
        'rule_compliance_rating',
        'rule_following_rating',
        'tidiness_rating',
        'punctuality_rating',
        'respect_rating',
        'positive_comment',
        'negative_comment',
        'advice',
        'would_recommend',
        'would_return',
        'liked_text',
        'improvement_text',
        'advice_text',
        'comment',
        'photos_json',
        'recommend',
        'recommend_guest',
        'status',
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
        'visible_at',
        'flagged_words_json',
    ];

    /**
     * Defines how Laravel converts stored Review attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'type' => ReviewType::class,
            'status' => ReviewStatus::class,
            'would_recommend' => 'boolean',
            'would_return' => 'boolean',
            'recommend' => 'boolean',
            'recommend_guest' => 'boolean',
            'photos_json' => 'array',
            'is_public' => 'boolean',
            'is_anonymous_future' => 'boolean',
            'is_double_blind' => 'boolean',
            'is_published_after_window' => 'boolean',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
            'hidden_at' => 'datetime',
            'expired_at' => 'datetime',
            'edited_at' => 'datetime',
            'edit_deadline_at' => 'datetime',
            'edit_count' => 'integer',
            'visible_at' => 'datetime',
            'flagged_words_json' => 'array',
        ];
    }

    /**
     * Adds the published query filter for reusable Review lookups.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::Published->value);
    }

    /**
     * Adds the visible query filter for reusable Review lookups.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $visible): void {
            $visible
                ->where('status', ReviewStatus::Published->value)
                ->orWhere(function (Builder $expired): void {
                    $expired
                        ->where('status', ReviewStatus::Pending->value)
                        ->whereHas('booking', fn (Builder $booking) => $booking
                            ->whereNotNull('review_deadline_at')
                            ->where('review_deadline_at', '<=', now()));
                });
        });
    }

    /**
     * Adds the guest to place query filter for reusable Review lookups.
     */
    public function scopeGuestToPlace(Builder $query): Builder
    {
        return $query->where('type', ReviewType::GuestToPlace->value);
    }

    /**
     * Adds the host to guest query filter for reusable Review lookups.
     */
    public function scopeHostToGuest(Builder $query): Builder
    {
        return $query->where('type', ReviewType::HostToGuest->value);
    }

    /**
     * Links this Review to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Review to the Point 26 review request that asked for it.
     */
    public function reviewRequest(): BelongsTo
    {
        return $this->belongsTo(ReviewRequest::class);
    }

    /**
     * Links this Review to the completed stay context when available.
     */
    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    /**
     * Links this Review to the checkout that opened the review window.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this Review to the User record used by its reviewer relation.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Links this Review to the User record used by its reviewee relation.
     */
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    /**
     * Links this Review to the Point 26 author user.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /**
     * Links this Review to the Point 26 target user when the review targets a person.
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Links this Review to the Bed record used by its bed relation.
     */
    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    /**
     * Links this Review to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this Review to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Review to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Lists related Media Item records attached to this Review through a polymorphic relation.
     */
    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }

    /**
     * Lists detailed metric scores attached to this Review.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(ReviewScore::class);
    }

    /**
     * Lists uploaded review photos and future media records.
     */
    public function reviewMedia(): HasMany
    {
        return $this->hasMany(ReviewMedia::class);
    }

    /**
     * Lists public host or future guest responses to this Review.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ReviewResponse::class);
    }

    /**
     * Fetches the privacy-safe roommate experience layer for this Review.
     */
    public function roommateExperience(): HasOne
    {
        return $this->hasOne(RoommateExperienceReview::class);
    }

    /**
     * Lists status history entries for this Review.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ReviewStatusLog::class);
    }

    /**
     * Lists review timeline events for this Review.
     */
    public function events(): HasMany
    {
        return $this->hasMany(ReviewEvent::class);
    }
}
