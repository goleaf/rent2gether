<?php

namespace App\Models;

use Database\Factories\HostHintActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostHintAction extends Model
{
    /** @use HasFactory<HostHintActionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'host_hint_snapshot_id',
        'action',
        'action_status',
        'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(HostHintSnapshot::class, 'host_hint_snapshot_id');
    }
}
