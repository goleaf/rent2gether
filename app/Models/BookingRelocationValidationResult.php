<?php

namespace App\Models;

use Database\Factories\BookingRelocationValidationResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRelocationValidationResult extends Model
{
    /** @use HasFactory<BookingRelocationValidationResultFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_relocation_id',
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
     * Links this validation result to its relocation.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }
}
