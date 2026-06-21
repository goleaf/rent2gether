<?php

namespace App\Models;

use Database\Factories\BookingRelocationOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRelocationOption extends Model
{
    /** @use HasFactory<BookingRelocationOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_relocation_id',
        'sleeping_place_id',
        'property_id',
        'room_id',
        'price_difference_amount',
        'additional_payment_amount',
        'refund_amount',
        'additional_deposit_amount',
        'currency',
        'availability_status',
        'compatibility_status',
        'pricing_status',
        'distance_label',
        'room_privacy_level',
        'comfort_score',
        'match_score',
        'host_note',
        'guest_selected',
        'selected_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'price_difference_amount' => 'decimal:2',
            'additional_payment_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'additional_deposit_amount' => 'decimal:2',
            'comfort_score' => 'decimal:2',
            'match_score' => 'decimal:2',
            'guest_selected' => 'boolean',
            'selected_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Links this option to the relocation request that generated it.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }

    /**
     * Links this option to the candidate sleeping place.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this option to the candidate property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this option to the candidate room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
