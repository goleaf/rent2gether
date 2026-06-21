<?php

namespace App\Services\Stays;

use App\Models\BookingStay;
use App\Models\Room;
use App\Models\RoomCurrentOccupancySnapshot;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;

class RoomOccupancySnapshotService
{
    public function getOrCreate(Room $room): RoomCurrentOccupancySnapshot
    {
        $room = $this->roomWithContext($room);

        return RoomCurrentOccupancySnapshot::query()->firstOrCreate(
            ['room_id' => $room->id],
            [
                'property_id' => $room->property_id,
                'host_user_id' => $room->property?->host_user_id ?: $room->user_id,
                'last_recalculated_at' => now(),
            ],
        );
    }

    public function refresh(Room $room): RoomCurrentOccupancySnapshot
    {
        $room = $this->roomWithContext($room);
        $today = CarbonImmutable::today();
        $weekEnd = $today->endOfWeek();
        $activeStays = BookingStay::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'sleeping_place_id',
                'status',
                'check_in_date',
                'planned_check_out_date',
                'has_open_complaint',
                'has_open_maintenance',
            ])
            ->where('room_id', $room->id)
            ->active()
            ->with('occupants:id,booking_stay_id,gender,stay_purpose,sleep_schedule,smoking_status,sociability_level')
            ->get();
        $occupants = $activeStays->flatMap->occupants;
        $occupiedPlaces = $activeStays->pluck('sleeping_place_id')->filter()->unique()->count();
        $placesCount = SleepingPlace::query()->where('room_id', $room->id)->count();

        return RoomCurrentOccupancySnapshot::query()->updateOrCreate(
            ['room_id' => $room->id],
            [
                'property_id' => $room->property_id,
                'host_user_id' => $room->property?->host_user_id ?: $room->user_id,
                'current_occupants_count' => $occupants->count(),
                'current_bookings_count' => $activeStays->count(),
                'occupied_sleeping_places_count' => $occupiedPlaces,
                'free_sleeping_places_count' => max(0, $placesCount - $occupiedPlaces),
                'male_occupants_count' => $occupants->where('gender', 'male')->count(),
                'female_occupants_count' => $occupants->where('gender', 'female')->count(),
                'unknown_gender_occupants_count' => $occupants->filter(fn ($occupant): bool => blank($occupant->gender))->count(),
                'students_count' => $occupants->where('stay_purpose', 'student')->count(),
                'workers_count' => $occupants->where('stay_purpose', 'work')->count(),
                'tourists_count' => $occupants->where('stay_purpose', 'tourist')->count(),
                'long_term_residents_count' => $occupants->where('stay_purpose', 'long_term_resident')->count(),
                'short_term_guests_count' => $occupants->where('stay_purpose', 'short_term_guest')->count(),
                'early_wakeup_count' => $occupants->where('sleep_schedule', 'wakes_up_early')->count(),
                'late_sleep_count' => $occupants->where('sleep_schedule', 'sleeps_late')->count(),
                'night_work_count' => $occupants->where('sleep_schedule', 'works_at_night')->count(),
                'smokers_count' => $occupants->where('smoking_status', 'smokes')->count(),
                'non_smokers_count' => $occupants->where('smoking_status', 'does_not_smoke')->count(),
                'quiet_preferring_count' => $occupants->where('sociability_level', 'prefers_quiet')->count(),
                'social_count' => $occupants->where('sociability_level', 'social')->count(),
                'checkout_today_count' => $activeStays->filter(fn (BookingStay $stay): bool => $stay->planned_check_out_date?->isSameDay($today) === true)->count(),
                'checkin_today_count' => $activeStays->filter(fn (BookingStay $stay): bool => $stay->check_in_date?->isSameDay($today) === true)->count(),
                'checkout_this_week_count' => $activeStays->filter(fn (BookingStay $stay): bool => $stay->planned_check_out_date?->betweenIncluded($today, $weekEnd) === true)->count(),
                'has_open_complaints' => $activeStays->contains('has_open_complaint', true),
                'has_open_maintenance' => $activeStays->contains('has_open_maintenance', true),
                'has_noise_warning' => $occupants->contains('sleep_schedule', 'works_at_night') || $occupants->contains('sleep_schedule', 'sleeps_late'),
                'has_cleanliness_warning' => false,
                'last_recalculated_at' => now(),
            ],
        )->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function getForListing(Room $room): array
    {
        $snapshot = $this->getOrCreate($room);

        return [
            'current_occupants_count' => $snapshot->current_occupants_count,
            'free_sleeping_places_count' => $snapshot->free_sleeping_places_count,
            'has_open_complaints' => $snapshot->has_open_complaints,
            'has_noise_warning' => $snapshot->has_noise_warning,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getForHost(Room $room): array
    {
        return $this->getOrCreate($room)->only([
            'current_occupants_count',
            'current_bookings_count',
            'occupied_sleeping_places_count',
            'free_sleeping_places_count',
            'checkout_today_count',
            'checkout_this_week_count',
            'has_open_complaints',
            'has_open_maintenance',
        ]);
    }

    private function roomWithContext(Room $room): Room
    {
        if (! $room->property_id || ! $room->relationLoaded('property')) {
            return Room::query()
                ->select(['id', 'property_id', 'user_id'])
                ->with('property:id,host_user_id,user_id')
                ->findOrFail($room->id);
        }

        $room->loadMissing('property:id,host_user_id,user_id');

        return $room;
    }
}
