<?php

namespace App\Models;

use App\Enums\BookingExtensionStatus;
use Database\Factories\BookingExtensionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingExtension extends Model
{
    /** @use HasFactory<BookingExtensionFactory> */
    use HasFactory;

    protected $fillable = [
        'extension_number',
        'booking_id',
        'booking_stay_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_quote_id',
        'booking_payment_id',
        'current_checkout_date',
        'requested_new_checkout_date',
        'current_check_out_date',
        'current_check_out_time',
        'new_check_out_date',
        'new_check_out_time',
        'additional_nights',
        'additional_nights_count',
        'additional_chargeable_days_count',
        'additional_calendar_presence_days_count',
        'additional_amount',
        'original_check_out',
        'new_check_out',
        'extra_nights',
        'extra_amount',
        'discount_amount',
        'total_extra',
        'new_total',
        'extension_type',
        'payment_required',
        'requires_host_approval',
        'requires_host_confirmation',
        'requires_payment',
        'payment_status',
        'payment_method',
        'paid_at',
        'payment_deadline_at',
        'accommodation_amount',
        'service_fee_amount',
        'cleaning_fee_amount',
        'additional_deposit_amount',
        'total_payable',
        'host_payout_amount',
        'refundable_amount',
        'non_refundable_amount',
        'currency',
        'guest_message',
        'host_reply',
        'host_response',
        'reject_reason',
        'rejection_reason',
        'hold_dates',
        'hold_expires_at',
        'expires_at',
        'status',
        'approved_at',
        'applied_at',
        'declined_at',
        'rejected_at',
        'cancelled_at',
        'closed_at',
    ];

    protected $casts = [
        'current_checkout_date' => 'date',
        'requested_new_checkout_date' => 'date',
        'current_check_out_date' => 'date:Y-m-d',
        'new_check_out_date' => 'date:Y-m-d',
        'original_check_out' => 'date',
        'new_check_out' => 'date',
        'additional_amount' => 'decimal:2',
        'additional_nights_count' => 'integer',
        'additional_chargeable_days_count' => 'integer',
        'additional_calendar_presence_days_count' => 'integer',
        'extra_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_extra' => 'decimal:2',
        'new_total' => 'decimal:2',
        'payment_required' => 'boolean',
        'requires_host_confirmation' => 'boolean',
        'requires_payment' => 'boolean',
        'accommodation_amount' => 'decimal:2',
        'service_fee_amount' => 'decimal:2',
        'cleaning_fee_amount' => 'decimal:2',
        'additional_deposit_amount' => 'decimal:2',
        'total_payable' => 'decimal:2',
        'host_payout_amount' => 'decimal:2',
        'refundable_amount' => 'decimal:2',
        'non_refundable_amount' => 'decimal:2',
        'hold_dates' => 'boolean',
        'payment_deadline_at' => 'datetime',
        'hold_expires_at' => 'datetime',
        'expires_at' => 'datetime',
        'requires_host_approval' => 'boolean',
        'status' => BookingExtensionStatus::class,
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
        'applied_at' => 'datetime',
        'declined_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Registers lifecycle hooks that keep Booking Extension records consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (BookingExtension $extension): void {
            $extension->original_check_out ??= $extension->current_checkout_date;
            $extension->current_checkout_date ??= $extension->original_check_out;
            $extension->new_check_out ??= $extension->requested_new_checkout_date;
            $extension->requested_new_checkout_date ??= $extension->new_check_out;
            $extension->current_check_out_date ??= $extension->current_checkout_date;
            $extension->current_checkout_date ??= $extension->current_check_out_date;
            $extension->new_check_out_date ??= $extension->requested_new_checkout_date;
            $extension->requested_new_checkout_date ??= $extension->new_check_out_date;
            $extension->extra_nights = $extension->extra_nights ?: $extension->additional_nights;
            $extension->additional_nights = $extension->additional_nights ?: $extension->extra_nights;
            $extension->additional_nights_count = $extension->additional_nights_count ?: $extension->additional_nights;
            $extension->additional_nights = $extension->additional_nights ?: $extension->additional_nights_count;
            $extension->additional_chargeable_days_count = $extension->additional_chargeable_days_count ?: $extension->additional_nights_count;
            $extension->additional_calendar_presence_days_count = $extension->additional_calendar_presence_days_count ?: $extension->additional_nights_count + 1;
            $extension->extra_amount = $extension->extra_amount ?: $extension->additional_amount;
            $extension->additional_amount = $extension->additional_amount ?: $extension->extra_amount;
            $extension->accommodation_amount = $extension->accommodation_amount ?: $extension->additional_amount;
            $extension->total_payable = $extension->total_payable ?: $extension->total_extra;
            $extension->total_extra = $extension->total_extra ?: $extension->total_payable;
            $extension->requires_host_confirmation = $extension->requires_host_confirmation ?? $extension->requires_host_approval;
            $extension->requires_host_approval = $extension->requires_host_approval ?? $extension->requires_host_confirmation;
            $extension->requires_payment = $extension->requires_payment ?? $extension->payment_required;
            $extension->payment_required = $extension->payment_required ?? $extension->requires_payment;
            $extension->host_reply ??= $extension->host_response;
            $extension->host_response ??= $extension->host_reply;
            $extension->reject_reason ??= $extension->rejection_reason;
            $extension->rejection_reason ??= $extension->reject_reason;
        });
    }

    /**
     * Links this Booking Extension to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this extension to the active stay it will lengthen.
     */
    public function bookingStay(): BelongsTo
    {
        return $this->belongsTo(BookingStay::class);
    }

    /**
     * Links this extension to the guest who requested it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this extension to the host who owns the booking.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this extension to the property copied from the booking snapshot.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this extension to the room copied from the booking snapshot.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this extension to the same sleeping place as the booking.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this extension to its future-ready internal payment record.
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    /**
     * Lists price lines that explain the extension amount.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(BookingExtensionLine::class);
    }

    /**
     * Lists validation warnings and blocking reasons for this extension.
     */
    public function validationResults(): HasMany
    {
        return $this->hasMany(BookingExtensionValidationResult::class);
    }

    /**
     * Lists host responses for this extension request.
     */
    public function hostResponses(): HasMany
    {
        return $this->hasMany(BookingExtensionHostResponse::class);
    }

    /**
     * Lists guest responses to host questions or proposals.
     */
    public function guestResponses(): HasMany
    {
        return $this->hasMany(BookingExtensionGuestResponse::class);
    }

    /**
     * Lists status changes recorded for audit history.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingExtensionStatusLog::class);
    }

    /**
     * Lists timeline events produced by the extension flow.
     */
    public function events(): HasMany
    {
        return $this->hasMany(BookingExtensionEvent::class);
    }

    /**
     * Returns the status label text for this Booking Extension.
     */
    public function statusLabel(): string
    {
        return $this->status instanceof BookingExtensionStatus
            ? $this->status->label()
            : __('statuses.extension.'.(string) $this->status);
    }
}
