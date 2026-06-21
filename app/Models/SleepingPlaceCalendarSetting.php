<?php

namespace App\Models;

use Database\Factories\SleepingPlaceCalendarSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceCalendarSetting extends Model
{
    /** @use HasFactory<SleepingPlaceCalendarSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'default_status',
        'default_price',
        'currency',
        'min_nights',
        'max_nights',
        'booking_mode',
        'weekly_discount_percent',
        'monthly_discount_percent',
        'cleaning_gap_hours',
        'cleaning_gap_days',
        'instant_booking_enabled',
        'requires_host_approval',
        'requires_payment_before_block',
        'requires_host_confirmation',
        'request_only',
        'can_extend',
        'same_day_check_in_allowed',
        'same_day_turnover_allowed',
        'default_check_in_time',
        'default_check_out_time',
        'earliest_check_in_time',
        'latest_check_out_time',
        'check_in_time_from',
        'check_in_time_until',
        'check_out_time_until',
        'check_in_weekdays_json',
        'check_out_weekdays_json',
        'active',
    ];

    protected $attributes = [
        'default_status' => 'available',
        'booking_mode' => 'instant',
        'cleaning_gap_hours' => 0,
        'cleaning_gap_days' => 0,
        'instant_booking_enabled' => false,
        'requires_host_approval' => true,
        'requires_payment_before_block' => true,
        'requires_host_confirmation' => false,
        'request_only' => false,
        'can_extend' => true,
        'same_day_check_in_allowed' => true,
        'same_day_turnover_allowed' => false,
        'active' => true,
    ];

    /**
     * Defines how Laravel converts stored Sleeping Place Calendar Setting attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'weekly_discount_percent' => 'decimal:2',
            'monthly_discount_percent' => 'decimal:2',
            'cleaning_gap_hours' => 'integer',
            'cleaning_gap_days' => 'integer',
            'instant_booking_enabled' => 'boolean',
            'requires_host_approval' => 'boolean',
            'requires_payment_before_block' => 'boolean',
            'requires_host_confirmation' => 'boolean',
            'request_only' => 'boolean',
            'can_extend' => 'boolean',
            'same_day_check_in_allowed' => 'boolean',
            'same_day_turnover_allowed' => 'boolean',
            'check_in_weekdays_json' => 'array',
            'check_out_weekdays_json' => 'array',
            'active' => 'boolean',
        ];
    }

    /**
     * Links this Sleeping Place Calendar Setting to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
