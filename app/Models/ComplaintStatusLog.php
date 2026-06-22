<?php

namespace App\Models;

use Database\Factories\ComplaintStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintStatusLog extends Model
{
    /** @use HasFactory<ComplaintStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'complaint_case_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    protected function casts(): array
    {
        return [
            'context_json' => 'array',
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
