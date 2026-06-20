<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'reviewer_id',
        'reviewee_id',
        'guest_user_id',
        'host_user_id',
        'type',
        'bed_id',
        'sleeping_place_id',
        'room_id',
        'property_id',
        'overall_rating',
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
        'visible_at',
        'flagged_words_json',
    ];

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
            'visible_at' => 'datetime',
            'flagged_words_json' => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::Published->value);
    }

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

    public function scopeGuestToPlace(Builder $query): Builder
    {
        return $query->where('type', ReviewType::GuestToPlace->value);
    }

    public function scopeHostToGuest(Builder $query): Builder
    {
        return $query->where('type', ReviewType::HostToGuest->value);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }
}
