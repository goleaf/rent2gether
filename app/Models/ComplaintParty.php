<?php

namespace App\Models;

use Database\Factories\ComplaintPartyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintParty extends Model
{
    /** @use HasFactory<ComplaintPartyFactory> */
    use HasFactory;

    protected $fillable = [
        'complaint_case_id',
        'user_id',
        'party_type',
        'display_name_snapshot',
        'role_in_case',
        'can_respond',
        'notified_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'can_respond' => 'boolean',
            'notified_at' => 'datetime',
            'responded_at' => 'datetime',
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
