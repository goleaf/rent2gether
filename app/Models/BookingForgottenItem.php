<?php

namespace App\Models;

use Database\Factories\BookingForgottenItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingForgottenItem extends Model
{
    /** @use HasFactory<BookingForgottenItemFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'item_name',
        'description',
        'photo_path',
        'photo_paths_json',
        'storage_location',
        'return_method',
        'status',
        'guest_notified_at',
        'returned_at',
        'picked_up_at',
        'disposed_at',
        'keep_until',
    ];

    /**
     * Defines how Laravel converts stored Booking Forgotten Item attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'photo_paths_json' => 'array',
            'guest_notified_at' => 'datetime',
            'returned_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'disposed_at' => 'datetime',
            'keep_until' => 'date:Y-m-d',
        ];
    }

    /**
     * Links this Booking Forgotten Item to the Booking Check Out record used by its check out relation.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this Booking Forgotten Item to the Booking record used by its booking check out relation.
     */
    public function bookingCheckOut(): BelongsTo
    {
        return $this->checkOut();
    }

    /**
     * Links this Booking Forgotten Item to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Forgotten Item to the User record used by its guest relation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this Booking Forgotten Item to the User record used by its host relation.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this Booking Forgotten Item to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Booking Forgotten Item to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Booking Forgotten Item to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
