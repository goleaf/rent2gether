<?php

namespace App\Models;

use Database\Factories\BookingPriceLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPriceLine extends Model
{
    /** @use HasFactory<BookingPriceLineFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'type',
        'label_key',
        'amount',
        'currency',
        'is_refundable',
        'metadata_json',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_refundable' => 'boolean',
            'metadata_json' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
