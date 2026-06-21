<?php

namespace App\Models;

use Database\Factories\BookingNoShowEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNoShowEvent extends Model
{
    /** @use HasFactory<BookingNoShowEventFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_no_show_id',
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
     * Defines how stored no-show event values are converted for PHP use.
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this event to its no-show case.
     */
    public function noShow(): BelongsTo
    {
        return $this->belongsTo(BookingNoShow::class, 'booking_no_show_id');
    }

    /**
     * Links this event to the booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this event to the actor when one exists.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
