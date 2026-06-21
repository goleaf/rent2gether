<?php

namespace App\Models;

use Database\Factories\BookingNoShowGuestResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNoShowGuestResponse extends Model
{
    /** @use HasFactory<BookingNoShowGuestResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_no_show_id',
        'booking_id',
        'guest_user_id',
        'response_type',
        'message',
        'new_arrival_time',
        'evidence_media_id',
    ];

    /**
     * Links this guest response to its no-show case.
     */
    public function noShow(): BelongsTo
    {
        return $this->belongsTo(BookingNoShow::class, 'booking_no_show_id');
    }

    /**
     * Links this guest response to the booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this response to the guest who sent it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }
}
