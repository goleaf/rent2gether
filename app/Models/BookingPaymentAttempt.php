<?php

namespace App\Models;

use Database\Factories\BookingPaymentAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPaymentAttempt extends Model
{
    /** @use HasFactory<BookingPaymentAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_payment_id',
        'booking_id',
        'guest_user_id',
        'attempt_number',
        'status',
        'payment_method',
        'amount',
        'currency',
        'provider',
        'provider_attempt_id',
        'provider_redirect_url',
        'provider_status',
        'provider_error_code',
        'provider_error_message',
        'provider_payload_json',
        'started_at',
        'succeeded_at',
        'failed_at',
        'cancelled_at',
        'expired_at',
    ];

    protected $hidden = [
        'provider_attempt_id',
        'provider_payload_json',
    ];

    /**
     * Defines how Laravel converts stored Payment Attempt attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'provider_payload_json' => 'array',
            'started_at' => 'datetime',
            'succeeded_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    /**
     * Links this attempt to its parent payment.
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    /**
     * Links this attempt to the Booking it belongs to.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this attempt to the guest who started it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }
}
