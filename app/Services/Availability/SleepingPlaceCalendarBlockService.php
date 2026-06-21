<?php

namespace App\Services\Availability;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarBlock;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class SleepingPlaceCalendarBlockService
{
    public function __construct(
        private readonly SleepingPlaceAvailabilityLogService $logs,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createBlock(User $host, SleepingPlace $place, array $data): SleepingPlaceCalendarBlock
    {
        $this->authorizeHost($host, $place);

        return $this->createTypedBlock($place, [
            ...$data,
            'created_by_user_id' => $host->id,
            'source_type' => $data['source_type'] ?? 'host',
            'block_type' => $data['block_type'] ?? 'closed_by_host',
            'reason_key' => $data['reason_key'] ?? $data['block_type'] ?? 'closed_by_host',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRepairBlock(SleepingPlace $place, array $data): SleepingPlaceCalendarBlock
    {
        return $this->createTypedBlock($place, [
            ...$data,
            'block_type' => 'repair',
            'reason_key' => $data['reason_key'] ?? 'repair',
            'source_type' => $data['source_type'] ?? 'repair',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCleaningBlock(SleepingPlace $place, array $data): SleepingPlaceCalendarBlock
    {
        return $this->createTypedBlock($place, [
            ...$data,
            'block_type' => 'cleaning',
            'reason_key' => $data['reason_key'] ?? 'cleaning',
            'source_type' => $data['source_type'] ?? 'cleaning',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createComplaintBlock(SleepingPlace $place, array $data): SleepingPlaceCalendarBlock
    {
        return $this->createTypedBlock($place, [
            ...$data,
            'block_type' => 'complaint',
            'reason_key' => $data['reason_key'] ?? 'unavailable_complaint',
            'source_type' => $data['source_type'] ?? 'complaint',
        ]);
    }

    public function releaseBlock(User $host, SleepingPlaceCalendarBlock $block): SleepingPlaceCalendarBlock
    {
        $block->loadMissing('sleepingPlace.property:id,host_user_id');
        $this->authorizeHost($host, $block->sleepingPlace);

        $block->update([
            'status' => 'released',
            'released_at' => now(),
        ]);

        $this->logs->record($block->sleepingPlace, null, $this->statusForBlock($block->block_type), 'available', 'calendar_block', $block->id, $host);

        return $block->refresh();
    }

    /**
     * @return Collection<int, SleepingPlaceCalendarBlock>
     */
    public function getActiveBlocks(SleepingPlace $place, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $start = $this->date($from);
        $end = $this->date($to);

        return $place->calendarBlocks()
            ->where('status', 'active')
            ->where('starts_at', '<', $end->endOfDay())
            ->where('ends_at', '>', $start->startOfDay())
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createTypedBlock(SleepingPlace $place, array $data): SleepingPlaceCalendarBlock
    {
        $start = $this->date($data['starts_at'] ?? $data['check_in_date'] ?? now());
        $end = $this->date($data['ends_at'] ?? $data['check_out_date'] ?? $start->addDay());

        if ($end->lessThanOrEqualTo($start)) {
            $end = $start->addDay();
        }

        $block = SleepingPlaceCalendarBlock::query()->create([
            'sleeping_place_id' => $place->id,
            'room_id' => $data['room_id'] ?? $place->room_id,
            'property_id' => $data['property_id'] ?? $place->property_id,
            'booking_id' => $data['booking_id'] ?? null,
            'source_type' => $data['source_type'] ?? 'host',
            'source_id' => $data['source_id'] ?? null,
            'block_type' => $data['block_type'],
            'status' => $data['status'] ?? 'active',
            'starts_at' => $start,
            'ends_at' => $end,
            'check_in_date' => $data['check_in_date'] ?? $start->toDateString(),
            'check_out_date' => $data['check_out_date'] ?? $end->toDateString(),
            'reason_key' => $data['reason_key'] ?? $data['block_type'],
            'visible_to_guest' => $data['visible_to_guest'] ?? false,
            'created_by_user_id' => $data['created_by_user_id'] ?? null,
        ]);

        $this->logs->record($place, null, null, $this->statusForBlock($block->block_type), 'calendar_block', $block->id);

        return $block;
    }

    private function statusForBlock(string $blockType): string
    {
        return match ($blockType) {
            'payment_pending' => 'payment_pending',
            'host_confirmation_pending' => 'host_confirmation_pending',
            'checked_in' => 'guest_checked_in',
            'closed_by_service_future' => 'closed_by_service_future',
            'closed_by_host' => 'closed_by_host',
            'breakdown' => 'unavailable_breakdown',
            'complaint' => 'unavailable_complaint',
            'hidden' => 'temporarily_hidden',
            'request_only' => 'request_only',
            'cleaning', 'repair' => $blockType,
            default => 'booked',
        };
    }

    private function authorizeHost(User $host, SleepingPlace $place): void
    {
        $place->loadMissing('property:id,host_user_id');

        if ((int) $place->user_id !== (int) $host->id && (int) $place->property?->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('availability.messages.not_owner'));
        }
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)
            : CarbonImmutable::parse($date);
    }
}
