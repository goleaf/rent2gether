<?php

namespace App\Models;

use Database\Factories\BookingStayNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStayNote extends Model
{
    /** @use HasFactory<BookingStayNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_stay_id',
        'booking_id',
        'user_id',
        'note_type',
        'visibility',
        'note',
    ];

    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    public function bookingStay(): BelongsTo
    {
        return $this->stay();
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
