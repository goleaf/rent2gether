<?php

namespace App\Models;

use Database\Factories\BookingRefundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRefund extends Model
{
    /** @use HasFactory<BookingRefundFactory> */
    use HasFactory;

    protected $fillable = [
        'refund_number',
        'booking_id',
        'booking_payment_id',
        'guest_user_id',
        'host_user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'refund_type',
        'status',
        'amount',
        'currency',
        'reason_key',
        'source_type',
        'source_id',
        'provider',
        'provider_refund_id',
        'provider_status',
        'provider_payload_json',
        'requested_at',
        'approved_at',
        'processed_at',
        'completed_at',
        'failed_at',
        'failure_reason',
        'comment',
    ];

    protected $hidden = [
        'provider_refund_id',
        'provider_payload_json',
    ];

    /**
     * Defines how Laravel converts stored Booking Refund attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'provider_payload_json' => 'array',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * Links this refund to its Booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this refund to the original payment when available.
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }

    /**
     * Links this refund to the guest receiving money.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Links this refund to the host context copied from the Booking.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Links this refund to the Property context copied from the Booking.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this refund to the Room context copied from the Booking.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this refund to the exact Sleeping Place context copied from the Booking.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
