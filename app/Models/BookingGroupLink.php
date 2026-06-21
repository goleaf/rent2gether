<?php

namespace App\Models;

use Database\Factories\BookingGroupLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingGroupLink extends Model
{
    /** @use HasFactory<BookingGroupLinkFactory> */
    use HasFactory;

    protected $fillable = [
        'group_booking_number',
        'main_booking_id',
        'booking_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'status',
    ];

    /**
     * Links this group row to the individual sleeping-place Booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this group row to the primary Booking in the group.
     */
    public function mainBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'main_booking_id');
    }

    /**
     * Links this group row to the guest who owns the grouped booking.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this group row to the host responsible for this sleeping place.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this group row to the property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this group row to the room context when it is available.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
