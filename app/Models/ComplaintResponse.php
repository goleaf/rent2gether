<?php

namespace App\Models;

use Database\Factories\ComplaintResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintResponse extends Model
{
    /** @use HasFactory<ComplaintResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'complaint_case_id',
        'user_id',
        'response_type',
        'message',
        'accepts_problem',
        'denies_problem',
        'offered_resolution_type',
        'offered_amount',
        'currency',
        'requires_guest_response',
        'requires_host_response',
    ];

    protected function casts(): array
    {
        return [
            'accepts_problem' => 'boolean',
            'denies_problem' => 'boolean',
            'offered_amount' => 'decimal:2',
            'requires_guest_response' => 'boolean',
            'requires_host_response' => 'boolean',
        ];
    }

    public function complaintCase(): BelongsTo
    {
        return $this->belongsTo(ComplaintCase::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
