<?php

namespace App\Models;

use Database\Factories\BookingExtensionValidationResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtensionValidationResult extends Model
{
    /** @use HasFactory<BookingExtensionValidationResultFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_extension_id',
        'validation_key',
        'severity',
        'message_key',
        'message_params_json',
        'blocking',
        'visible_to_guest',
        'visible_to_host',
    ];

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
     * Links this validation result to the extension request it describes.
     */
    public function bookingExtension(): BelongsTo
    {
        return $this->belongsTo(BookingExtension::class);
    }
}
