<?php

namespace App\Models;

use Database\Factories\SleepingPlaceRatingSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceRatingSnapshot extends Model
{
    /** @use HasFactory<SleepingPlaceRatingSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'room_id',
        'property_id',
        'host_user_id',
        'overall_rating',
        'cleanliness_rating',
        'safety_rating',
        'location_rating',
        'description_accuracy_rating',
        'sleeping_place_quality_rating',
        'mattress_quality_rating',
        'noise_level_rating',
        'amenities_rating',
        'internet_rating',
        'value_for_money_rating',
        'problem_resolution_rating',
        'reviews_count',
        'published_reviews_count',
        'photo_reviews_count',
        'completed_stays_count',
        'confirmed_mismatch_count',
        'confirmed_maintenance_issues_count',
        'confirmed_cleanliness_complaints_count',
        'last_review_at',
        'last_recalculated_at',
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Rating Snapshot attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'overall_rating' => 'decimal:2',
            'cleanliness_rating' => 'decimal:2',
            'safety_rating' => 'decimal:2',
            'location_rating' => 'decimal:2',
            'description_accuracy_rating' => 'decimal:2',
            'sleeping_place_quality_rating' => 'decimal:2',
            'mattress_quality_rating' => 'decimal:2',
            'noise_level_rating' => 'decimal:2',
            'amenities_rating' => 'decimal:2',
            'internet_rating' => 'decimal:2',
            'value_for_money_rating' => 'decimal:2',
            'problem_resolution_rating' => 'decimal:2',
            'last_review_at' => 'datetime',
            'last_recalculated_at' => 'datetime',
        ];
    }

    /**
     * Links this snapshot to the sleeping place it summarizes.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
