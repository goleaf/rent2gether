<?php

namespace App\Models;

use Database\Factories\DisputeResolutionProposalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeResolutionProposal extends Model
{
    /** @use HasFactory<DisputeResolutionProposalFactory> */
    use HasFactory;

    protected $fillable = [
        'dispute_case_id',
        'proposed_by_user_id',
        'resolution_type',
        'amount',
        'currency',
        'description',
        'guest_accepts',
        'host_accepts',
        'guest_accepted_at',
        'host_accepted_at',
        'status',
    ];

    protected $attributes = [
        'status' => 'offered',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'guest_accepts' => 'boolean',
            'host_accepts' => 'boolean',
            'guest_accepted_at' => 'datetime',
            'host_accepted_at' => 'datetime',
        ];
    }

    public function disputeCase(): BelongsTo
    {
        return $this->belongsTo(DisputeCase::class);
    }

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by_user_id');
    }
}
