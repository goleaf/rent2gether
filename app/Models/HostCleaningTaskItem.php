<?php

namespace App\Models;

use Database\Factories\HostCleaningTaskItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCleaningTaskItem extends Model
{
    /** @use HasFactory<HostCleaningTaskItemFactory> */
    use HasFactory;

    protected $fillable = [
        'host_cleaning_task_id',
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
        'required' => false,
        'sort_order' => 0,
    ];

    /**
     * Defines how Laravel converts stored Host Cleaning Task Item attributes into PHP values.
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
     * Links this Host Cleaning Task Item to the Host Cleaning Task record used by its task relation.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(HostCleaningTask::class, 'host_cleaning_task_id');
    }

    /**
     * Links this Host Cleaning Task Item to the User record used by its completed by relation.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
