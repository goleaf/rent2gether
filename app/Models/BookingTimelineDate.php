<?php

namespace App\Models;

use Database\Factories\BookingTimelineDateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTimelineDate extends Model
{
    /** @use HasFactory<BookingTimelineDateFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_quote_id',
        'booking_id',
        'event_key',
        'scheduled_at',
        'status',
    ];

    /**
     * Defines how Laravel converts stored Booking Timeline Date attributes.
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * Links this timeline date to the Quote that first calculated it.
     */
    public function bookingQuote(): BelongsTo
    {
        return $this->belongsTo(BookingQuote::class);
    }

    /**
     * Links this timeline date to the Booking after quote conversion.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
