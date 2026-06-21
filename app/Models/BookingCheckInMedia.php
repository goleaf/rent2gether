<?php

namespace App\Models;

use Database\Factories\BookingCheckInMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckInMedia extends Model
{
    /** @use HasFactory<BookingCheckInMediaFactory> */
    use HasFactory;

    protected $table = 'booking_check_in_media';

    protected $fillable = [
        'booking_check_in_id',
        'booking_id',
        'uploaded_by_user_id',
        'media_type',
        'media_role',
        'path',
        'thumbnail_path',
        'caption',
        'visibility',
    ];

    /**
     * Links this media row to its check-in process.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Allows factories to use the conventional BookingCheckIn relation name.
     */
    public function bookingCheckIn(): BelongsTo
    {
        return $this->checkIn();
    }

    /**
     * Links this media row to its booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this media row to the user who uploaded it.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
