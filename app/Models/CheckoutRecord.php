<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id', 'planned_time', 'actual_departure_at',
    'keys_returned', 'locker_emptied', 'belongings_collected', 'linen_returned', 'place_clean',
    'has_damage', 'has_extra_dirt', 'has_forgotten_items',
    'deposit_withheld', 'withhold_amount', 'withhold_reason',
    'photos_after', 'guest_confirmed', 'host_confirmed', 'status',
])]
class CheckoutRecord extends Model
{
    protected $casts = [
        'actual_departure_at' => 'datetime',
        'keys_returned' => 'boolean',
        'locker_emptied' => 'boolean',
        'belongings_collected' => 'boolean',
        'linen_returned' => 'boolean',
        'place_clean' => 'boolean',
        'has_damage' => 'boolean',
        'has_extra_dirt' => 'boolean',
        'has_forgotten_items' => 'boolean',
        'deposit_withheld' => 'boolean',
        'withhold_amount' => 'decimal:2',
        'photos_after' => 'array',
        'guest_confirmed' => 'boolean',
        'host_confirmed' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
