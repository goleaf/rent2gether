<?php

namespace App\Models;

use Database\Factories\BookingGuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingGuest extends Model
{
    /** @use HasFactory<BookingGuestFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'full_name',
        'age',
        'document_type',
        'document_last_four',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
