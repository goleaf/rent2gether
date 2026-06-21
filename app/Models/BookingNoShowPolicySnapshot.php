<?php

namespace App\Models;

use Database\Factories\BookingNoShowPolicySnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNoShowPolicySnapshot extends Model
{
    /** @use HasFactory<BookingNoShowPolicySnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
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
        'policy_snapshot_json',
    ];

    /**
     * Defines how stored no-show snapshot values are converted for PHP use.
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
            'policy_snapshot_json' => 'array',
        ];
    }

    /**
     * Links this no-show policy snapshot to the booking that froze it.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this no-show policy snapshot to the sleeping place being booked.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
