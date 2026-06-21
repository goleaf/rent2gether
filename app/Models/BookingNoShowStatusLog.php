<?php

namespace App\Models;

use Database\Factories\BookingNoShowStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNoShowStatusLog extends Model
{
    /** @use HasFactory<BookingNoShowStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_no_show_id',
        'booking_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how stored no-show status log values are converted for PHP use.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log to its no-show case.
     */
    public function noShow(): BelongsTo
    {
        return $this->belongsTo(BookingNoShow::class, 'booking_no_show_id');
    }

    /**
     * Links this status log to the booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this status log to the actor when one exists.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
