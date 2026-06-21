<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingListingMismatchStatusLog extends Model
{
    /** @use HasFactory<BookingListingMismatchStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_listing_mismatch_report_id',
        'booking_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how Laravel converts stored mismatch status log attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log to the parent mismatch report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'booking_listing_mismatch_report_id');
    }

    /**
     * Links this status log to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this status log to the acting user when available.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
