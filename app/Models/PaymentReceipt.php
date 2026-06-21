<?php

namespace App\Models;

use Database\Factories\PaymentReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    /** @use HasFactory<PaymentReceiptFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'booking_payment_id',
        'guest_user_id',
        'receipt_number',
        'status',
        'issued_at',
        'receipt_data_json',
        'file_path',
    ];

    /**
     * Defines how Laravel converts stored Payment Receipt attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'receipt_data_json' => 'array',
        ];
    }

    /**
     * Links this receipt to its Booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this receipt to the payment it documents.
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    /**
     * Links this receipt to the guest who can view it.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }
}
