<?php

namespace App\Models;

use Database\Factories\BookingNoShowContactAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNoShowContactAttempt extends Model
{
    /** @use HasFactory<BookingNoShowContactAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_no_show_id',
        'booking_id',
        'attempted_by_user_id',
        'contact_channel',
        'attempt_type',
        'status',
        'message_key',
        'message_text',
        'attempted_at',
        'response_received_at',
        'response_summary',
    ];

    protected $attributes = [
        'status' => 'created',
    ];

    /**
     * Defines how stored no-show contact attempt values are converted for PHP use.
     */
    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'response_received_at' => 'datetime',
        ];
    }

    /**
     * Links this contact attempt to its no-show case.
     */
    public function noShow(): BelongsTo
    {
        return $this->belongsTo(BookingNoShow::class, 'booking_no_show_id');
    }

    /**
     * Links this contact attempt to the booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this contact attempt to the user who sent or recorded it.
     */
    public function attemptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attempted_by_user_id');
    }
}
