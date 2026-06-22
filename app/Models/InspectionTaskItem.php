<?php

namespace App\Models;

use Database\Factories\InspectionTaskItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionTaskItem extends Model
{
    /** @use HasFactory<InspectionTaskItemFactory> */
    use HasFactory;

    protected $fillable = [
        'inspection_task_id',
        'item_key',
        'label_key',
        'status',
        'required',
        'sort_order',
        'completed_by_user_id',
        'completed_at',
        'result_value',
        'note',
    ];

    protected $attributes = [
        'status' => 'pending',
        'required' => true,
        'sort_order' => 0,
    ];

    /**
     * Defines how stored inspection checklist item attributes become PHP values.
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
     * Links this checklist item to its inspection task.
     */
    public function inspectionTask(): BelongsTo
    {
        return $this->belongsTo(InspectionTask::class);
    }

    /**
     * Links this checklist item to the user who completed it.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
