<?php

namespace App\Models;

use Database\Factories\HostBulkActionBatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostBulkActionBatch extends Model
{
    /** @use HasFactory<HostBulkActionBatchFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_type',
        'target_type',
        'status',
        'selected_count',
        'affected_count',
        'skipped_count',
        'failed_count',
        'payload_json',
        'preview_json',
        'result_json',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $attributes = [
        'status' => 'draft',
        'selected_count' => 0,
        'affected_count' => 0,
        'skipped_count' => 0,
        'failed_count' => 0,
    ];

    /**
     * Defines how Laravel converts stored Host Bulk Action Batch attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'preview_json' => 'array',
            'result_json' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Links this Host Bulk Action Batch to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lists related Host Bulk Action Item records for this Host Bulk Action Batch.
     */
    public function items(): HasMany
    {
        return $this->hasMany(HostBulkActionItem::class, 'batch_id');
    }

    /**
     * Lists related Host Bulk Action Log records for this Host Bulk Action Batch.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(HostBulkActionLog::class, 'batch_id');
    }
}
