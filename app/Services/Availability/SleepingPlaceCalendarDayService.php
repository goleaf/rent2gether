<?php

namespace App\Services\Availability;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class SleepingPlaceCalendarDayService
{
    public function __construct(
        private readonly SleepingPlaceAvailabilityLogService $logs,
    ) {}

    public function setDayStatus(User $host, SleepingPlace $place, CarbonInterface $date, string $status, array $data = []): SleepingPlaceCalendarDay
    {
        $this->authorizeHost($host, $place);

        $day = $this->upsertDay($place, $date, [
            'status' => $status,
            'reason' => $data['reason_key'] ?? $data['reason'] ?? $status,
            'reason_key' => $data['reason_key'] ?? $data['reason'] ?? $status,
            'source' => $data['source_type'] ?? $data['source'] ?? 'host',
            'source_type' => $data['source_type'] ?? $data['source'] ?? 'host',
            'source_id' => $data['source_id'] ?? null,
            'note' => $data['note'] ?? null,
            'blocked_by_host' => $status !== 'available',
            'check_in_allowed' => $data['check_in_allowed'] ?? $status === 'available',
            'check_out_allowed' => $data['check_out_allowed'] ?? $status === 'available',
        ]);

        $this->logs->record($place, $this->date($date), null, $status, 'calendar_day', $day->id, $host, $data['note'] ?? null);

        return $day;
    }

    /**
     * @return Collection<int, SleepingPlaceCalendarDay>
     */
    public function bulkSetDayStatus(User $host, SleepingPlace $place, CarbonInterface $from, CarbonInterface $to, string $status, array $data = []): Collection
    {
        $this->authorizeHost($host, $place);

        $days = collect();

        foreach ($this->dateRange($from, $to) as $date) {
            if ($this->hasActiveLock($place, $date)) {
                continue;
            }

            $days->push($this->setDayStatus($host, $place, $date, $status, $data));
        }

        return $days;
    }

    public function setCheckInAllowed(User $host, SleepingPlace $place, CarbonInterface $date, bool $allowed): SleepingPlaceCalendarDay
    {
        $this->authorizeHost($host, $place);

        return $this->upsertDay($place, $date, [
            'check_in_allowed' => $allowed,
        ]);
    }

    public function setCheckOutAllowed(User $host, SleepingPlace $place, CarbonInterface $date, bool $allowed): SleepingPlaceCalendarDay
    {
        $this->authorizeHost($host, $place);

        return $this->upsertDay($place, $date, [
            'check_out_allowed' => $allowed,
        ]);
    }

    public function setPriceOverride(User $host, SleepingPlace $place, CarbonInterface $date, float|int|string $price, ?string $currency = null): SleepingPlaceCalendarDay
    {
        $this->authorizeHost($host, $place);

        return $this->upsertDay($place, $date, [
            'price' => $price,
            'price_override' => $price,
            'currency' => $currency ?? $place->currency ?? 'EUR',
            'source' => 'host',
            'source_type' => 'host',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsertDay(SleepingPlace $place, CarbonInterface $date, array $attributes): SleepingPlaceCalendarDay
    {
        return SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $place->id,
                'date' => $this->date($date)->toDateString(),
            ],
            $attributes,
        );
    }

    private function hasActiveLock(SleepingPlace $place, CarbonInterface $date): bool
    {
        return $place->bookingDateLocks()
            ->whereDate('date', $this->date($date)->toDateString())
            ->where('status', 'active')
            ->exists();
    }

    private function authorizeHost(User $host, SleepingPlace $place): void
    {
        $place->loadMissing('property:id,host_user_id');

        if ((int) $place->user_id !== (int) $host->id && (int) $place->property?->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('availability.messages.not_owner'));
        }
    }

    private function date(CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::instance($date)->startOfDay();
    }

    /**
     * @return list<CarbonImmutable>
     */
    private function dateRange(CarbonInterface $from, CarbonInterface $to): array
    {
        $dates = [];
        $cursor = $this->date($from);
        $end = $this->date($to);

        while ($cursor->lessThan($end)) {
            $dates[] = $cursor;
            $cursor = $cursor->addDay();
        }

        return $dates;
    }
}
