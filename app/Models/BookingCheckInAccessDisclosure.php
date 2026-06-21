<?php

namespace App\Models;

use Database\Factories\BookingCheckInAccessDisclosureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInAccessDisclosure extends Model
{
    /** @use HasFactory<BookingCheckInAccessDisclosureFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'booking_id',
        'guest_user_id',
        'disclosure_type',
        'shown_at',
        'shown_by_user_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Defines timestamp casts for access disclosure audit rows.
     */
    protected function casts(): array
    {
        return [
            'shown_at' => 'datetime',
        ];
    }

    /**
     * Links this disclosure to its check-in process.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this disclosure to its booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this disclosure to the guest who was allowed to see access data.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this disclosure to the user who triggered the display.
     */
    public function shownBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shown_by_user_id');
    }
}
