<?php

namespace App\Models;

use Database\Factories\BookingStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusLog extends Model
{
    /** @use HasFactory<BookingStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how Laravel converts stored Booking Status Log attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log to the Booking lifecycle it documents.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this status log to the user who caused the transition, when available.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
