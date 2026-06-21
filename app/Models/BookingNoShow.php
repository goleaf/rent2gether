<?php

namespace App\Models;

use Database\Factories\BookingNoShowFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingNoShow extends Model
{
    /** @use HasFactory<BookingNoShowFactory> */
    use HasFactory;

    protected $fillable = [
        'no_show_number',
        'booking_id',
        'booking_check_in_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'status',
        'reason_key',
        'check_in_date',
        'planned_check_in_time',
        'check_in_window',
        'no_show_started_at',
        'host_reported_at',
        'guest_contacted_at',
        'guest_last_response_at',
        'waiting_period_minutes',
        'waiting_until',
        'waiting_expired_at',
        'guest_not_answering',
        'guest_warned_late_arrival',
        'guest_warned_cancellation',
        'guest_claimed_arrived',
        'host_marked_no_show',
        'guest_response_type',
        'guest_response_message',
        'host_comment',
        'guest_comment',
        'decision_key',
        'decision_at',
        'decided_by_user_id',
        'refund_or_penalty_status',
        'refund_amount',
        'penalty_amount',
        'deposit_refund_amount',
        'cleaning_fee_refund_amount',
        'service_fee_refund_amount',
        'host_payout_amount',
        'currency',
        'calendar_release_status',
        'dates_released_at',
        'booking_cancellation_id',
        'booking_refund_id',
        'complaint_case_id',
        'host_unresponsive_case_id',
        'future_support_review_required',
        'future_support_comment',
        'completed_at',
        'closed_at',
    ];

    protected $hidden = [
        'future_support_comment',
    ];

    protected $attributes = [
        'status' => 'watching',
        'waiting_period_minutes' => 180,
        'guest_not_answering' => false,
        'guest_warned_late_arrival' => false,
        'guest_warned_cancellation' => false,
        'guest_claimed_arrived' => false,
        'host_marked_no_show' => false,
        'refund_or_penalty_status' => 'not_calculated',
        'refund_amount' => 0,
        'penalty_amount' => 0,
        'deposit_refund_amount' => 0,
        'cleaning_fee_refund_amount' => 0,
        'service_fee_refund_amount' => 0,
        'host_payout_amount' => 0,
        'calendar_release_status' => 'not_released',
        'future_support_review_required' => false,
    ];

    /**
     * Defines how stored no-show case values are converted for PHP use.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'no_show_started_at' => 'datetime',
            'host_reported_at' => 'datetime',
            'guest_contacted_at' => 'datetime',
            'guest_last_response_at' => 'datetime',
            'waiting_period_minutes' => 'integer',
            'waiting_until' => 'datetime',
            'waiting_expired_at' => 'datetime',
            'guest_not_answering' => 'boolean',
            'guest_warned_late_arrival' => 'boolean',
            'guest_warned_cancellation' => 'boolean',
            'guest_claimed_arrived' => 'boolean',
            'host_marked_no_show' => 'boolean',
            'decision_at' => 'datetime',
            'refund_amount' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'deposit_refund_amount' => 'decimal:2',
            'cleaning_fee_refund_amount' => 'decimal:2',
            'service_fee_refund_amount' => 'decimal:2',
            'host_payout_amount' => 'decimal:2',
            'dates_released_at' => 'datetime',
            'future_support_review_required' => 'boolean',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this no-show case to its booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this no-show case to the related check-in record when it exists.
     */
    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(BookingCheckIn::class, 'booking_check_in_id');
    }

    /**
     * Links this no-show case to the guest.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this no-show case to the host.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this no-show case to the copied property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this no-show case to the copied room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this no-show case to the exact sleeping place calendar.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this no-show case to the actor that made the decision.
     */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    /**
     * Links this no-show case to the cancellation created from it.
     */
    public function bookingCancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class);
    }

    /**
     * Links this no-show case to the refund created from it.
     */
    public function bookingRefund(): BelongsTo
    {
        return $this->belongsTo(BookingRefund::class);
    }

    /**
     * Links this no-show case to a host-unresponsive case that blocked or converted it.
     */
    public function hostUnresponsiveCase(): BelongsTo
    {
        return $this->belongsTo(BookingHostUnresponsiveCase::class, 'host_unresponsive_case_id');
    }

    /**
     * Lists contact attempts made while verifying this no-show.
     */
    public function contactAttempts(): HasMany
    {
        return $this->hasMany(BookingNoShowContactAttempt::class);
    }

    /**
     * Lists guest responses collected during this no-show flow.
     */
    public function guestResponses(): HasMany
    {
        return $this->hasMany(BookingNoShowGuestResponse::class);
    }

    /**
     * Lists evidence media attached to this no-show case.
     */
    public function media(): HasMany
    {
        return $this->hasMany(BookingNoShowMedia::class);
    }

    /**
     * Lists status transition records for this no-show case.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingNoShowStatusLog::class);
    }

    /**
     * Lists timeline events for this no-show case.
     */
    public function events(): HasMany
    {
        return $this->hasMany(BookingNoShowEvent::class);
    }
}
