<?php

namespace App\Models;

use Database\Factories\HostBulkActionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostBulkActionLog extends Model
{
    /** @use HasFactory<HostBulkActionLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'batch_id',
        'action_type',
        'target_type',
        'target_id',
        'message',
        'context_json',
    ];

    /**
     * Defines how Laravel converts stored Host Bulk Action Log attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    /**
     * Links this Host Bulk Action Log to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Host Bulk Action Log to the Host Bulk Action Batch record used by its batch relation.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(HostBulkActionBatch::class, 'batch_id');
    }
}
