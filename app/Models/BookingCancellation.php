<?php

namespace App\Models;

use Database\Factories\BookingCancellationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingCancellation extends Model
{
    /** @use HasFactory<BookingCancellationFactory> */
    use HasFactory;

    protected $fillable = [
        'cancellation_number',
        'booking_id',
        'booking_cancellation_preview_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'cancelled_by_user_id',
        'cancelled_by_type',
        'cancellation_type',
        'reason_key',
        'comment',
        'status',
        'check_in_date',
        'check_out_date',
        'cancelled_at',
        'hours_before_check_in',
        'nights_before_check_in',
        'nights_used',
        'nights_unused',
        'policy_snapshot_id',
        'accommodation_amount',
        'cleaning_fee_amount',
        'service_fee_amount',
        'deposit_amount',
        'tax_amount',
        'city_fee_amount',
        'accommodation_refund_amount',
        'cleaning_fee_refund_amount',
        'service_fee_refund_amount',
        'deposit_refund_amount',
        'tax_refund_amount',
        'city_fee_refund_amount',
        'penalty_amount',
        'host_payout_adjustment_amount',
        'total_refund_amount',
        'total_non_refundable_amount',
        'currency',
        'refund_status',
        'booking_refund_id',
        'calendar_release_status',
        'dates_released_at',
        'requires_host_response',
        'requires_dispute',
        'complaint_case_id',
        'mismatch_report_id',
        'host_unresponsive_case_id',
        'no_show_case_id',
        'completed_at',
        'closed_at',
    ];

    protected $hidden = [
        'host_payout_adjustment_amount',
    ];

    /**
     * Defines how Laravel converts stored cancellation attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'cancelled_at' => 'datetime',
            'hours_before_check_in' => 'integer',
            'nights_before_check_in' => 'integer',
            'nights_used' => 'integer',
            'nights_unused' => 'integer',
            'accommodation_amount' => 'decimal:2',
            'cleaning_fee_amount' => 'decimal:2',
            'service_fee_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'city_fee_amount' => 'decimal:2',
            'accommodation_refund_amount' => 'decimal:2',
            'cleaning_fee_refund_amount' => 'decimal:2',
            'service_fee_refund_amount' => 'decimal:2',
            'deposit_refund_amount' => 'decimal:2',
            'tax_refund_amount' => 'decimal:2',
            'city_fee_refund_amount' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'host_payout_adjustment_amount' => 'decimal:2',
            'total_refund_amount' => 'decimal:2',
            'total_non_refundable_amount' => 'decimal:2',
            'dates_released_at' => 'datetime',
            'requires_host_response' => 'boolean',
            'requires_dispute' => 'boolean',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this cancellation to the Booking that was cancelled.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this cancellation to its refund preview when one was required.
     */
    public function preview(): BelongsTo
    {
        return $this->belongsTo(BookingCancellationPreview::class, 'booking_cancellation_preview_id');
    }

    /**
     * Links this cancellation to the guest.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this cancellation to the host.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this cancellation to the actor that confirmed it.
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    /**
     * Links this cancellation to the copied Property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this cancellation to the copied Room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this cancellation to the exact SleepingPlace calendar being released or kept blocked.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this cancellation to the immutable policy snapshot used for calculation.
     */
    public function policySnapshot(): BelongsTo
    {
        return $this->belongsTo(BookingCancellationPolicySnapshot::class, 'policy_snapshot_id');
    }

    /**
     * Links this cancellation to the internal BookingRefund record.
     */
    public function bookingRefund(): BelongsTo
    {
        return $this->belongsTo(BookingRefund::class);
    }

    /**
     * Lists the refund calculation lines shown to guest and host.
     */
    public function refundLines(): HasMany
    {
        return $this->hasMany(BookingCancellationRefundLine::class);
    }

    /**
     * Lists status transitions for this cancellation.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingCancellationStatusLog::class);
    }

    /**
     * Lists timeline events for this cancellation.
     */
    public function events(): HasMany
    {
        return $this->hasMany(BookingCancellationEvent::class);
    }

    /**
     * Lists replacement suggestions created after the cancellation.
     */
    public function alternatives(): HasMany
    {
        return $this->hasMany(BookingCancellationAlternative::class);
    }
}
