<?php

namespace App\Services\Availability;

use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class SleepingPlaceCalendarStatusService
{
    public function resolveDateStatus(SleepingPlace $place, CarbonInterface $date): string
    {
        $date = $this->date($date);
        $statuses = collect();

        $place->calendarBlocks()
            ->where('status', 'active')
            ->where('starts_at', '<', $date->endOfDay())
            ->where('ends_at', '>', $date->startOfDay())
            ->get(['block_type'])
            ->each(fn ($block): mixed => $statuses->push($this->statusForBlock($block->block_type)));

        $place->bookingDateLocks()
            ->whereDate('date', $date->toDateString())
            ->where('status', 'active')
            ->get(['lock_type'])
            ->each(fn ($lock): mixed => $statuses->push($this->statusForLock($lock->lock_type)));

        $calendarDay = $place->calendarDays()
            ->whereDate('date', $date->toDateString())
            ->first(['status']);

        if ($calendarDay) {
            $statuses->push($this->normalizeStatus($calendarDay->status));
        }

        $place->loadMissing('calendarSettings');

        if ($place->calendarSettings?->active === false) {
            $statuses->push('temporarily_hidden');
        }

        if ($place->calendarSettings?->request_only || $place->calendarSettings?->booking_mode === 'request_only') {
            $statuses->push('request_only');
        }

        if ($place->calendarSettings?->booking_mode === 'hidden') {
            $statuses->push('temporarily_hidden');
        }

        if ($statuses->isEmpty()) {
            return $this->normalizeStatus($place->calendarSettings?->default_status ?? 'available');
        }

        return $statuses
            ->map(fn (string $status): string => $this->normalizeStatus($status))
            ->sortBy(fn (string $status): int => $this->getStatusPriority($status))
            ->first();
    }

    public function resolveRangeStatus(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): string
    {
        $statuses = collect();

        foreach ($this->dateRange($checkIn, $checkOut) as $date) {
            $statuses->push($this->resolveDateStatus($place, $date));
        }

        if ($statuses->isEmpty()) {
            return 'available';
        }

        return $statuses
            ->sortBy(fn (string $status): int => $this->getStatusPriority($status))
            ->first();
    }

    public function getPublicStatus(SleepingPlace $place, CarbonInterface $date): string
    {
        return match ($this->resolveDateStatus($place, $date)) {
            'unavailable_complaint',
            'closed_by_service_future',
            'unavailable_breakdown',
            'temporarily_hidden',
            'closed_by_host' => 'unavailable',
            'payment_pending',
            'host_confirmation_pending',
            'booked',
            'guest_checked_in',
            'occupied' => 'occupied',
            default => $this->resolveDateStatus($place, $date),
        };
    }

    public function getStatusPriority(string $status): int
    {
        return match ($this->normalizeStatus($status)) {
            'repair' => 1,
            'unavailable_breakdown' => 2,
            'unavailable_complaint' => 3,
            'closed_by_service_future' => 4,
            'closed_by_host' => 5,
            'booked', 'guest_checked_in', 'occupied' => 6,
            'payment_pending' => 7,
            'host_confirmation_pending' => 8,
            'cleaning' => 9,
            'temporarily_hidden' => 10,
            'request_only' => 11,
            'available' => 12,
            default => 50,
        };
    }

    private function statusForLock(string $lockType): string
    {
        return match ($lockType) {
            'payment_pending' => 'payment_pending',
            'host_confirmation_pending' => 'host_confirmation_pending',
            'checked_in' => 'guest_checked_in',
            'manual_hold' => 'closed_by_host',
            'extension_pending' => 'payment_pending',
            'relocation_pending' => 'host_confirmation_pending',
            default => 'booked',
        };
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

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'pending_payment' => 'payment_pending',
            'pending_approval' => 'host_confirmation_pending',
            'blocked_by_host', 'blocked' => 'closed_by_host',
            'maintenance' => 'repair',
            default => $status,
        };
    }

    private function date(CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::instance($date)->startOfDay();
    }

    /**
     * @return list<CarbonImmutable>
     */
    private function dateRange(CarbonInterface $checkIn, CarbonInterface $checkOut): array
    {
        $dates = [];
        $cursor = $this->date($checkIn);
        $end = $this->date($checkOut);

        while ($cursor->lessThan($end)) {
            $dates[] = $cursor;
            $cursor = $cursor->addDay();
        }

        return $dates;
    }
}
