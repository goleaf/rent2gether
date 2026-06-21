<?php

namespace App\Models;

use Database\Factories\BookingQuoteSuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingQuoteSuggestion extends Model
{
    /** @use HasFactory<BookingQuoteSuggestionFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_quote_id',
        'suggestion_type',
        'sleeping_place_id',
        'room_id',
        'property_id',
        'check_in_date',
        'check_out_date',
        'nights_count',
        'price_preview_amount',
        'currency',
        'message_key',
        'sort_order',
    ];

    /**
     * Defines how Laravel converts stored Booking Quote Suggestion attributes.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'nights_count' => 'integer',
            'price_preview_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Links this suggestion to the Quote that needs an alternative.
     */
    public function bookingQuote(): BelongsTo
    {
        return $this->belongsTo(BookingQuote::class);
    }

    /**
     * Links this suggestion to an alternative Sleeping Place when applicable.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this suggestion to a Room context when applicable.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this suggestion to a Property context when applicable.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
