<?php

namespace App\Models;

use Database\Factories\BookingCancellationAlternativeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCancellationAlternative extends Model
{
    /** @use HasFactory<BookingCancellationAlternativeFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_cancellation_id',
        'sleeping_place_id',
        'property_id',
        'room_id',
        'suggestion_type',
        'check_in_date',
        'check_out_date',
        'price_preview_amount',
        'currency',
        'message_key',
        'sort_order',
    ];

    /**
     * Defines how Laravel converts stored cancellation alternative attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'price_preview_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Links this alternative to the parent cancellation.
     */
    public function cancellation(): BelongsTo
    {
        return $this->belongsTo(BookingCancellation::class, 'booking_cancellation_id');
    }

    /**
     * Links this alternative to a suggested SleepingPlace when available.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this alternative to a suggested Property when available.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this alternative to a suggested Room when available.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
