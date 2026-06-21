<?php

namespace App\Models;

use Database\Factories\BookingRequestWarningFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequestWarning extends Model
{
    public const SEVERITY_INFO = 'info';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_IMPORTANT = 'important';

    public const SEVERITY_URGENT = 'urgent';

    public const SEVERITY_BLOCKING = 'blocking';

    /** @use HasFactory<BookingRequestWarningFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_request_id',
        'warning_key',
        'severity',
        'message_key',
        'message_params_json',
        'blocking',
        'visible_to_host',
        'visible_to_guest',
    ];

    protected $attributes = [
        'severity' => self::SEVERITY_WARNING,
        'blocking' => false,
        'visible_to_host' => true,
        'visible_to_guest' => false,
    ];

    /**
     * Defines how Laravel converts stored warning attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'blocking' => 'boolean',
            'visible_to_host' => 'boolean',
            'visible_to_guest' => 'boolean',
        ];
    }

    /**
     * Links this warning to the Booking Request it explains.
     */
    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }
}
