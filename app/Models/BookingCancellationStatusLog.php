<?php

namespace App\Models;

use Database\Factories\BookingCancellationStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCancellationStatusLog extends Model
{
    /** @use HasFactory<BookingCancellationStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_cancellation_id',
        'booking_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how Laravel converts stored status-log attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this log to the parent cancellation.
     */
    public function cancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class, 'booking_cancellation_id');
    }

    /**
     * Links this log to the affected Booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this log to the user who caused the transition when available.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
