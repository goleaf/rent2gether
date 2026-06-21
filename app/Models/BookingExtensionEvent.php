<?php

namespace App\Models;

use Database\Factories\BookingExtensionEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtensionEvent extends Model
{
    /** @use HasFactory<BookingExtensionEventFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_extension_id',
        'booking_id',
        'event_key',
        'event_type',
        'source_type',
        'source_id',
        'user_id',
        'occurred_at',
        'context_json',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this timeline event to the extension flow.
     */
    public function bookingExtension(): BelongsTo
    {
        return $this->belongsTo(BookingExtension::class);
    }

    /**
     * Links this timeline event to the original booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this event to the user who triggered it when known.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
