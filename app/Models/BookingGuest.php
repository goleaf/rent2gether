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
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_type',
        'verification_status',
        'is_main_guest',
        'full_name',
        'age',
        'document_type',
        'document_last_four',
    ];

    /**
     * Defines how Laravel converts stored Booking Guest attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'is_main_guest' => 'boolean',
        ];
    }

    /**
     * Links this Booking Guest to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Booking Guest to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
