<?php

namespace App\Models;

use Database\Factories\RoomRatingSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRatingSnapshot extends Model
{
    /** @use HasFactory<RoomRatingSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'property_id',
        'host_user_id',
        'overall_rating',
        'cleanliness_rating',
        'safety_rating',
        'noise_level_rating',
        'roommate_experience_rating',
        'roommate_cleanliness_rating',
        'roommate_friendliness_rating',
        'roommate_quietness_rating',
        'reviews_count',
        'completed_stays_count',
        'confirmed_roommate_complaints_count',
        'confirmed_noise_complaints_count',
        'last_recalculated_at',
    ];

    /**
     * Defines how Laravel converts stored Room Rating Snapshot attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'overall_rating' => 'decimal:2',
            'cleanliness_rating' => 'decimal:2',
            'safety_rating' => 'decimal:2',
            'noise_level_rating' => 'decimal:2',
            'roommate_experience_rating' => 'decimal:2',
            'roommate_cleanliness_rating' => 'decimal:2',
            'roommate_friendliness_rating' => 'decimal:2',
            'roommate_quietness_rating' => 'decimal:2',
            'last_recalculated_at' => 'datetime',
        ];
    }

    /**
     * Links this snapshot to the room it summarizes.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
