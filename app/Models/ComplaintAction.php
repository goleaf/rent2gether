<?php

namespace App\Models;

use Database\Factories\ComplaintActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintAction extends Model
{
    /** @use HasFactory<ComplaintActionFactory> */
    use HasFactory;

    protected $fillable = [
        'complaint_case_id',
        'action_type',
        'status',
        'source_type',
        'source_id',
        'assigned_to_user_id',
        'due_at',
        'created_by_user_id',
        'completed_at',
        'note',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function complaintCase(): BelongsTo
    {
        return $this->belongsTo(ComplaintCase::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
