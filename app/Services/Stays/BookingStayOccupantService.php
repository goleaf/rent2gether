<?php

namespace App\Services\Stays;

use App\Models\Booking;
use App\Models\BookingStay;
use App\Models\BookingStayOccupant;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Collection;

class BookingStayOccupantService
{
    public function __construct(
        private readonly StayVisibilityService $visibility,
    ) {}

    /**
     * @return Collection<int, BookingStayOccupant>
     */
    public function createOccupantsFromBooking(Booking $booking, BookingStay $stay): Collection
    {
        if ($stay->occupants()->exists()) {
            return $stay->occupants()->get();
        }

        $booking->loadMissing(['guest:id,name,city,country,languages,travel_purpose,is_smoker,prefers_quiet,sleep_schedule,rating_as_guest', 'bookingGuests']);
        $bookingGuests = $booking->bookingGuests;

        if ($bookingGuests->isEmpty()) {
            $this->createMainOccupant($booking, $stay);
        } else {
            foreach ($bookingGuests as $bookingGuest) {
                BookingStayOccupant::query()->create([
                    'booking_stay_id' => $stay->id,
                    'booking_id' => $booking->id,
                    'user_id' => $bookingGuest->user_id,
                    'occupant_name' => $bookingGuest->guest_name ?: $bookingGuest->full_name ?: $booking->guest?->name ?: __('occupants.messages.roommate'),
                    'occupant_type' => $bookingGuest->guest_type ?: ($bookingGuest->is_main_guest ? 'main_guest' : 'group_member'),
                    'is_main_guest' => (bool) $bookingGuest->is_main_guest,
                    ...$this->profileSnapshot($booking, (bool) $bookingGuest->is_main_guest),
                ]);
            }

            if (! $stay->occupants()->where('is_main_guest', true)->exists()) {
                $this->createMainOccupant($booking, $stay);
            }
        }

        return $stay->refresh()->occupants()->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addSecondGuest(BookingStay $stay, array $data): BookingStayOccupant
    {
        return BookingStayOccupant::query()->create([
            'booking_stay_id' => $stay->id,
            'booking_id' => $stay->booking_id,
            'user_id' => $data['user_id'] ?? null,
            'occupant_name' => $data['occupant_name'] ?? $data['guest_name'] ?? __('occupants.messages.roommate'),
            'occupant_type' => 'second_guest',
            'is_main_guest' => false,
            'age_range' => $data['age_range'] ?? null,
            'gender' => $data['gender'] ?? null,
            'public_gender_visible' => (bool) ($data['public_gender_visible'] ?? false),
            'city_name' => $data['city_name'] ?? null,
            'country_name' => $data['country_name'] ?? null,
            'languages_json' => $data['languages_json'] ?? null,
            'stay_purpose' => $data['stay_purpose'] ?? null,
            'sleep_schedule' => $data['sleep_schedule'] ?? null,
            'smoking_status' => $data['smoking_status'] ?? null,
            'sociability_level' => $data['sociability_level'] ?? null,
            'neighbor_rating_snapshot' => $data['neighbor_rating_snapshot'] ?? null,
            'public_visibility' => $data['public_visibility'] ?? 'roommates_only',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateOccupantVisibility(BookingStayOccupant $occupant, array $data): BookingStayOccupant
    {
        $occupant->forceFill([
            'public_visibility' => $data['public_visibility'] ?? $occupant->public_visibility,
            'public_gender_visible' => $data['public_gender_visible'] ?? $occupant->public_gender_visible,
        ])->save();

        return $occupant->refresh();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getPublicRoommateSummary(Room $room, ?User $viewer = null): Collection
    {
        return BookingStayOccupant::query()
            ->select([
                'id',
                'booking_stay_id',
                'booking_id',
                'user_id',
                'occupant_name',
                'occupant_type',
                'is_main_guest',
                'age_range',
                'gender',
                'public_gender_visible',
                'city_name',
                'country_name',
                'languages_json',
                'stay_purpose',
                'sleep_schedule',
                'smoking_status',
                'sociability_level',
                'neighbor_rating_snapshot',
                'public_visibility',
            ])
            ->whereHas('stay', fn ($query) => $query->forRoom($room)->active())
            ->when($viewer, fn ($query) => $query->where(function ($nested) use ($viewer): void {
                $nested->whereNull('user_id')->orWhere('user_id', '!=', $viewer->id);
            }))
            ->with('stay.visibilityPreference')
            ->orderBy('id')
            ->get()
            ->map(fn (BookingStayOccupant $occupant): array => $this->visibility->filterOccupantForRoommates($occupant));
    }

    private function createMainOccupant(Booking $booking, BookingStay $stay): BookingStayOccupant
    {
        return BookingStayOccupant::query()->create([
            'booking_stay_id' => $stay->id,
            'booking_id' => $booking->id,
            'user_id' => $booking->guest_user_id,
            'occupant_name' => $booking->guest?->name ?: __('occupants.messages.roommate'),
            'occupant_type' => 'main_guest',
            'is_main_guest' => true,
            ...$this->profileSnapshot($booking, true),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function profileSnapshot(Booking $booking, bool $mainGuest): array
    {
        $guest = $mainGuest ? $booking->guest : null;
        $languages = $guest?->languages;

        return [
            'age_range' => null,
            'gender' => $guest?->gender,
            'public_gender_visible' => false,
            'city_name' => $guest?->city,
            'country_name' => $guest?->country,
            'languages_json' => is_array($languages) ? $languages : $this->languageList($languages),
            'stay_purpose' => $guest?->travel_purpose,
            'sleep_schedule' => $guest?->sleep_schedule,
            'smoking_status' => $guest?->is_smoker === null ? null : ($guest->is_smoker ? 'smokes' : 'does_not_smoke'),
            'sociability_level' => $guest?->prefers_quiet ? 'prefers_quiet' : null,
            'neighbor_rating_snapshot' => $guest?->rating_as_guest,
            'public_visibility' => 'roommates_only',
        ];
    }

    /**
     * @return list<string>
     */
    private function languageList(mixed $languages): array
    {
        if ($languages === null || $languages === '') {
            return [];
        }

        if (is_string($languages)) {
            return array_values(array_filter(array_map('trim', explode(',', $languages))));
        }

        return [];
    }
}
