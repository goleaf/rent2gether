<?php

namespace App\Models;

use Database\Factories\BookingPaymentAllocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPaymentAllocation extends Model
{
    /** @use HasFactory<BookingPaymentAllocationFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_payment_id',
        'booking_id',
        'allocation_type',
        'amount',
        'currency',
        'refundable',
        'line_reference_type',
        'line_reference_id',
    ];

    /**
     * Defines how Laravel converts stored Payment Allocation attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refundable' => 'boolean',
        ];
    }

    /**
     * Links this allocation to its parent payment.
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    /**
     * Links this allocation to the Booking it explains.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
