<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingListingMismatchReport extends Model
{
    /** @use HasFactory<BookingListingMismatchReportFactory> */
    use HasFactory;

    protected $fillable = [
        'mismatch_number',
        'booking_id',
        'booking_stay_id',
        'booking_check_in_id',
        'booking_check_out_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'source_type',
        'source_id',
        'mismatch_type',
        'severity',
        'status',
        'reported_at',
        'discovered_at',
        'guest_description',
        'host_response',
        'what_was_promised',
        'what_was_actual',
        'guest_wants_to_stay',
        'guest_wants_fix',
        'guest_wants_relocation',
        'guest_wants_cancellation',
        'guest_wants_refund',
        'guest_wants_compensation',
        'host_accepts_problem',
        'host_offered_fix',
        'host_offered_relocation',
        'host_offered_refund',
        'host_offered_compensation',
        'host_denied_problem',
        'resolution_type',
        'resolution_status',
        'compensation_amount',
        'refund_amount',
        'price_difference_amount',
        'currency',
        'cleaning_task_id',
        'maintenance_request_id',
        'booking_relocation_id',
        'booking_cancellation_id',
        'booking_refund_id',
        'complaint_case_id',
        'snapshot_compared',
        'auto_match_confidence',
        'future_review_required',
        'future_review_comment',
        'resolved_at',
        'closed_at',
    ];

    protected $attributes = [
        'severity' => 'medium',
        'status' => 'reported',
        'guest_wants_to_stay' => false,
        'guest_wants_fix' => true,
        'guest_wants_relocation' => false,
        'guest_wants_cancellation' => false,
        'guest_wants_refund' => false,
        'guest_wants_compensation' => false,
        'host_offered_fix' => false,
        'host_offered_relocation' => false,
        'host_offered_refund' => false,
        'host_offered_compensation' => false,
        'host_denied_problem' => false,
        'compensation_amount' => 0,
        'refund_amount' => 0,
        'price_difference_amount' => 0,
        'snapshot_compared' => false,
        'future_review_required' => false,
    ];

    /**
     * Defines how Laravel converts stored mismatch report attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'discovered_at' => 'datetime',
            'guest_wants_to_stay' => 'boolean',
            'guest_wants_fix' => 'boolean',
            'guest_wants_relocation' => 'boolean',
            'guest_wants_cancellation' => 'boolean',
            'guest_wants_refund' => 'boolean',
            'guest_wants_compensation' => 'boolean',
            'host_accepts_problem' => 'boolean',
            'host_offered_fix' => 'boolean',
            'host_offered_relocation' => 'boolean',
            'host_offered_refund' => 'boolean',
            'host_offered_compensation' => 'boolean',
            'host_denied_problem' => 'boolean',
            'compensation_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'price_difference_amount' => 'decimal:2',
            'snapshot_compared' => 'boolean',
            'auto_match_confidence' => 'decimal:2',
            'future_review_required' => 'boolean',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this mismatch report to the Booking being disputed.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this mismatch report to the active stay when the guest already lives there.
     */
    public function stay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class, 'booking_stay_id');
    }

    /**
     * Links this mismatch report to the check-in record when the issue starts at arrival.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this mismatch report to the checkout record when the issue is reported after stay.
     */
    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    /**
     * Links this mismatch report to the guest who reported it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this mismatch report to the host responsible for the listing.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this mismatch report to the booked property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this mismatch report to the booked room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this mismatch report to the exact sleeping place the guest booked.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this mismatch report to a relocation created from the resolution.
     */
    public function bookingRelocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class);
    }

    /**
     * Links this mismatch report to a cancellation created from the resolution.
     */
    public function bookingCancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class);
    }

    /**
     * Links this mismatch report to a refund created from the resolution.
     */
    public function bookingRefund(): BelongsTo
    {
        return $this->belongsTo(BookingRefund::class);
    }

    /**
     * Lists all item-level mismatches attached to this report.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BookingListingMismatchItem::class);
    }

    /**
     * Lists guest and host evidence attached to this report.
     */
    public function media(): HasMany
    {
        return $this->hasMany(BookingListingMismatchMedia::class);
    }

    /**
     * Lists host responses and offers attached to this report.
     */
    public function hostResponses(): HasMany
    {
        return $this->hasMany(BookingListingMismatchHostResponse::class);
    }

    /**
     * Lists guest replies to host offers.
     */
    public function guestResponses(): HasMany
    {
        return $this->hasMany(BookingListingMismatchGuestResponse::class);
    }

    /**
     * Lists offered resolution choices for this report.
     */
    public function resolutionOptions(): HasMany
    {
        return $this->hasMany(BookingListingMismatchResolutionOption::class);
    }

    /**
     * Lists refund and compensation calculation lines for this report.
     */
    public function compensationLines(): HasMany
    {
        return $this->hasMany(BookingListingMismatchCompensationLine::class);
    }

    /**
     * Lists automatic warning rows generated for this report.
     */
    public function warnings(): HasMany
    {
        return $this->hasMany(BookingListingMismatchWarning::class);
    }

    /**
     * Lists status changes for audit and timeline views.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingListingMismatchStatusLog::class);
    }

    /**
     * Lists domain events recorded for this report.
     */
    public function events(): HasMany
    {
        return $this->hasMany(BookingListingMismatchEvent::class);
    }
}
