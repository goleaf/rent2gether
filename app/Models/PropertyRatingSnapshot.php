<?php

namespace App\Models;

use Database\Factories\PropertyRatingSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyRatingSnapshot extends Model
{
    /** @use HasFactory<PropertyRatingSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'host_user_id',
        'overall_rating',
        'cleanliness_rating',
        'safety_rating',
        'location_rating',
        'kitchen_rating',
        'bathroom_rating',
        'internet_rating',
        'amenities_rating',
        'description_accuracy_rating',
        'problem_resolution_rating',
        'reviews_count',
        'completed_stays_count',
        'confirmed_property_complaints_count',
        'last_recalculated_at',
    ];

    /**
     * Defines how Laravel converts stored Property Rating Snapshot attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'overall_rating' => 'decimal:2',
            'cleanliness_rating' => 'decimal:2',
            'safety_rating' => 'decimal:2',
            'location_rating' => 'decimal:2',
            'kitchen_rating' => 'decimal:2',
            'bathroom_rating' => 'decimal:2',
            'internet_rating' => 'decimal:2',
            'amenities_rating' => 'decimal:2',
            'description_accuracy_rating' => 'decimal:2',
            'problem_resolution_rating' => 'decimal:2',
            'last_recalculated_at' => 'datetime',
        ];
    }

    /**
     * Links this snapshot to the property it summarizes.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
