<?php

namespace App\Models;

use Database\Factories\CleaningStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningStatusLog extends Model
{
    /** @use HasFactory<CleaningStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'cleaning_task_id',
        'user_id',
        'old_status',
        'new_status',
        'reason_key',
        'note',
        'context_json',
    ];

    /**
     * Defines how stored cleaning status log attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this status log to its cleaning task.
     */
    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }

    /**
     * Links this status log to the user who triggered it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
