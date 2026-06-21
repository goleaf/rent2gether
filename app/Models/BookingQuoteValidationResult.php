<?php

namespace App\Models;

use Database\Factories\BookingQuoteValidationResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingQuoteValidationResult extends Model
{
    /** @use HasFactory<BookingQuoteValidationResultFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_quote_id',
        'validation_key',
        'severity',
        'message_key',
        'message_params_json',
        'blocking',
        'visible_to_guest',
        'visible_to_host',
    ];

    /**
     * Defines how Laravel converts stored Booking Quote Validation Result attributes.
     */
    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'blocking' => 'boolean',
            'visible_to_guest' => 'boolean',
            'visible_to_host' => 'boolean',
        ];
    }

    /**
     * Links this validation result to the Booking Quote it was produced for.
     */
    public function bookingQuote(): BelongsTo
    {
        return $this->belongsTo(BookingQuote::class);
    }
}
