<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id', 'reviewer_id', 'reviewee_id', 'type', 'bed_id', 'room_id', 'property_id',
    'overall_rating', 'cleanliness_rating', 'safety_rating', 'location_rating', 'accuracy_rating',
    'bed_comfort_rating', 'amenities_rating', 'communication_rating', 'neighbors_rating', 'value_rating',
    'rule_compliance_rating', 'tidiness_rating', 'punctuality_rating',
    'positive_comment', 'negative_comment', 'advice', 'would_recommend', 'would_return', 'status',
])]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $casts = [
        'type' => ReviewType::class,
        'status' => ReviewStatus::class,
        'would_recommend' => 'boolean',
        'would_return' => 'boolean',
    ];

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

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', ReviewStatus::Published);
    }

    public function scopeForBed(Builder $query, int $bedId): void
    {
        $query->where('bed_id', $bedId)->where('type', ReviewType::GuestToPlace);
    }
}
