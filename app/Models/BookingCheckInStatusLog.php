<?php

namespace App\Models;

use Database\Factories\BookingCheckInStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInStatusLog extends Model
{
    /** @use HasFactory<BookingCheckInStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'booking_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines casts for structured check-in status context.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log to its check-in process.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this status log to its booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this status log to the user who caused the transition.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
