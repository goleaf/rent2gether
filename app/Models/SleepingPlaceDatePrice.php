<?php

namespace App\Models;

use Database\Factories\SleepingPlaceDatePriceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceDatePrice extends Model
{
    public const TYPE_MANUAL_OVERRIDE = 'manual_override';

    public const TYPE_HOLIDAY = 'holiday';

    public const TYPE_WEEKEND_OVERRIDE = 'weekend_override';

    public const TYPE_SEASONAL = 'seasonal';

    public const TYPE_SPECIAL_EVENT = 'special_event';

    /** @use HasFactory<SleepingPlaceDatePriceFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'date',
        'price',
        'currency',
        'price_type',
        'min_nights',
        'max_nights',
        'check_in_allowed',
        'check_out_allowed',
        'note',
    ];

    /**
     * Defines how Laravel converts stored date price attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'price' => 'decimal:2',
            'min_nights' => 'integer',
            'max_nights' => 'integer',
            'check_in_allowed' => 'boolean',
            'check_out_allowed' => 'boolean',
        ];
    }

    /**
     * Links this special date price to the Sleeping Place it overrides.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
