<?php

namespace App\Models;

use Database\Factories\SleepingPlaceTurnoverRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceTurnoverRule extends Model
{
    /** @use HasFactory<SleepingPlaceTurnoverRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'min_gap_minutes',
        'cleaning_required_between_guests',
        'cleaning_gap_minutes',
        'inspection_required_after_checkout',
        'inspection_gap_minutes',
        'same_day_turnover_allowed',
        'morning_checkout_evening_checkin_allowed',
        'same_day_turnover_requires_cleaning_done',
        'same_day_turnover_requires_inspection_done',
        'earliest_new_check_in_time',
        'latest_previous_check_out_time',
    ];

    protected $attributes = [
        'min_gap_minutes' => 0,
        'cleaning_required_between_guests' => true,
        'cleaning_gap_minutes' => 0,
        'inspection_required_after_checkout' => false,
        'inspection_gap_minutes' => 0,
        'same_day_turnover_allowed' => false,
        'morning_checkout_evening_checkin_allowed' => true,
        'same_day_turnover_requires_cleaning_done' => true,
        'same_day_turnover_requires_inspection_done' => false,
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Turnover Rule attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'min_gap_minutes' => 'integer',
            'cleaning_required_between_guests' => 'boolean',
            'cleaning_gap_minutes' => 'integer',
            'inspection_required_after_checkout' => 'boolean',
            'inspection_gap_minutes' => 'integer',
            'same_day_turnover_allowed' => 'boolean',
            'morning_checkout_evening_checkin_allowed' => 'boolean',
            'same_day_turnover_requires_cleaning_done' => 'boolean',
            'same_day_turnover_requires_inspection_done' => 'boolean',
        ];
    }

    /**
     * Links this turnover rule to the Sleeping Place it configures.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
