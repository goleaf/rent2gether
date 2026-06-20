<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'booking_id', 'planned_time', 'actual_departure_at',
    'planned_checkout_time', 'actual_checkout_at',
    'keys_returned', 'locker_emptied', 'belongings_collected', 'belongings_removed', 'linen_returned', 'place_clean',
    'has_damage', 'no_damage', 'damage_found', 'damage_description', 'has_extra_dirt', 'has_forgotten_items',
    'deposit_withheld', 'deposit_action', 'withhold_amount', 'withhold_reason',
    'photos_after', 'damage_media', 'guest_confirmed', 'host_confirmed',
    'guest_confirmed_checkout_at', 'host_confirmed_checkout_at', 'status',
])]
class CheckoutRecord extends Model
{
    protected $casts = [
        'actual_departure_at' => 'datetime',
        'actual_checkout_at' => 'datetime',
        'keys_returned' => 'boolean',
        'locker_emptied' => 'boolean',
        'belongings_collected' => 'boolean',
        'belongings_removed' => 'boolean',
        'linen_returned' => 'boolean',
        'place_clean' => 'boolean',
        'has_damage' => 'boolean',
        'no_damage' => 'boolean',
        'damage_found' => 'boolean',
        'has_extra_dirt' => 'boolean',
        'has_forgotten_items' => 'boolean',
        'deposit_withheld' => 'boolean',
        'withhold_amount' => 'decimal:2',
        'photos_after' => 'array',
        'damage_media' => 'array',
        'guest_confirmed' => 'boolean',
        'host_confirmed' => 'boolean',
        'guest_confirmed_checkout_at' => 'datetime',
        'host_confirmed_checkout_at' => 'datetime',
    ];

    /**
     * Links this Checkout Record to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Lists related Media Item records attached to this Checkout Record through a polymorphic relation.
     */
    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable');
    }
}
