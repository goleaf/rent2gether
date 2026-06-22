<?php

namespace App\Models;

use Database\Factories\InspectionStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionStatusLog extends Model
{
    /** @use HasFactory<InspectionStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'inspection_task_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how stored inspection status log attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log to its inspection task.
     */
    public function inspectionTask(): BelongsTo
    {
        return $this->belongsTo(InspectionTask::class);
    }

    /**
     * Links this status log to the user who triggered it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
