<?php

namespace App\Models;

use Database\Factories\SleepingPlaceCalendarDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceCalendarDay extends Model
{
    /** @use HasFactory<SleepingPlaceCalendarDayFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'date',
        'status',
        'price',
        'price_override',
        'currency',
        'min_nights',
        'max_nights',
        'check_in_allowed',
        'check_out_allowed',
        'reason',
        'reason_key',
        'source',
        'source_type',
        'source_id',
        'note',
        'booking_id',
        'blocked_by_host',
    ];

    protected $attributes = [
        'status' => 'available',
        'check_in_allowed' => true,
        'check_out_allowed' => true,
        'blocked_by_host' => false,
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Calendar Day attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'price' => 'decimal:2',
            'price_override' => 'decimal:2',
            'check_in_allowed' => 'boolean',
            'check_out_allowed' => 'boolean',
            'blocked_by_host' => 'boolean',
        ];
    }

    /**
     * Links this Sleeping Place Calendar Day to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this Sleeping Place Calendar Day to the Booking record used by its booking relation.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
