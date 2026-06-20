<?php

namespace App\Models;

use Database\Factories\BookingCheckInChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInChecklistItem extends Model
{
    /** @use HasFactory<BookingCheckInChecklistItemFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_in_id',
        'item_key',
        'label_key',
        'status',
        'required',
        'completed_by_user_id',
        'completed_at',
        'note',
    ];

    /**
     * Defines how Laravel converts stored Booking Check In Checklist Item attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Links this Booking Check In Checklist Item to the Booking Check In record used by its check in relation.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this Booking Check In Checklist Item to the User record used by its booking check in relation.
     */
    public function bookingCheckIn(): BelongsTo
    {
        return $this->checkIn();
    }

    /**
     * Links this Booking Check In Checklist Item to the User record used by its completed by relation.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
