<?php

namespace App\Models;

use Database\Factories\SleepingPlaceCancellationPolicyRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceCancellationPolicyRule extends Model
{
    /** @use HasFactory<SleepingPlaceCancellationPolicyRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_cancellation_policy_id',
        'rule_key',
        'applies_when',
        'refund_percent',
        'fixed_penalty_amount',
        'currency',
        'description',
        'sort_order',
    ];

    /**
     * Defines how Laravel converts stored cancellation-rule attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'refund_percent' => 'decimal:2',
            'fixed_penalty_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Links this rule to the parent cancellation policy.
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(SleepingPlaceCancellationPolicy::class, 'sleeping_place_cancellation_policy_id');
    }
}
