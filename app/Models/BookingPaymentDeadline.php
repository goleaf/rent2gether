<?php

namespace App\Models;

use Database\Factories\BookingPaymentDeadlineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPaymentDeadline extends Model
{
    /** @use HasFactory<BookingPaymentDeadlineFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'booking_payment_id',
        'deadline_type',
        'due_at',
        'status',
    ];

    /**
     * Defines how Laravel converts stored Payment Deadline attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
        ];
    }

    /**
     * Links this deadline to its Booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this deadline to a payment when the deadline is payment-specific.
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }
}
