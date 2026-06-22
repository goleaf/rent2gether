<?php

namespace App\Models;

use Database\Factories\CleaningEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningEvent extends Model
{
    /** @use HasFactory<CleaningEventFactory> */
    use HasFactory;

    protected $fillable = [
        'cleaning_task_id',
        'event_key',
        'event_type',
        'source_type',
        'source_id',
        'user_id',
        'occurred_at',
        'context_json',
    ];

    protected $attributes = [
        'event_type' => 'system',
    ];

    /**
     * Defines how stored cleaning event attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this event to its cleaning task.
     */
    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }

    /**
     * Links this event to the user who caused it when one exists.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
