<?php

namespace App\Models;

use Database\Factories\BookingExtensionStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtensionStatusLog extends Model
{
    /** @use HasFactory<BookingExtensionStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_extension_id',
        'booking_id',
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
     * Links this status log to the extension whose status changed.
     */
    public function bookingExtension(): BelongsTo
    {
        return $this->belongsTo(BookingExtension::class);
    }

    /**
     * Links this status log to the original booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this status log to the user who caused the change when known.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
