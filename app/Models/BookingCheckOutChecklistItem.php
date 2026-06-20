<?php

namespace App\Models;

use Database\Factories\BookingCheckOutChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckOutChecklistItem extends Model
{
    /** @use HasFactory<BookingCheckOutChecklistItemFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'item_key',
        'label_key',
        'status',
        'required',
        'completed_by_user_id',
        'completed_at',
        'note',
    ];

    /**
     * Defines how Laravel converts stored Booking Check Out Checklist Item attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Links this Booking Check Out Checklist Item to the Booking Check Out record used by its check out relation.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this Booking Check Out Checklist Item to the User record used by its booking check out relation.
     */
    public function bookingCheckOut(): BelongsTo
    {
        return $this->checkOut();
    }

    /**
     * Links this Booking Check Out Checklist Item to the User record used by its completed by relation.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
