<?php

namespace App\Models;

use Database\Factories\BookingRequestStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequestStatusLog extends Model
{
    /** @use HasFactory<BookingRequestStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_request_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how Laravel converts stored status log attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log row to the Booking Request.
     */
    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }

    /**
     * Links this status log row to the user who caused the transition.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
