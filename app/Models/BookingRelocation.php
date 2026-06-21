<?php

namespace App\Models;

use Database\Factories\BookingRelocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingRelocation extends Model
{
    /** @use HasFactory<BookingRelocationFactory> */
    use HasFactory;

    protected $fillable = [
        'relocation_number',
        'original_booking_id',
        'new_booking_id',
        'booking_stay_id',
        'guest_user_id',
        'host_user_id',
        'current_property_id',
        'current_room_id',
        'current_sleeping_place_id',
        'new_property_id',
        'new_room_id',
        'new_sleeping_place_id',
        'source_type',
        'source_id',
        'requested_by_user_id',
        'requested_by_type',
        'reason',
        'status',
        'relocation_date',
        'relocation_time',
        'check_in_date',
        'check_out_date',
        'original_check_in_date',
        'original_check_out_date',
        'old_period_check_in_date',
        'old_period_check_out_date',
        'new_period_check_in_date',
        'new_period_check_out_date',
        'old_remaining_value_amount',
        'new_remaining_value_amount',
        'price_difference_amount',
        'additional_payment_amount',
        'refund_amount',
        'additional_deposit_amount',
        'cleaning_fee_difference_amount',
        'service_fee_difference_amount',
        'host_payout_difference_amount',
        'currency',
        'price_difference_payer',
        'requires_guest_consent',
        'requires_host_consent',
        'guest_consented_at',
        'host_consented_at',
        'requires_payment',
        'payment_status',
        'booking_payment_id',
        'payment_method',
        'paid_at',
        'payment_deadline_at',
        'requires_refund',
        'refund_status',
        'booking_refund_id',
        'guest_comment',
        'host_comment',
        'support_comment',
        'future_support_status',
        'future_support_decision',
        'hold_dates',
        'hold_expires_at',
        'expires_at',
        'approved_at',
        'applied_at',
        'rejected_at',
        'cancelled_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'relocation_date' => 'date:Y-m-d',
            'check_in_date' => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'original_check_in_date' => 'date:Y-m-d',
            'original_check_out_date' => 'date:Y-m-d',
            'old_period_check_in_date' => 'date:Y-m-d',
            'old_period_check_out_date' => 'date:Y-m-d',
            'new_period_check_in_date' => 'date:Y-m-d',
            'new_period_check_out_date' => 'date:Y-m-d',
            'old_remaining_value_amount' => 'decimal:2',
            'new_remaining_value_amount' => 'decimal:2',
            'price_difference_amount' => 'decimal:2',
            'additional_payment_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'additional_deposit_amount' => 'decimal:2',
            'cleaning_fee_difference_amount' => 'decimal:2',
            'service_fee_difference_amount' => 'decimal:2',
            'host_payout_difference_amount' => 'decimal:2',
            'requires_guest_consent' => 'boolean',
            'requires_host_consent' => 'boolean',
            'requires_payment' => 'boolean',
            'requires_refund' => 'boolean',
            'hold_dates' => 'boolean',
            'guest_consented_at' => 'datetime',
            'host_consented_at' => 'datetime',
            'paid_at' => 'datetime',
            'payment_deadline_at' => 'datetime',
            'hold_expires_at' => 'datetime',
            'expires_at' => 'datetime',
            'approved_at' => 'datetime',
            'applied_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Links this relocation to the original booking whose sleeping-place history is preserved.
     */
    public function originalBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'original_booking_id');
    }

    /**
     * Links this relocation to the new booking segment created for the new sleeping place.
     */
    public function newBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'new_booking_id');
    }

    /**
     * Links this relocation to the stay active at the time of the move.
     */
    public function bookingStay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class);
    }

    /**
     * Links this relocation to the guest whose stay is moving.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this relocation to the host who owns the involved booking.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this relocation to the current property before the move.
     */
    public function currentProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'current_property_id');
    }

    /**
     * Links this relocation to the current room before the move.
     */
    public function currentRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'current_room_id');
    }

    /**
     * Links this relocation to the current sleeping place before the move.
     */
    public function currentSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'current_sleeping_place_id');
    }

    /**
     * Links this relocation to the target property when a new place is selected.
     */
    public function newProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'new_property_id');
    }

    /**
     * Links this relocation to the target room when a new place is selected.
     */
    public function newRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'new_room_id');
    }

    /**
     * Links this relocation to the target sleeping place when selected.
     */
    public function newSleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class, 'new_sleeping_place_id');
    }

    /**
     * Links this relocation to the user or system actor that requested it.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     * Links this relocation to its internal relocation payment record.
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    /**
     * Links this relocation to its internal relocation refund record.
     */
    public function bookingRefund(): BelongsTo
    {
        return $this->belongsTo(BookingRefund::class);
    }

    /**
     * Lists target sleeping-place options generated for this relocation.
     */
    public function options(): HasMany
    {
        return $this->hasMany(BookingRelocationOption::class);
    }

    /**
     * Lists price difference lines for the relocation.
     */
    public function priceLines(): HasMany
    {
        return $this->hasMany(BookingRelocationPriceLine::class);
    }

    /**
     * Lists availability and consent validation results.
     */
    public function validationResults(): HasMany
    {
        return $this->hasMany(BookingRelocationValidationResult::class);
    }

    /**
     * Lists guest and host consent records required before applying the move.
     */
    public function consents(): HasMany
    {
        return $this->hasMany(BookingRelocationConsent::class);
    }

    /**
     * Lists host responses made during relocation negotiation.
     */
    public function hostResponses(): HasMany
    {
        return $this->hasMany(BookingRelocationHostResponse::class);
    }

    /**
     * Lists guest responses made during relocation negotiation.
     */
    public function guestResponses(): HasMany
    {
        return $this->hasMany(BookingRelocationGuestResponse::class);
    }

    /**
     * Lists key, locker, bedding, and towel transfers for the move.
     */
    public function inventoryTransfers(): HasMany
    {
        return $this->hasMany(BookingRelocationInventoryTransfer::class);
    }

    /**
     * Lists status transitions for audit history.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingRelocationStatusLog::class);
    }

    /**
     * Lists timeline events emitted by the relocation flow.
     */
    public function events(): HasMany
    {
        return $this->hasMany(BookingRelocationEvent::class);
    }
}
