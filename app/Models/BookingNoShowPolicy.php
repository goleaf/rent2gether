<?php

namespace App\Models;

use Database\Factories\BookingNoShowPolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingNoShowPolicy extends Model
{
    /** @use HasFactory<BookingNoShowPolicyFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'waiting_period_minutes',
        'same_day_waiting_period_minutes',
        'night_arrival_waiting_period_minutes',
        'hold_first_night_on_no_show',
        'release_remaining_nights_after_no_show',
        'refund_deposit_on_no_show',
        'refund_cleaning_fee_on_no_show',
        'refund_service_fee_on_no_show',
        'host_payout_rule',
        'guest_penalty_rule',
        'active',
    ];

    protected $attributes = [
        'waiting_period_minutes' => 180,
        'hold_first_night_on_no_show' => true,
        'release_remaining_nights_after_no_show' => true,
        'refund_deposit_on_no_show' => true,
        'refund_cleaning_fee_on_no_show' => true,
        'refund_service_fee_on_no_show' => false,
        'host_payout_rule' => 'policy_based',
        'guest_penalty_rule' => 'policy_based',
        'active' => true,
    ];

    /**
     * Defines how stored no-show policy values are converted for PHP use.
     */
    protected function casts(): array
    {
        return [
            'waiting_period_minutes' => 'integer',
            'same_day_waiting_period_minutes' => 'integer',
            'night_arrival_waiting_period_minutes' => 'integer',
            'hold_first_night_on_no_show' => 'boolean',
            'release_remaining_nights_after_no_show' => 'boolean',
            'refund_deposit_on_no_show' => 'boolean',
            'refund_cleaning_fee_on_no_show' => 'boolean',
            'refund_service_fee_on_no_show' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Links this no-show policy to the sleeping place that owns it.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists immutable booking snapshots created from this policy's values.
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(BookingNoShowPolicySnapshot::class, 'sleeping_place_id', 'sleeping_place_id');
    }
}
