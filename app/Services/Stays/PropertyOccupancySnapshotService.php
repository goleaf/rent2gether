<?php

namespace App\Services\Stays;

use App\Models\BookingStay;
use App\Models\Property;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;

class PropertyOccupancySnapshotService
{
    public function getOrCreate(Property $property): PropertyCurrentOccupancySnapshot
    {
        return PropertyCurrentOccupancySnapshot::query()->firstOrCreate(
            ['property_id' => $property->id],
            [
                'host_user_id' => $property->host_user_id ?: $property->user_id,
                'last_recalculated_at' => now(),
            ],
        );
    }

    public function refresh(Property $property): PropertyCurrentOccupancySnapshot
    {
        $today = CarbonImmutable::today();
        $weekEnd = $today->endOfWeek();
        $activeStays = BookingStay::query()
            ->select([
                'id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'check_in_date',
                'planned_check_out_date',
                'has_open_complaint',
                'has_open_maintenance',
            ])
            ->where('property_id', $property->id)
            ->active()
            ->get();
        $occupiedPlaces = $activeStays->pluck('sleeping_place_id')->filter()->unique()->count();
        $placesCount = SleepingPlace::query()->where('property_id', $property->id)->count();

        return PropertyCurrentOccupancySnapshot::query()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'host_user_id' => $property->host_user_id ?: $property->user_id,
                'current_occupants_count' => $activeStays->count(),
                'current_bookings_count' => $activeStays->count(),
                'occupied_rooms_count' => $activeStays->pluck('room_id')->filter()->unique()->count(),
                'occupied_sleeping_places_count' => $occupiedPlaces,
                'free_sleeping_places_count' => max(0, $placesCount - $occupiedPlaces),
                'checkout_today_count' => $activeStays->filter(fn (BookingStay $stay): bool => $stay->planned_check_out_date?->isSameDay($today) === true)->count(),
                'checkin_today_count' => $activeStays->filter(fn (BookingStay $stay): bool => $stay->check_in_date?->isSameDay($today) === true)->count(),
                'checkout_this_week_count' => $activeStays->filter(fn (BookingStay $stay): bool => $stay->planned_check_out_date?->betweenIncluded($today, $weekEnd) === true)->count(),
                'has_open_complaints' => $activeStays->contains('has_open_complaint', true),
                'has_open_maintenance' => $activeStays->contains('has_open_maintenance', true),
                'has_cleaning_needed' => $activeStays->contains('checkout_soon', true),
                'has_inspection_needed' => $activeStays->contains('has_open_complaint', true),
                'last_recalculated_at' => now(),
            ],
        )->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function getForHost(Property $property): array
    {
        return $this->getOrCreate($property)->only([
            'current_occupants_count',
            'current_bookings_count',
            'occupied_rooms_count',
            'occupied_sleeping_places_count',
            'free_sleeping_places_count',
            'checkout_today_count',
            'checkout_this_week_count',
            'has_open_complaints',
            'has_open_maintenance',
        ]);
    }
}
