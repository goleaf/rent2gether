<?php

namespace App\Models;

use Database\Factories\CleaningTaskItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningTaskItem extends Model
{
    /** @use HasFactory<CleaningTaskItemFactory> */
    use HasFactory;

    protected $fillable = [
        'cleaning_task_id',
        'item_key',
        'label_key',
        'status',
        'required',
        'sort_order',
        'completed_by_user_id',
        'completed_at',
        'note',
    ];

    protected $attributes = [
        'status' => 'pending',
        'required' => true,
        'sort_order' => 0,
    ];

    /**
     * Defines how stored cleaning checklist item attributes become PHP values.
     */
    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'sort_order' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Links this checklist item to its cleaning task.
     */
    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }

    /**
     * Links this checklist item to the user who completed it.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
