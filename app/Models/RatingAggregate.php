<?php

namespace App\Models;

use Database\Factories\RatingAggregateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingAggregate extends Model
{
    /** @use HasFactory<RatingAggregateFactory> */
    use HasFactory;

    protected $fillable = [
        'target_type',
        'target_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'metric_key',
        'rating_average',
        'rating_weighted_average',
        'rating_count',
        'rating_sum',
        'rating_weight_sum',
        'last_review_id',
        'last_rating_event_id',
        'last_recalculated_at',
    ];

    /**
     * Defines how Laravel converts stored Rating Aggregate attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'rating_average' => 'decimal:2',
            'rating_weighted_average' => 'decimal:2',
            'rating_count' => 'integer',
            'rating_sum' => 'decimal:2',
            'rating_weight_sum' => 'decimal:2',
            'last_recalculated_at' => 'datetime',
        ];
    }

    /**
     * Links this Rating Aggregate to its latest source review.
     */
    public function lastReview(): BelongsTo
    {
        return $this->belongsTo(Review::class, 'last_review_id');
    }
}
