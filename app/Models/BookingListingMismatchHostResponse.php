<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchHostResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingListingMismatchHostResponse extends Model
{
    /** @use HasFactory<BookingListingMismatchHostResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_listing_mismatch_report_id',
        'host_user_id',
        'response_type',
        'message',
        'accepts_problem',
        'proposed_resolution_type',
        'offered_compensation_amount',
        'offered_refund_amount',
        'currency',
        'alternative_sleeping_place_id',
        'maintenance_request_id',
        'cleaning_task_id',
    ];

    /**
     * Defines how Laravel converts stored host response attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'accepts_problem' => 'boolean',
            'offered_compensation_amount' => 'decimal:2',
            'offered_refund_amount' => 'decimal:2',
        ];
    }

    /**
     * Links this response to the parent mismatch report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'booking_listing_mismatch_report_id');
    }

    /**
     * Links this response to the host who sent it.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this response to an alternative sleeping place when relocation is offered.
     */
    public function alternativeSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'alternative_sleeping_place_id');
    }
}
