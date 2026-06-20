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

    /**
     * Defines how Laravel converts stored Booking Price Line attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_refundable' => 'boolean',
            'metadata_json' => 'array',
        ];
    }

    /**
     * Links this Booking Price Line to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
