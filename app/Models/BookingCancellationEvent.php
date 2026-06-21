<?php

namespace App\Models;

use Database\Factories\BookingCancellationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCancellationEvent extends Model
{
    /** @use HasFactory<BookingCancellationEventFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_cancellation_id',
        'booking_id',
        'event_key',
        'event_type',
        'source_type',
        'source_id',
        'user_id',
        'occurred_at',
        'context_json',
    ];

    /**
     * Defines how Laravel converts stored cancellation-event attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this event to the parent cancellation.
     */
    public function cancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class, 'booking_cancellation_id');
    }

    /**
     * Links this event to the affected Booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this event to the user who caused it when available.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
