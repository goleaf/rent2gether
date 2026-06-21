<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingListingMismatchEvent extends Model
{
    /** @use HasFactory<BookingListingMismatchEventFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_listing_mismatch_report_id',
        'booking_id',
        'event_key',
        'event_type',
        'source_type',
        'source_id',
        'user_id',
        'occurred_at',
        'context_json',
    ];

    protected $attributes = [
        'event_type' => 'system',
    ];

    /**
     * Defines how Laravel converts stored mismatch event attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this event to the parent mismatch report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'booking_listing_mismatch_report_id');
    }

    /**
     * Links this event to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this event to the acting user when available.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
