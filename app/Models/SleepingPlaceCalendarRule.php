<?php

namespace App\Models;

use Database\Factories\SleepingPlaceCalendarRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceCalendarRule extends Model
{
    /** @use HasFactory<SleepingPlaceCalendarRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'rule_type',
        'starts_at',
        'ends_at',
        'weekdays_json',
        'status',
        'price',
        'min_nights',
        'max_nights',
        'check_in_allowed',
        'check_out_allowed',
        'priority',
    ];

    protected $attributes = [
        'priority' => 0,
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Calendar Rule attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'weekdays_json' => 'array',
            'price' => 'decimal:2',
            'check_in_allowed' => 'boolean',
            'check_out_allowed' => 'boolean',
            'priority' => 'integer',
        ];
    }

    /**
     * Links this Sleeping Place Calendar Rule to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
