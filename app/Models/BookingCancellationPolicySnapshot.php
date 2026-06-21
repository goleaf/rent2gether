<?php

namespace App\Models;

use Database\Factories\BookingCancellationPolicySnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCancellationPolicySnapshot extends Model
{
    /** @use HasFactory<BookingCancellationPolicySnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'sleeping_place_id',
        'policy_type',
        'title_snapshot',
        'description_snapshot',
        'rules_snapshot_json',
        'free_cancellation_until',
        'cancellation_penalty_starts_at',
        'first_night_non_refundable',
        'cleaning_fee_refundable_before_check_in',
        'service_fee_refundable',
        'deposit_always_refundable_before_check_in',
    ];

    /**
     * Defines how Laravel converts stored snapshot attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'rules_snapshot_json' => 'array',
            'free_cancellation_until' => 'datetime',
            'cancellation_penalty_starts_at' => 'datetime',
            'first_night_non_refundable' => 'boolean',
            'cleaning_fee_refundable_before_check_in' => 'boolean',
            'service_fee_refundable' => 'boolean',
            'deposit_always_refundable_before_check_in' => 'boolean',
        ];
    }

    /**
     * Links this cancellation-policy snapshot to the Booking that owns it.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this snapshot to the SleepingPlace policy copied at booking time.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
