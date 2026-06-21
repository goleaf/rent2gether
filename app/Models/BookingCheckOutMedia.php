<?php

namespace App\Models;

use Database\Factories\BookingCheckOutMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckOutMedia extends Model
{
    /** @use HasFactory<BookingCheckOutMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'booking_id',
        'uploaded_by_user_id',
        'media_type',
        'media_role',
        'path',
        'thumbnail_path',
        'caption',
        'visibility',
    ];

    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
