<?php

namespace App\Models;

use Database\Factories\BookingQuoteLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingQuoteLine extends Model
{
    /** @use HasFactory<BookingQuoteLineFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_quote_id',
        'line_type',
        'label_key',
        'date',
        'quantity',
        'unit_amount',
        'amount',
        'currency',
        'is_discount',
        'is_fee',
        'is_deposit',
        'is_refundable',
        'is_payable_now',
        'sort_order',
    ];

    /**
     * Defines how Laravel converts stored Booking Quote Line attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'decimal:2',
            'unit_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'is_discount' => 'boolean',
            'is_fee' => 'boolean',
            'is_deposit' => 'boolean',
            'is_refundable' => 'boolean',
            'is_payable_now' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Links this price line to the Booking Quote it explains.
     */
    public function bookingQuote(): BelongsTo
    {
        return $this->belongsTo(BookingQuote::class);
    }
}
