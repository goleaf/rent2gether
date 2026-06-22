<?php

namespace App\Models;

use Database\Factories\RatingEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingEvent extends Model
{
    /** @use HasFactory<RatingEventFactory> */
    use HasFactory;

    protected $fillable = [
        'rating_event_number',
        'source_type',
        'source_id',
        'event_key',
        'event_type',
        'target_type',
        'target_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_id',
        'booking_stay_id',
        'metric_key',
        'impact_direction',
        'impact_value',
        'weight',
        'confirmed',
        'frozen',
        'ignored',
        'reason_key',
        'note',
    ];

    /**
     * Defines how Laravel converts stored Rating Event attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'impact_value' => 'decimal:2',
            'weight' => 'decimal:2',
            'confirmed' => 'boolean',
            'frozen' => 'boolean',
            'ignored' => 'boolean',
        ];
    }

    /**
     * Links this Rating Event to the user target when applicable.
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Links this Rating Event to its sleeping place context.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
