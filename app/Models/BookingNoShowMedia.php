<?php

namespace App\Models;

use Database\Factories\BookingNoShowMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNoShowMedia extends Model
{
    /** @use HasFactory<BookingNoShowMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_no_show_id',
        'booking_id',
        'uploaded_by_user_id',
        'media_type',
        'media_role',
        'path',
        'thumbnail_path',
        'caption',
        'visibility',
    ];

    protected $attributes = [
        'visibility' => 'guest_and_host',
    ];

    /**
     * Links this evidence media to its no-show case.
     */
    public function noShow(): BelongsTo
    {
        return $this->belongsTo(BookingNoShow::class, 'booking_no_show_id');
    }

    /**
     * Links this evidence media to the booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this evidence media to the user who uploaded it.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
