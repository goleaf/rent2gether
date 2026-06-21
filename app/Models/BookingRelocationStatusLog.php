<?php

namespace App\Models;

use Database\Factories\BookingRelocationStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRelocationStatusLog extends Model
{
    /** @use HasFactory<BookingRelocationStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_relocation_id',
        'original_booking_id',
        'new_booking_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log to its relocation.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }

    /**
     * Links this status log to the original booking.
     */
    public function originalBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'original_booking_id');
    }

    /**
     * Links this status log to the new booking segment when one exists.
     */
    public function newBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'new_booking_id');
    }

    /**
     * Links this status log to the user who changed the relocation status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
