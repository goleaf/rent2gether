<?php

namespace App\Models;

use Database\Factories\SleepingPlaceBookingDateLockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceBookingDateLock extends Model
{
    /** @use HasFactory<SleepingPlaceBookingDateLockFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'booking_id',
        'booking_quote_id',
        'booking_request_id',
        'booking_extension_id',
        'booking_relocation_id',
        'date',
        'lock_type',
        'status',
        'expires_at',
        'released_at',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Booking Date Lock attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'expires_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    /**
     * Links this date lock to the Sleeping Place it protects.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this date lock to its Booking when the lock has become a booking hold.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this date lock to the Booking Request that temporarily holds dates.
     */
    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }

    /**
     * Links this date lock to its Booking Extension when an extension is holding dates.
     */
    public function bookingExtension(): BelongsTo
    {
        return $this->belongsTo(BookingExtension::class);
    }

    /**
     * Links this date lock to its Booking Relocation when a relocation is holding dates.
     */
    public function bookingRelocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class);
    }
}
