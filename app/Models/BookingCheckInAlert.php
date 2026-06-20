<?php

namespace App\Models;

use Database\Factories\BookingCheckInAlertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInAlert extends Model
{
    /** @use HasFactory<BookingCheckInAlertFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'alert_type',
        'severity',
        'status',
        'message_key',
        'message_params_json',
        'resolved_at',
    ];

    /**
     * Defines how Laravel converts stored Booking Check In Alert attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Links this Booking Check In Alert to the Booking Check In record used by its check in relation.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this Booking Check In Alert to the Booking record used by its booking check in relation.
     */
    public function bookingCheckIn(): BelongsTo
    {
        return $this->checkIn();
    }

    /**
     * Links this Booking Check In Alert to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Check In Alert to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Booking Check In Alert to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
