<?php

namespace App\Models;

use Database\Factories\InspectionEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionEvent extends Model
{
    /** @use HasFactory<InspectionEventFactory> */
    use HasFactory;

    protected $fillable = [
        'inspection_task_id',
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
     * Defines how stored inspection event attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'context_json' => 'array',
        ];
    }

    /**
     * Links this event to its inspection task.
     */
    public function inspectionTask(): BelongsTo
    {
        return $this->belongsTo(InspectionTask::class);
    }

    /**
     * Links this event to the user who caused it when one exists.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
