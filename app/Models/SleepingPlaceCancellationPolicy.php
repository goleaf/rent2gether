<?php

namespace App\Models;

use Database\Factories\SleepingPlaceCancellationPolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SleepingPlaceCancellationPolicy extends Model
{
    /** @use HasFactory<SleepingPlaceCancellationPolicyFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'policy_type',
        'title',
        'description',
        'free_cancellation_until_days_before_check_in',
        'free_cancellation_until_hours_before_check_in',
        'penalty_starts_hours_before_check_in',
        'first_night_non_refundable',
        'cleaning_fee_refundable_before_check_in',
        'service_fee_refundable',
        'deposit_always_refundable_before_check_in',
        'active',
    ];

    /**
     * Defines how Laravel converts stored cancellation-policy attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'free_cancellation_until_days_before_check_in' => 'integer',
            'free_cancellation_until_hours_before_check_in' => 'integer',
            'penalty_starts_hours_before_check_in' => 'integer',
            'first_night_non_refundable' => 'boolean',
            'cleaning_fee_refundable_before_check_in' => 'boolean',
            'service_fee_refundable' => 'boolean',
            'deposit_always_refundable_before_check_in' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Links this policy to the SleepingPlace it protects.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists detailed refund rules for this policy.
     */
    public function rules(): HasMany
    {
        return $this->hasMany(SleepingPlaceCancellationPolicyRule::class);
    }
}
