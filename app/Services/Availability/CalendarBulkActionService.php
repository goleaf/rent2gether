<?php

namespace App\Services\Availability;

use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CalendarBulkActionService
{
    public function __construct(
        private readonly SleepingPlaceCalendarDayService $days,
        private readonly SleepingPlaceCalendarBlockService $blocks,
    ) {}

    public function bulkOpenDates(User $host, SleepingPlace $place, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->days->bulkSetDayStatus($host, $place, $from, $to, 'available', [
            'reason_key' => 'host_opened',
            'blocked_by_host' => false,
            'check_in_allowed' => true,
            'check_out_allowed' => true,
        ]);
    }

    public function bulkCloseDates(User $host, SleepingPlace $place, CarbonInterface $from, CarbonInterface $to, string $reason): Collection
    {
        return $this->days->bulkSetDayStatus($host, $place, $from, $to, 'closed_by_host', [
            'reason_key' => $reason,
        ]);
    }

    public function bulkSetRequestOnly(User $host, SleepingPlace $place, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->days->bulkSetDayStatus($host, $place, $from, $to, 'request_only', [
            'reason_key' => 'request_only',
            'check_in_allowed' => true,
            'check_out_allowed' => true,
        ]);
    }

    public function bulkSetPrice(User $host, SleepingPlace $place, CarbonInterface $from, CarbonInterface $to, float|int|string $price, ?string $currency = null): Collection
    {
        $changed = collect();

        foreach ($this->dateRange($from, $to) as $date) {
            if ($place->bookingDateLocks()->whereDate('date', $date->toDateString())->where('status', 'active')->exists()) {
                continue;
            }

            $changed->push($this->days->setPriceOverride($host, $place, $date, $price, $currency));
        }

        return $changed;
    }

    public function bulkMarkRepair(User $host, SleepingPlace $place, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $days = $this->days->bulkSetDayStatus($host, $place, $from, $to, 'repair', [
            'reason_key' => 'repair',
        ]);

        $this->blocks->createRepairBlock($place, [
            'starts_at' => $from,
            'ends_at' => $to,
            'created_by_user_id' => $host->id,
        ]);

        return $days;
    }

    public function bulkMarkCleaning(User $host, SleepingPlace $place, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $days = $this->days->bulkSetDayStatus($host, $place, $from, $to, 'cleaning', [
            'reason_key' => 'cleaning',
        ]);

        $this->blocks->createCleaningBlock($place, [
            'starts_at' => $from,
            'ends_at' => $to,
            'created_by_user_id' => $host->id,
        ]);

        return $days;
    }

    /**
     * @return list<CarbonImmutable>
     */
    private function dateRange(CarbonInterface $from, CarbonInterface $to): array
    {
        $dates = [];
        $cursor = CarbonImmutable::instance($from)->startOfDay();
        $end = CarbonImmutable::instance($to)->startOfDay();

        while ($cursor->lessThan($end)) {
            $dates[] = $cursor;
            $cursor = $cursor->addDay();
        }

        return $dates;
    }
}
