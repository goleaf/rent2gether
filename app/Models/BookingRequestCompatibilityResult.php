<?php

namespace App\Models;

use Database\Factories\BookingRequestCompatibilityResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequestCompatibilityResult extends Model
{
    public const STATUS_GOOD = 'good';

    public const STATUS_MEDIUM = 'medium';

    public const STATUS_WARNING = 'warning';

    public const STATUS_CONFLICT = 'conflict';

    public const STATUS_BLOCKING = 'blocking';

    /** @use HasFactory<BookingRequestCompatibilityResultFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_request_id',
        'compatibility_key',
        'status',
        'severity',
        'message_key',
        'message_params_json',
    ];

    protected $attributes = [
        'severity' => 'info',
    ];

    /**
     * Defines how Laravel converts stored compatibility attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
        ];
    }

    /**
     * Links this compatibility result to the Booking Request.
     */
    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }
}
