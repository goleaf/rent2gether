<?php

namespace App\Models;

use Database\Factories\BookingStayEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStayEvent extends Model
{
    /** @use HasFactory<BookingStayEventFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_stay_id',
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

    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    public function bookingStay(): BelongsTo
    {
        return $this->stay();
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
