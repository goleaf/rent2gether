<?php

namespace App\Models;

use Database\Factories\BookingPaymentStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPaymentStatusLog extends Model
{
    /** @use HasFactory<BookingPaymentStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_payment_id',
        'booking_payment_attempt_id',
        'booking_refund_id',
        'booking_id',
        'user_id',
        'old_status',
        'new_status',
        'event_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how Laravel converts stored Payment Status Log attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this log to a payment when present.
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    /**
     * Links this log to a payment attempt when present.
     */
    public function bookingPaymentAttempt(): BelongsTo
    {
        return $this->belongsTo(BookingPaymentAttempt::class);
    }

    /**
     * Links this log to a refund when present.
     */
    public function bookingRefund(): BelongsTo
    {
        return $this->belongsTo(BookingRefund::class);
    }

    /**
     * Links this log to the Booking context when present.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this log to the user who caused it when present.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
