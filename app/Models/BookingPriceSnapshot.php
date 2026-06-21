<?php

namespace App\Models;

use Database\Factories\BookingPriceSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPriceSnapshot extends Model
{
    /** @use HasFactory<BookingPriceSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'booking_quote_id',
        'pricing_settings_snapshot_json',
        'quote_lines_snapshot_json',
        'discounts_snapshot_json',
        'promo_code_snapshot_json',
        'accommodation_before_discount',
        'discount_amount',
        'accommodation_after_discount',
        'early_check_in_fee',
        'late_checkout_fee',
        'extra_guest_fee',
        'cleaning_fee',
        'guest_service_fee',
        'host_service_fee',
        'tax_amount',
        'city_fee',
        'deposit_amount',
        'total_without_deposit',
        'total_payable',
        'host_payout_amount',
        'refundable_amount',
        'non_refundable_amount',
        'currency',
    ];

    /**
     * Defines how Laravel converts stored price snapshot attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'pricing_settings_snapshot_json' => 'array',
            'quote_lines_snapshot_json' => 'array',
            'discounts_snapshot_json' => 'array',
            'promo_code_snapshot_json' => 'array',
            'accommodation_before_discount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'accommodation_after_discount' => 'decimal:2',
            'early_check_in_fee' => 'decimal:2',
            'late_checkout_fee' => 'decimal:2',
            'extra_guest_fee' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
            'guest_service_fee' => 'decimal:2',
            'host_service_fee' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'city_fee' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'total_without_deposit' => 'decimal:2',
            'total_payable' => 'decimal:2',
            'host_payout_amount' => 'decimal:2',
            'refundable_amount' => 'decimal:2',
            'non_refundable_amount' => 'decimal:2',
        ];
    }

    /**
     * Links this frozen price snapshot to its Booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this frozen price snapshot to the source Quote when available.
     */
    public function bookingQuote(): BelongsTo
    {
        return $this->belongsTo(BookingQuote::class);
    }
}
