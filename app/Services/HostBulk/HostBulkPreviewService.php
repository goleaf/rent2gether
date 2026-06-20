<?php

namespace App\Services\HostBulk;

use App\Models\HostBulkActionBatch;
use App\Models\SleepingPlace;

class HostBulkPreviewService
{
    public function __construct(
        private readonly HostBulkPermissionService $permissions,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function preview(HostBulkActionBatch $batch): array
    {
        $batch->loadMissing('items');

        $selected = $batch->items->count();
        $skipped = 0;
        $skipReasons = [];
        $payload = $batch->payload_json ?? [];

        foreach ($batch->items as $item) {
            $reason = $this->skipReason($batch->action_type, $item->target_type, $item->target_id, $payload);

            if ($reason !== null) {
                $item->forceFill([
                    'status' => 'skipped',
                    'error_message' => $reason,
                ])->save();
                $skipped++;
                $skipReasons[] = [
                    'target_type' => $item->target_type,
                    'target_id' => $item->target_id,
                    'reason' => $reason,
                ];
            } elseif ($item->status === 'skipped') {
                $item->forceFill(['status' => 'pending', 'error_message' => null])->save();
            }
        }

        return [
            'selected_count' => $selected,
            'affected_count' => max(0, $selected - $skipped),
            'skipped_count' => $skipped,
            'failed_count' => 0,
            'dangerous' => in_array($batch->action_type, $this->dangerousActions(), true),
            'fields' => array_keys($payload),
            'skip_reasons' => $skipReasons,
            'message_key' => 'host_bulk.messages.preview_before_apply',
        ];
    }

    /**
     * @return list<string>
     */
    private function dangerousActions(): array
    {
        return [
            'change_price',
            'open_dates',
            'close_dates',
            'mark_occupied',
            'change_rules',
            'message_guests',
            'assign_cleaning',
            'hide_listings',
            'activate_listings',
            'publish_listings',
            'archive_listings',
        ];
    }

    private function skipReason(string $actionType, string $targetType, int $targetId, array $payload): ?string
    {
        if (! in_array($actionType, ['open_dates', 'mark_available'], true)) {
            return null;
        }

        if ($targetType !== 'sleeping_place' || empty($payload['range'])) {
            return null;
        }

        $place = SleepingPlace::query()->find($targetId);

        if (! $place instanceof SleepingPlace) {
            return 'target_missing';
        }

        return $this->permissions->hasActiveBookingConflict($place, $payload['range'])
            ? 'active_booking_conflict'
            : null;
    }
}
