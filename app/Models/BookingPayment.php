<?php

namespace App\Models;

use Database\Factories\BookingPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingPayment extends Model
{
    /** @use HasFactory<BookingPaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'booking_id',
        'booking_quote_id',
        'booking_request_id',
        'booking_extension_id',
        'booking_relocation_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'payment_type',
        'payment_purpose',
        'payment_method',
        'status',
        'amount',
        'currency',
        'required_now_amount',
        'remaining_amount',
        'remaining_due_at',
        'provider',
        'provider_payment_id',
        'provider_status',
        'provider_payload_json',
        'payment_deadline_at',
        'paid_at',
        'failed_at',
        'expired_at',
        'cancelled_at',
        'failure_reason',
        'description',
    ];

    protected $hidden = [
        'provider_payment_id',
        'provider_payload_json',
    ];

    /**
     * Defines how Laravel converts stored Booking Payment attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'required_now_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'provider_payload_json' => 'array',
            'remaining_due_at' => 'datetime',
            'payment_deadline_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'expired_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Links this payment to the Booking it settles.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this payment to the Quote used to calculate it.
     */
    public function bookingQuote(): BelongsTo
    {
        return $this->belongsTo(BookingQuote::class);
    }

    /**
     * Links this payment to the approved Booking Request when applicable.
     */
    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }

    /**
     * Links this payment to a stay extension when applicable.
     */
    public function bookingExtension(): BelongsTo
    {
        return $this->belongsTo(BookingExtension::class);
    }

    /**
     * Links this payment to the guest who pays it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this payment to the host receiving booking context.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this payment to the Property context copied from the Booking.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this payment to the Room context copied from the Booking.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this payment to the exact Sleeping Place being booked.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists payment attempts for retry and audit flows.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(BookingPaymentAttempt::class);
    }

    /**
     * Lists money allocations that explain what the payment covers.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(BookingPaymentAllocation::class);
    }

    /**
     * Lists payment deadlines tied to this payment.
     */
    public function deadlines(): HasMany
    {
        return $this->hasMany(BookingPaymentDeadline::class);
    }

    /**
     * Lists payment status audit rows.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingPaymentStatusLog::class);
    }

    /**
     * Fetches the future-ready receipt for this payment.
     */
    public function receipt(): HasOne
    {
        return $this->hasOne(PaymentReceipt::class);
    }
}
