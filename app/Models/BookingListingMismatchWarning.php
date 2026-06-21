<?php

namespace App\Models;

use Database\Factories\BookingListingMismatchWarningFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingListingMismatchWarning extends Model
{
    /** @use HasFactory<BookingListingMismatchWarningFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_listing_mismatch_report_id',
        'warning_key',
        'severity',
        'message_key',
        'message_params_json',
        'visible_to_guest',
        'visible_to_host',
        'blocking',
    ];

    protected $attributes = [
        'severity' => 'warning',
        'visible_to_guest' => true,
        'visible_to_host' => true,
        'blocking' => false,
    ];

    /**
     * Defines how Laravel converts stored warning attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'visible_to_guest' => 'boolean',
            'visible_to_host' => 'boolean',
            'blocking' => 'boolean',
        ];
    }

    /**
     * Links this warning to the parent mismatch report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(BookingListingMismatchReport::class, 'booking_listing_mismatch_report_id');
    }
}
