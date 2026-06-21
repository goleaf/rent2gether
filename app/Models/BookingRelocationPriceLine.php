<?php

namespace App\Models;

use Database\Factories\BookingRelocationPriceLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRelocationPriceLine extends Model
{
    /** @use HasFactory<BookingRelocationPriceLineFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_relocation_id',
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

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
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
     * Links this price line to the relocation it explains.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }
}
