<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchResolutionOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingListingMismatchResolutionOption extends Model
{
    /** @use HasFactory<BookingListingMismatchResolutionOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_listing_mismatch_report_id',
        'resolution_type',
        'status',
        'description',
        'amount',
        'currency',
        'sleeping_place_id',
        'booking_relocation_id',
        'booking_cancellation_id',
        'booking_refund_id',
        'cleaning_task_id',
        'maintenance_request_id',
        'offered_by_user_id',
        'accepted_by_user_id',
        'offered_at',
        'accepted_at',
        'rejected_at',
        'completed_at',
    ];

    protected $attributes = [
        'status' => 'offered',
    ];

    /**
     * Defines how Laravel converts stored resolution option attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'offered_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Links this option to the parent mismatch report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'booking_listing_mismatch_report_id');
    }

    /**
     * Links this option to the proposed sleeping place when relocation is offered.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this option to a relocation created from it.
     */
    public function bookingRelocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class);
    }

    /**
     * Links this option to a cancellation created from it.
     */
    public function bookingCancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class);
    }

    /**
     * Links this option to a refund created from it.
     */
    public function bookingRefund(): BelongsTo
    {
        return $this->belongsTo(BookingRefund::class);
    }

    /**
     * Links this option to the user who offered it.
     */
    public function offeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'offered_by_user_id');
    }

    /**
     * Links this option to the user who accepted it.
     */
    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }
}
