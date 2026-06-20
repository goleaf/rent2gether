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

    protected function casts(): array
    {
        return [
            'context_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(HostBulkActionBatch::class, 'batch_id');
    }
}
