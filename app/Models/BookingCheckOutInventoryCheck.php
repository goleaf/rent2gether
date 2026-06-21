<?php

namespace App\Models;

use Database\Factories\BookingCheckOutInventoryCheckFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCheckOutInventoryCheck extends Model
{
    /** @use HasFactory<BookingCheckOutInventoryCheckFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_check_out_id',
        'booking_id',
        'inventory_item_id',
        'item_name_snapshot',
        'expected_return',
        'returned',
        'lost',
        'damaged',
        'needs_replacement',
        'deduction_requested',
        'deduction_amount',
        'currency',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'expected_return' => 'boolean',
            'returned' => 'boolean',
            'lost' => 'boolean',
            'damaged' => 'boolean',
            'needs_replacement' => 'boolean',
            'deduction_requested' => 'boolean',
            'deduction_amount' => 'decimal:2',
        ];
    }

    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(BookingCheckOut::class, 'booking_check_out_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
