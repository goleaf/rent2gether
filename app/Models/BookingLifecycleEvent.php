<?php

namespace App\Models;

use Database\Factories\BookingLifecycleEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingLifecycleEvent extends Model
{
    /** @use HasFactory<BookingLifecycleEventFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'event_key',
        'event_type',
        'source_type',
        'source_id',
        'user_id',
        'occurred_at',
        'context_json',
    ];

    protected $attributes = [
        'event_type' => 'system',
    ];

    /**
     * Defines how Laravel converts stored Booking Lifecycle Event attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this event to the Booking timeline it belongs to.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this event to the user who caused it, when available.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
