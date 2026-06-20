<?php

namespace App\Models;

use Database\Factories\HostBulkActionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostBulkActionItem extends Model
{
    /** @use HasFactory<HostBulkActionItemFactory> */
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'target_type',
        'target_id',
        'status',
        'before_json',
        'after_json',
        'error_message',
        'processed_at',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'before_json' => 'array',
            'after_json' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(HostBulkActionBatch::class, 'batch_id');
    }
}
