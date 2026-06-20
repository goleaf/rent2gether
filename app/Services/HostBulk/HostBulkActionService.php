<?php

namespace App\Services\HostBulk;

use App\Models\Booking;
use App\Models\HostBulkActionBatch;
use App\Models\HostBulkActionItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class HostBulkActionService
{
    public function __construct(
        private readonly HostBulkPermissionService $permissions,
        private readonly HostBulkPreviewService $previewer,
        private readonly HostBulkActionLogger $logger,
        private readonly HostBulkPricingService $pricing,
        private readonly HostBulkCalendarService $calendar,
        private readonly HostBulkRulesService $rules,
        private readonly HostBulkMessageService $messages,
        private readonly HostBulkCleaningService $cleaning,
        private readonly HostBulkPublicationService $publication,
    ) {}

    public function createBatch(User $host, string $actionType, array $targets, array $payload): HostBulkActionBatch
    {
        $normalizedTargets = $this->normalizeTargets($targets, $payload['target_type'] ?? null);
        $targetType = $normalizedTargets[0]['type'] ?? ($payload['target_type'] ?? 'sleeping_place');

        return DB::transaction(function () use ($host, $actionType, $payload, $normalizedTargets, $targetType): HostBulkActionBatch {
            foreach ($normalizedTargets as $target) {
                $this->permissions->ensureHostOwnsTarget($host, $target['type'], $target['id']);
            }

            $batch = HostBulkActionBatch::query()->create([
                'user_id' => $host->id,
                'action_type' => $actionType,
                'target_type' => $targetType,
                'status' => 'draft',
                'selected_count' => count($normalizedTargets),
                'payload_json' => $payload,
            ]);

            foreach ($normalizedTargets as $target) {
                $batch->items()->create([
                    'target_type' => $target['type'],
                    'target_id' => $target['id'],
                    'status' => 'pending',
                ]);
            }

            $this->logger->log($host, $batch, 'host_bulk.log.created', ['selected_count' => count($normalizedTargets)]);

            return $batch->refresh();
        });
    }

    public function preview(HostBulkActionBatch $batch): array
    {
        $preview = $this->previewer->preview($batch);

        $batch->forceFill([
            'status' => 'previewed',
            'affected_count' => $preview['affected_count'],
            'skipped_count' => $preview['skipped_count'],
            'failed_count' => $preview['failed_count'],
            'preview_json' => $preview,
        ])->save();

        $this->logger->log($batch->user, $batch, 'host_bulk.log.previewed', $preview);

        return $preview;
    }

    public function confirm(HostBulkActionBatch $batch): HostBulkActionBatch
    {
        if (! in_array($batch->status, ['draft', 'previewed'], true)) {
            throw ValidationException::withMessages([
                'batch' => __('host_bulk.errors.cannot_confirm'),
            ]);
        }

        if ($batch->status === 'draft') {
            $this->preview($batch);
        }

        $batch->forceFill(['status' => 'confirmed'])->save();
        $this->logger->log($batch->user, $batch, 'host_bulk.log.confirmed');

        return $batch->refresh();
    }

    public function process(HostBulkActionBatch $batch): HostBulkActionBatch
    {
        $batch->loadMissing('items', 'user');

        if ($batch->status === 'draft') {
            $batch = $this->confirm($batch);
        }

        $batch->forceFill(['status' => 'processing', 'started_at' => now()])->save();

        $affected = 0;
        $failed = 0;
        $skipped = $batch->items()->where('status', 'skipped')->count();

        foreach ($batch->items()->where('status', 'pending')->cursor() as $item) {
            try {
                $model = $this->permissions->ensureHostOwnsTarget($batch->user, $item->target_type, $item->target_id);
                $before = $this->snapshot($model);
                $this->processItem($batch, $item, $model);
                $model = $this->permissions->ensureHostOwnsTarget($batch->user, $item->target_type, $item->target_id);
                $item->forceFill([
                    'status' => 'processed',
                    'before_json' => $before,
                    'after_json' => $this->snapshot($model),
                    'processed_at' => now(),
                ])->save();
                $affected++;
            } catch (Throwable $exception) {
                $failed++;
                $item->forceFill([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                    'processed_at' => now(),
                ])->save();
            }
        }

        $status = $failed > 0 && $affected === 0 ? 'failed' : ($failed > 0 ? 'completed_with_errors' : 'completed');
        $result = [
            'selected_count' => $batch->selected_count,
            'affected_count' => $affected,
            'skipped_count' => $skipped,
            'failed_count' => $failed,
        ];

        $batch->forceFill([
            'status' => $status,
            'affected_count' => $affected,
            'skipped_count' => $skipped,
            'failed_count' => $failed,
            'result_json' => $result,
            'completed_at' => now(),
        ])->save();

        $this->logger->log($batch->user, $batch, 'host_bulk.log.processed', $result);

        return $batch->refresh();
    }

    public function cancel(HostBulkActionBatch $batch): HostBulkActionBatch
    {
        $batch->forceFill(['status' => 'cancelled', 'cancelled_at' => now()])->save();
        $batch->items()->where('status', 'pending')->update(['status' => 'cancelled']);
        $this->logger->log($batch->user, $batch, 'host_bulk.log.cancelled');

        return $batch->refresh();
    }

    public function getResult(HostBulkActionBatch $batch): array
    {
        return $batch->result_json ?? [
            'selected_count' => $batch->selected_count,
            'affected_count' => $batch->affected_count,
            'skipped_count' => $batch->skipped_count,
            'failed_count' => $batch->failed_count,
        ];
    }

    private function processItem(HostBulkActionBatch $batch, HostBulkActionItem $item, Model $model): void
    {
        $payload = $batch->payload_json ?? [];

        match ($batch->action_type) {
            'change_price' => $this->pricing->setPrice(collect([$model]), $payload['price'], $payload['range'] ?? null, $payload['currency'] ?? 'EUR'),
            'open_dates' => $this->calendar->openDates(collect([$model]), $payload['range'], $payload),
            'close_dates' => $this->calendar->closeDates(collect([$model]), $payload['range'], $payload['reason'] ?? 'host_blocked'),
            'mark_occupied' => $this->calendar->markOccupied(collect([$model]), $payload['range'], $payload['reason'] ?? 'occupied'),
            'add_discount' => $this->applyDiscount($payload, collect([$model])),
            'change_rules' => $this->applyRules($item->target_type, collect([$model]), $payload),
            'message_guests' => $this->messages->sendToBookingGuests($batch->user, collect([$model]), $payload['message'] ?? ''),
            'assign_cleaning' => $this->cleaning->createCleaningTasks(collect([$model]), ['user_id' => $batch->user_id, ...$payload]),
            'hide_listings' => $this->publication->hideListings(collect([$model])),
            'activate_listings' => $this->publication->activateListings(collect([$model])),
            'pause_listings' => $this->publication->pauseListings(collect([$model])),
            'archive_listings' => $this->publication->archiveListings(collect([$model])),
            'publish_listings' => $this->publication->publishListings(collect([$model])),
            default => throw ValidationException::withMessages(['action_type' => __('host_bulk.errors.unknown_action')]),
        };
    }

    private function applyDiscount(array $payload, Collection $places): void
    {
        if (($payload['discount_type'] ?? 'weekly') === 'monthly') {
            $this->pricing->setMonthlyDiscount($places, (float) ($payload['percent'] ?? 0));

            return;
        }

        $this->pricing->setWeeklyDiscount($places, (float) ($payload['percent'] ?? 0));
    }

    private function applyRules(string $targetType, Collection $targets, array $payload): void
    {
        match ($targetType) {
            'property' => $this->rules->updateHouseRules($targets, $payload['rules'] ?? []),
            'room' => $this->rules->updateRoomRules($targets, $payload['rules'] ?? []),
            default => null,
        };
    }

    /**
     * @return list<array{type:string,id:int}>
     */
    private function normalizeTargets(array $targets, ?string $fallbackType): array
    {
        return collect($targets)
            ->map(function (mixed $target) use ($fallbackType): array {
                if (is_array($target)) {
                    return [
                        'type' => (string) ($target['type'] ?? $fallbackType ?? 'sleeping_place'),
                        'id' => (int) $target['id'],
                    ];
                }

                if ($target instanceof Model) {
                    return [
                        'type' => $this->targetTypeForModel($target),
                        'id' => (int) $target->getKey(),
                    ];
                }

                return [
                    'type' => (string) ($fallbackType ?? 'sleeping_place'),
                    'id' => (int) $target,
                ];
            })
            ->values()
            ->all();
    }

    private function targetTypeForModel(Model $model): string
    {
        return match (true) {
            $model instanceof Property => 'property',
            $model instanceof Room => 'room',
            $model instanceof SleepingPlace => 'sleeping_place',
            $model instanceof Booking => 'booking',
            default => 'unknown',
        };
    }

    private function snapshot(Model $model): array
    {
        return $model->fresh()?->attributesToArray() ?? $model->attributesToArray();
    }
}
