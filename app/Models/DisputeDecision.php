<?php

namespace App\Models;

use Database\Factories\DisputeDecisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeDecision extends Model
{
    /** @use HasFactory<DisputeDecisionFactory> */
    use HasFactory;

    protected $fillable = [
        'dispute_case_id',
        'decision_type',
        'resolution_type',
        'amount_to_guest',
        'amount_to_host',
        'deposit_return_amount',
        'deposit_deduction_amount',
        'host_payout_adjustment_amount',
        'currency',
        'reason_summary',
        'decision_note',
        'decided_by_type',
        'decided_by_user_id',
        'decided_at',
        'status',
    ];

    protected $attributes = [
        'amount_to_guest' => 0,
        'amount_to_host' => 0,
        'deposit_return_amount' => 0,
        'deposit_deduction_amount' => 0,
        'host_payout_adjustment_amount' => 0,
        'status' => 'draft',
    ];

    protected function casts(): array
    {
        return [
            'amount_to_guest' => 'decimal:2',
            'amount_to_host' => 'decimal:2',
            'deposit_return_amount' => 'decimal:2',
            'deposit_deduction_amount' => 'decimal:2',
            'host_payout_adjustment_amount' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    public function disputeCase(): BelongsTo
    {
        return $this->belongsTo(DisputeCase::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
