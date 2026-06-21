<?php

namespace App\Models;

use Database\Factories\BookingRelocationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRelocationEvent extends Model
{
    /** @use HasFactory<BookingRelocationEventFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_relocation_id',
        'original_booking_id',
        'new_booking_id',
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
     * Links this event to its relocation.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }

    /**
     * Links this event to the original booking.
     */
    public function originalBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'original_booking_id');
    }

    /**
     * Links this event to the new booking segment when it exists.
     */
    public function newBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'new_booking_id');
    }

    /**
     * Links this event to the user who caused it when applicable.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
