<?php

namespace App\Livewire\Host\Bulk;

use App\Models\Booking;
use App\Models\HostBulkActionBatch;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostBulk\HostBulkActionService;
use App\Services\HostBulk\HostBulkCloneService;
use App\Services\HostBulk\HostBulkPermissionService;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

abstract class BaseHostBulkComponent extends Component
{
    private const TARGET_OPTION_LIMIT = 30;

    public string $section = 'panel';

    public ?int $propertyId = null;

    public ?int $roomId = null;

    public string $targetType = 'sleeping_place';

    /** @var list<int|string> */
    public array $selectedTargetIds = [];

    public string $actionType = 'change_price';

    public ?string $rangeStart = null;

    public ?string $rangeEnd = null;

    public ?string $price = null;

    public string $currency = 'EUR';

    public string $discountType = 'weekly';

    public ?string $discountPercent = null;

    public string $rulesText = '';

    public string $checkInTimeFrom = '14:00';

    public string $checkInTimeUntil = '22:00';

    public string $checkOutTimeUntil = '11:00';

    public ?string $cleaningFee = null;

    public string $occupiedReason = 'host_bulk_occupied';

    public string $message = '';

    public ?string $cleaningScheduledDate = null;

    public ?string $cleaningScheduledTime = '12:00';

    public string $cleaningReason = 'host_bulk';

    public string $cleaningNote = '';

    public ?int $cloneRoomId = null;

    public ?int $cloneSleepingPlaceId = null;

    public bool $copyPhotos = false;

    public bool $copyPrice = true;

    public bool $copyCalendar = false;

    public ?int $identicalRoomId = null;

    public int $identicalCount = 2;

    public string $identicalName = '';

    public ?string $identicalPrice = null;

    public string $identicalCurrency = 'EUR';

    public int $identicalMinNights = 1;

    public int $identicalMaxGuests = 1;

    public string $identicalType = 'single';

    #[Locked]
    public ?int $lastBatchId = null;

    public string $bulkSummaryMode = '';

    public int $resultSelectedCount = 0;

    public int $resultAffectedCount = 0;

    public int $resultSkippedCount = 0;

    public int $resultFailedCount = 0;

    public string $noticeKey = '';

    public function mount(): void
    {
        $this->cleaningScheduledDate ??= now()->toDateString();
        $this->identicalName = $this->identicalName ?: __('host_bulk.defaults.sleeping_place');
    }

    public function updatedPropertyId(): void
    {
        $this->roomId = null;
        $this->selectedTargetIds = [];
        $this->cloneRoomId = null;
        $this->cloneSleepingPlaceId = null;
        $this->identicalRoomId = null;
    }

    public function updatedRoomId(): void
    {
        $this->selectedTargetIds = [];
        $this->cloneSleepingPlaceId = null;
    }

    public function updatedActionType(): void
    {
        $this->targetType = $this->defaultTargetTypeForAction($this->actionType);
        $this->selectedTargetIds = [];
        $this->clearBulkSummary();
    }

    public function updatedTargetType(): void
    {
        $this->selectedTargetIds = [];
        $this->clearPreviewSummary();
    }

    public function selectVisibleTargets(): void
    {
        $this->selectedTargetIds = collect($this->targetOptions)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    public function clearTargets(): void
    {
        $this->selectedTargetIds = [];
        $this->clearPreviewSummary();
    }

    public function previewBulkAction(HostBulkActionService $service): void
    {
        $this->validate($this->bulkRules());

        $batch = $service->createBatch(
            $this->host(),
            $this->actionType,
            $this->targets(),
            $this->payloadForAction(),
        );

        $service->preview($batch);

        $this->lastBatchId = $batch->id;
        $this->bulkSummaryMode = 'preview';
        $this->resetManualResultCounts();
        $this->noticeKey = 'host_bulk.messages.preview_before_apply';
    }

    public function applyBulkAction(HostBulkActionService $service): void
    {
        $this->validate($this->bulkRules());

        $batch = $service->createBatch(
            $this->host(),
            $this->actionType,
            $this->targets(),
            $this->payloadForAction(),
        );

        $processed = $service->process($service->confirm($batch));

        $this->lastBatchId = $processed->id;
        $this->bulkSummaryMode = 'preview_result';
        $this->resetManualResultCounts();
        $this->noticeKey = 'host_bulk.messages.completed';
    }

    public function cloneRoom(HostBulkCloneService $cloneService, HostBulkPermissionService $permissions): void
    {
        $this->validate([
            'cloneRoomId' => ['required', 'integer'],
            'copyPhotos' => ['boolean'],
        ]);

        $room = $permissions->ensureHostOwnsTarget($this->host(), 'room', (int) $this->cloneRoomId);
        $clone = $cloneService->cloneRoom($this->roomModel($room), ['copy_photos' => $this->copyPhotos]);

        $this->showManualResult($this->singleResult($clone->id));
        $this->noticeKey = 'host_bulk.messages.clone_room_completed';
    }

    public function cloneSleepingPlace(HostBulkCloneService $cloneService, HostBulkPermissionService $permissions): void
    {
        $this->validate([
            'cloneSleepingPlaceId' => ['required', 'integer'],
            'copyPhotos' => ['boolean'],
            'copyPrice' => ['boolean'],
            'copyCalendar' => ['boolean'],
        ]);

        $place = $permissions->ensureHostOwnsTarget($this->host(), 'sleeping_place', (int) $this->cloneSleepingPlaceId);
        $clone = $cloneService->cloneSleepingPlace($this->sleepingPlaceModel($place), [
            'copy_photos' => $this->copyPhotos,
            'copy_price' => $this->copyPrice,
            'copy_calendar' => $this->copyCalendar,
        ]);

        $this->showManualResult($this->singleResult($clone->id));
        $this->noticeKey = 'host_bulk.messages.clone_sleeping_place_completed';
    }

    public function createIdenticalPlaces(HostBulkCloneService $cloneService, HostBulkPermissionService $permissions): void
    {
        $this->validate([
            'identicalRoomId' => ['required', 'integer'],
            'identicalCount' => ['required', 'integer', 'min:1', 'max:25'],
            'identicalName' => ['required', 'string', 'max:80'],
            'identicalPrice' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'identicalCurrency' => ['required', 'string', 'size:3'],
            'identicalMinNights' => ['required', 'integer', 'min:1', 'max:365'],
            'identicalMaxGuests' => ['required', 'integer', 'min:1', 'max:10'],
            'identicalType' => ['required', 'string', 'max:40'],
        ]);

        $room = $permissions->ensureHostOwnsTarget($this->host(), 'room', (int) $this->identicalRoomId);
        $created = $cloneService->createIdenticalPlaces($this->roomModel($room), $this->identicalCount, [
            'display_name' => trim($this->identicalName),
            'base_price_per_night' => $this->identicalPrice ?? 0,
            'currency' => strtoupper($this->identicalCurrency),
            'min_nights' => $this->identicalMinNights,
            'max_guests' => $this->identicalMaxGuests,
            'type' => $this->identicalType,
        ]);

        $this->showManualResult([
            'selected_count' => $this->identicalCount,
            'affected_count' => $created->count(),
            'skipped_count' => max(0, $this->identicalCount - $created->count()),
            'failed_count' => 0,
        ]);
        $this->noticeKey = 'host_bulk.messages.create_identical_places_completed';
    }

    /**
     * @return list<array{id:int,label:string,meta:string}>
     */
    #[Computed]
    public function properties(): array
    {
        return Property::query()
            ->select(['id', 'title', 'city', 'publication_status', 'status', 'updated_at'])
            ->forHost($this->host()->id)
            ->orderByDesc('updated_at')
            ->limit(self::TARGET_OPTION_LIMIT)
            ->get()
            ->map(fn (Property $property): array => [
                'id' => $property->id,
                'label' => $property->title,
                'meta' => $this->meta([$property->city, $property->publication_status ?: $this->displayValue($property->status)]),
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,label:string,meta:string}>
     */
    #[Computed]
    public function rooms(): array
    {
        return Room::query()
            ->select(['id', 'property_id', 'user_id', 'title', 'room_number', 'status', 'updated_at'])
            ->with(['property:id,title,host_user_id,user_id'])
            ->withCount('sleepingPlaces')
            ->forHost($this->host()->id)
            ->when($this->propertyId, fn ($query) => $query->forProperty((int) $this->propertyId))
            ->orderByDesc('updated_at')
            ->limit(self::TARGET_OPTION_LIMIT)
            ->get()
            ->map(fn (Room $room): array => [
                'id' => $room->id,
                'label' => $room->title ?: __('host_bulk.defaults.room_copy').' #'.$room->id,
                'meta' => $this->meta([$room->property?->title, $room->room_number, __('host_bulk.messages.places_count', ['count' => $room->sleeping_places_count])]),
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,label:string,meta:string}>
     */
    #[Computed]
    public function sleepingPlaces(): array
    {
        return SleepingPlace::query()
            ->select(['id', 'property_id', 'room_id', 'user_id', 'display_name', 'place_number', 'base_price_per_night', 'currency', 'status', 'publication_status', 'updated_at'])
            ->with(['room:id,title,room_number,property_id', 'property:id,title,host_user_id,user_id'])
            ->forHost($this->host()->id)
            ->when($this->propertyId, fn ($query) => $query->forProperty((int) $this->propertyId))
            ->when($this->roomId, fn ($query) => $query->forRoom((int) $this->roomId))
            ->orderByDesc('updated_at')
            ->limit(self::TARGET_OPTION_LIMIT)
            ->get()
            ->map(fn (SleepingPlace $place): array => [
                'id' => $place->id,
                'label' => $place->display_name ?: __('host_bulk.defaults.sleeping_place').' #'.$place->id,
                'meta' => $this->meta([
                    $place->room?->title,
                    $place->publication_status,
                    $this->moneyMeta($place->base_price_per_night, $place->currency),
                ]),
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,label:string,meta:string}>
     */
    #[Computed]
    public function bookings(): array
    {
        return Booking::query()
            ->select(['id', 'booking_number', 'guest_user_id', 'host_user_id', 'property_id', 'room_id', 'sleeping_place_id', 'check_in_date', 'check_out_date', 'status', 'updated_at'])
            ->with(['guest:id,name', 'sleepingPlace:id,display_name,place_number'])
            ->forHost($this->host()->id)
            ->when($this->propertyId, fn ($query) => $query->where('property_id', (int) $this->propertyId))
            ->when($this->roomId, fn ($query) => $query->where('room_id', (int) $this->roomId))
            ->orderByDesc('updated_at')
            ->limit(self::TARGET_OPTION_LIMIT)
            ->get()
            ->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'label' => $booking->booking_number ?: __('host_bulk.defaults.booking').' #'.$booking->id,
                'meta' => $this->meta([
                    $booking->guest?->name,
                    $booking->sleepingPlace?->display_name,
                    $booking->check_in_date?->toDateString().' - '.$booking->check_out_date?->toDateString(),
                ]),
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,label:string,meta:string}>
     */
    #[Computed]
    public function targetOptions(): array
    {
        return match ($this->targetType) {
            'property' => $this->properties,
            'room' => $this->rooms,
            'booking' => $this->bookings,
            default => $this->sleepingPlaces,
        };
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function actionOptions(): array
    {
        return collect($this->actionTypes())
            ->mapWithKeys(fn (string $action): array => [$action => __('host_bulk.actions.'.$action)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function targetTypeOptions(): array
    {
        return collect($this->allowedTargetTypesForAction())
            ->mapWithKeys(fn (string $type): array => [$type => __('host_bulk.targets.'.$type)])
            ->all();
    }

    public function render(): View
    {
        $summaryBatch = $this->summaryBatch();

        return view('livewire.host.bulk.shell', [
            'section' => $this->section,
            'preview' => $this->previewSummary($summaryBatch),
            'result' => $this->resultSummary($summaryBatch),
        ])->layout('layouts.app', [
            'title' => __('host_bulk.title'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function bulkRules(): array
    {
        $rules = [
            'actionType' => ['required', Rule::in($this->actionTypes())],
            'targetType' => ['required', Rule::in($this->allowedTargetTypesForAction())],
            'selectedTargetIds' => ['required', 'array', 'min:1'],
            'selectedTargetIds.*' => ['integer', 'min:1'],
        ];

        return match ($this->actionType) {
            'change_price' => [
                ...$rules,
                'price' => ['required', 'numeric', 'min:0', 'max:999999'],
                'currency' => ['required', 'string', 'size:3'],
                'rangeStart' => ['nullable', 'date', 'required_with:rangeEnd'],
                'rangeEnd' => ['nullable', 'date', 'after:rangeStart', 'required_with:rangeStart'],
            ],
            'open_dates' => [
                ...$rules,
                'rangeStart' => ['required', 'date'],
                'rangeEnd' => ['required', 'date', 'after:rangeStart'],
                'price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
                'currency' => ['required', 'string', 'size:3'],
            ],
            'close_dates', 'mark_occupied' => [
                ...$rules,
                'rangeStart' => ['required', 'date'],
                'rangeEnd' => ['required', 'date', 'after:rangeStart'],
                'occupiedReason' => ['nullable', 'string', 'max:80'],
            ],
            'add_discount' => [
                ...$rules,
                'discountType' => ['required', Rule::in(['weekly', 'monthly'])],
                'discountPercent' => ['required', 'numeric', 'min:0', 'max:90'],
            ],
            'change_rules' => [
                ...$rules,
                'rulesText' => ['required', 'string', 'max:4000'],
            ],
            'change_check_in_time' => [
                ...$rules,
                'checkInTimeFrom' => ['required', 'date_format:H:i'],
                'checkInTimeUntil' => ['nullable', 'date_format:H:i'],
                'checkOutTimeUntil' => ['required', 'date_format:H:i'],
            ],
            'change_cleaning_fee' => [
                ...$rules,
                'cleaningFee' => ['required', 'numeric', 'min:0', 'max:999999'],
            ],
            'message_guests' => [
                ...$rules,
                'message' => ['required', 'string', 'min:1', 'max:2000'],
            ],
            'assign_cleaning' => [
                ...$rules,
                'cleaningScheduledDate' => ['required', 'date'],
                'cleaningScheduledTime' => ['nullable', 'date_format:H:i'],
                'cleaningReason' => ['required', 'string', 'max:80'],
                'cleaningNote' => ['nullable', 'string', 'max:1000'],
            ],
            default => $rules,
        };
    }

    /**
     * @return list<string>
     */
    private function actionTypes(): array
    {
        return [
            'change_price',
            'open_dates',
            'close_dates',
            'add_discount',
            'change_rules',
            'change_check_in_time',
            'change_cleaning_fee',
            'mark_occupied',
            'message_guests',
            'assign_cleaning',
            'hide_listings',
            'activate_listings',
        ];
    }

    /**
     * @return list<string>
     */
    private function allowedTargetTypesForAction(): array
    {
        return match ($this->actionType) {
            'message_guests' => ['booking'],
            'change_rules', 'assign_cleaning' => ['property', 'room', 'sleeping_place'],
            default => ['sleeping_place'],
        };
    }

    private function defaultTargetTypeForAction(string $actionType): string
    {
        return match ($actionType) {
            'message_guests' => 'booking',
            default => 'sleeping_place',
        };
    }

    /**
     * @return list<array{type:string,id:int}>
     */
    private function targets(): array
    {
        return collect($this->selectedTargetIds)
            ->map(fn (int|string $id): array => [
                'type' => $this->targetType,
                'id' => (int) $id,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadForAction(): array
    {
        return match ($this->actionType) {
            'change_price' => [
                'target_type' => 'sleeping_place',
                'price' => (float) $this->price,
                'currency' => strtoupper($this->currency),
                'range' => $this->optionalRange(),
            ],
            'open_dates' => array_filter([
                'target_type' => 'sleeping_place',
                'range' => $this->requiredRange(),
                'price' => $this->price === null || $this->price === '' ? null : (float) $this->price,
                'currency' => strtoupper($this->currency),
            ], fn (mixed $value): bool => $value !== null),
            'close_dates' => [
                'target_type' => 'sleeping_place',
                'range' => $this->requiredRange(),
                'reason' => $this->occupiedReason ?: 'host_blocked',
            ],
            'mark_occupied' => [
                'target_type' => 'sleeping_place',
                'range' => $this->requiredRange(),
                'reason' => $this->occupiedReason ?: 'occupied',
            ],
            'add_discount' => [
                'target_type' => 'sleeping_place',
                'discount_type' => $this->discountType,
                'percent' => (float) $this->discountPercent,
            ],
            'change_rules' => [
                'target_type' => $this->targetType,
                'rules' => $this->normalizedRules(),
            ],
            'change_check_in_time' => [
                'target_type' => 'sleeping_place',
                'check_in_time_from' => $this->checkInTimeFrom,
                'check_in_time_until' => $this->checkInTimeUntil,
                'check_out_time_until' => $this->checkOutTimeUntil,
            ],
            'change_cleaning_fee' => [
                'target_type' => 'sleeping_place',
                'fee' => (float) $this->cleaningFee,
            ],
            'message_guests' => [
                'target_type' => 'booking',
                'message' => trim($this->message),
            ],
            'assign_cleaning' => [
                'target_type' => $this->targetType,
                'scheduled_date' => $this->cleaningScheduledDate,
                'scheduled_time' => $this->cleaningScheduledTime,
                'reason' => $this->cleaningReason,
                'note' => trim($this->cleaningNote),
            ],
            default => [
                'target_type' => 'sleeping_place',
            ],
        };
    }

    /**
     * @return array{start:string,end:string}|null
     */
    private function optionalRange(): ?array
    {
        if (! $this->rangeStart || ! $this->rangeEnd) {
            return null;
        }

        return $this->requiredRange();
    }

    /**
     * @return array{start:string,end:string}
     */
    private function requiredRange(): array
    {
        if (! $this->rangeStart || ! $this->rangeEnd) {
            throw ValidationException::withMessages([
                'rangeStart' => __('validation.required', ['attribute' => __('host_bulk.fields.range_start')]),
            ]);
        }

        return [
            'start' => $this->rangeStart,
            'end' => $this->rangeEnd,
        ];
    }

    /**
     * @return list<string>
     */
    private function normalizedRules(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $this->rulesText) ?: [])
            ->map(fn (string $rule): string => trim($rule))
            ->filter()
            ->values()
            ->all();
    }

    private function host(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function roomModel(Model $model): Room
    {
        abort_unless($model instanceof Room, 403);

        return $model;
    }

    private function sleepingPlaceModel(Model $model): SleepingPlace
    {
        abort_unless($model instanceof SleepingPlace, 403);

        return $model;
    }

    /**
     * @param  list<mixed>  $parts
     */
    private function meta(array $parts): string
    {
        return collect($parts)
            ->map(fn (mixed $part): string => trim((string) $part))
            ->filter()
            ->implode(' · ');
    }

    private function moneyMeta(int|float|string|null $amount, ?string $currency): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        return strtoupper((string) ($currency ?: 'EUR')).' '.number_format((float) $amount, 2, '.', '');
    }

    private function displayValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return (string) $value;
    }

    private function clearBulkSummary(): void
    {
        $this->lastBatchId = null;
        $this->bulkSummaryMode = '';
        $this->resetManualResultCounts();
    }

    private function clearPreviewSummary(): void
    {
        if ($this->bulkSummaryMode === 'preview') {
            $this->clearBulkSummary();

            return;
        }

        if ($this->bulkSummaryMode === 'preview_result') {
            $this->bulkSummaryMode = 'result';
        }
    }

    private function resetManualResultCounts(): void
    {
        $this->resultSelectedCount = 0;
        $this->resultAffectedCount = 0;
        $this->resultSkippedCount = 0;
        $this->resultFailedCount = 0;
    }

    /**
     * @param  array<string, int>  $summary
     */
    private function showManualResult(array $summary): void
    {
        $this->lastBatchId = null;
        $this->bulkSummaryMode = 'result';
        $this->resultSelectedCount = (int) ($summary['selected_count'] ?? 0);
        $this->resultAffectedCount = (int) ($summary['affected_count'] ?? 0);
        $this->resultSkippedCount = (int) ($summary['skipped_count'] ?? 0);
        $this->resultFailedCount = (int) ($summary['failed_count'] ?? 0);
    }

    private function summaryBatch(): ?HostBulkActionBatch
    {
        if ($this->lastBatchId === null || $this->bulkSummaryMode === '') {
            return null;
        }

        return HostBulkActionBatch::query()
            ->select([
                'id',
                'user_id',
                'selected_count',
                'affected_count',
                'skipped_count',
                'failed_count',
                'preview_json',
                'result_json',
            ])
            ->whereKey($this->lastBatchId)
            ->where('user_id', $this->host()->id)
            ->first();
    }

    /**
     * @return array<string, int>
     */
    private function previewSummary(?HostBulkActionBatch $batch): array
    {
        if (! in_array($this->bulkSummaryMode, ['preview', 'preview_result'], true) || $batch === null) {
            return [];
        }

        return $batch->preview_json ?? $this->summaryFromBatch($batch);
    }

    /**
     * @return array<string, int>
     */
    private function resultSummary(?HostBulkActionBatch $batch): array
    {
        if (! in_array($this->bulkSummaryMode, ['result', 'preview_result'], true)) {
            return [];
        }

        if ($batch instanceof HostBulkActionBatch) {
            return $batch->result_json ?? $this->summaryFromBatch($batch);
        }

        if (($this->resultSelectedCount + $this->resultAffectedCount + $this->resultSkippedCount + $this->resultFailedCount) === 0) {
            return [];
        }

        return [
            'selected_count' => $this->resultSelectedCount,
            'affected_count' => $this->resultAffectedCount,
            'skipped_count' => $this->resultSkippedCount,
            'failed_count' => $this->resultFailedCount,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function summaryFromBatch(HostBulkActionBatch $batch): array
    {
        return [
            'selected_count' => (int) $batch->selected_count,
            'affected_count' => (int) $batch->affected_count,
            'skipped_count' => (int) $batch->skipped_count,
            'failed_count' => (int) $batch->failed_count,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function singleResult(int $targetId): array
    {
        return [
            'target_id' => $targetId,
            'selected_count' => 1,
            'affected_count' => 1,
            'skipped_count' => 0,
            'failed_count' => 0,
        ];
    }
}
