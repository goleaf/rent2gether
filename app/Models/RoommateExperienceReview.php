<?php

namespace App\Models;

use Database\Factories\RoommateExperienceReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoommateExperienceReview extends Model
{
    /** @use HasFactory<RoommateExperienceReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'review_id',
        'booking_id',
        'room_id',
        'property_id',
        'sleeping_place_id',
        'quiet_roommates',
        'clean_roommates',
        'friendly_roommates',
        'roommates_disturbed_sleep',
        'roommates_broke_rules',
        'conflict_happened',
        'roommate_experience_rating',
        'comment',
    ];

    /**
     * Defines how Laravel converts stored Roommate Experience Review attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'quiet_roommates' => 'boolean',
            'clean_roommates' => 'boolean',
            'friendly_roommates' => 'boolean',
            'roommates_disturbed_sleep' => 'boolean',
            'roommates_broke_rules' => 'boolean',
            'conflict_happened' => 'boolean',
            'roommate_experience_rating' => 'decimal:2',
        ];
    }

    /**
     * Links this Roommate Experience Review to its parent review.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Links this Roommate Experience Review to its booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Roommate Experience Review to its room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
