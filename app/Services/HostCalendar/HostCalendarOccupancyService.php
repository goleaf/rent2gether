<?php

namespace App\Services\HostCalendar;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostCalendar\Data\HostCalendarOccupancyData;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class HostCalendarOccupancyService
{
    public function getPropertyOccupancy(Property $property, array $range): HostCalendarOccupancyData
    {
        $total = $property->sleepingPlaces()->count();
        $occupied = $this->occupiedCount(['property_id' => $property->id], $range);

        return new HostCalendarOccupancyData($occupied, $total, max(0, $total - $occupied), $this->getOccupancyPercent($occupied, $total));
    }

    public function getRoomOccupancy(Room $room, array $range): HostCalendarOccupancyData
    {
        $total = $room->sleepingPlaces()->count();
        $occupied = $this->occupiedCount(['room_id' => $room->id], $range);

        return new HostCalendarOccupancyData($occupied, $total, max(0, $total - $occupied), $this->getOccupancyPercent($occupied, $total));
    }

    public function getSleepingPlaceOccupancy(SleepingPlace $place, array $range): HostCalendarOccupancyData
    {
        $occupied = $this->occupiedCount(['sleeping_place_id' => $place->id], $range) > 0 ? 1 : 0;

        return new HostCalendarOccupancyData($occupied, 1, $occupied === 1 ? 0 : 1, $this->getOccupancyPercent($occupied, 1));
    }

    public function getOccupancyPercent(int $occupied, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($occupied / $total) * 100);
    }

    public function getDailyOccupancy(User $host, array $range): Collection
    {
        return collect(CarbonPeriod::create($this->date($range['start']), $this->date($range['end'])->subDay()))
            ->map(function (CarbonInterface $date) use ($host): array {
                $dailyRange = ['start' => $date->toDateString(), 'end' => $date->addDay()->toDateString()];
                $total = SleepingPlace::query()
                    ->whereHas('property', fn ($property) => $property->where('host_user_id', $host->id))
                    ->count();
                $occupied = $this->occupiedCount(['host_user_id' => $host->id], $dailyRange);

                return [
                    'date' => $date->toDateString(),
                    'occupied' => $occupied,
                    'total' => $total,
                    'percent' => $this->getOccupancyPercent($occupied, $total),
                ];
            });
    }

    private function occupiedCount(array $scope, array $range): int
    {
        return Booking::query()
            ->whereNotIn('status', $this->cancelledStatuses())
            ->whereDate('check_in_date', '<', $this->date($range['end'])->toDateString())
            ->whereDate('check_out_date', '>', $this->date($range['start'])->toDateString())
            ->when(isset($scope['host_user_id']), fn ($query) => $query->where('host_user_id', $scope['host_user_id']))
            ->when(isset($scope['property_id']), fn ($query) => $query->where('property_id', $scope['property_id']))
            ->when(isset($scope['room_id']), fn ($query) => $query->where('room_id', $scope['room_id']))
            ->when(isset($scope['sleeping_place_id']), fn ($query) => $query->where('sleeping_place_id', $scope['sleeping_place_id']))
            ->distinct()
            ->count('sleeping_place_id');
    }

    /**
     * @return list<string>
     */
    private function cancelledStatuses(): array
    {
        return [
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::CancelledByGuest->value,
            BookingStatus::CancelledByHost->value,
            BookingStatus::CancelledBySystem->value,
            BookingStatus::CancelledByService->value,
            BookingStatus::DeclinedByHost->value,
            BookingStatus::Expired->value,
            BookingStatus::NoShow->value,
        ];
    }

    private function date(string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date)->startOfDay();
    }
}
