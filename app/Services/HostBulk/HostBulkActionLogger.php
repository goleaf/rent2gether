<?php

namespace App\Services\HostBulk;

use App\Models\HostBulkActionBatch;
use App\Models\HostBulkActionLog;
use App\Models\User;

class HostBulkActionLogger
{
    public function log(User $host, ?HostBulkActionBatch $batch, string $message, array $context = [], ?string $targetType = null, ?int $targetId = null): HostBulkActionLog
    {
        return HostBulkActionLog::query()->create([
            'user_id' => $host->id,
            'batch_id' => $batch?->id,
            'action_type' => $batch?->action_type ?? ($context['action_type'] ?? 'manual'),
            'target_type' => $targetType ?? $batch?->target_type ?? ($context['target_type'] ?? 'mixed'),
            'target_id' => $targetId,
            'message' => $message,
            'context_json' => $context,
        ]);
    }
}
