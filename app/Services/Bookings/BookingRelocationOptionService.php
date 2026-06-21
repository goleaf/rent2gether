<?php

namespace App\Services\Bookings;

use App\Enums\SleepingPlaceStatus;
use App\Models\BookingRelocation;
use App\Models\BookingRelocationOption;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class BookingRelocationOptionService
{
    public function __construct(
        private readonly BookingRelocationAvailabilityService $availability,
        private readonly BookingRelocationPriceService $pricing,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, BookingRelocationOption>
     */
    public function findOptions(BookingRelocation $relocation, array $filters = []): Collection
    {
        $query = SleepingPlace::query()
            ->with(['property:id,host_user_id,user_id,status', 'room:id,property_id,status,title'])
            ->whereKeyNot($relocation->current_sleeping_place_id)
            ->where('user_id', $relocation->host_user_id)
            ->where('status', SleepingPlaceStatus::Active->value)
            ->when($filters['room_id'] ?? null, fn ($query, int $roomId) => $query->where('room_id', $roomId))
            ->when($filters['property_id'] ?? null, fn ($query, int $propertyId) => $query->where('property_id', $propertyId))
            ->orderBy('property_id')
            ->orderBy('room_id')
            ->orderBy('id')
            ->limit(20);

        return $query->get()
            ->sortBy(fn (SleepingPlace $place): int => $this->optionPriority($relocation, $place))
            ->map(fn (SleepingPlace $place): BookingRelocationOption => $this->createOption($relocation, $place))
            ->filter(fn (BookingRelocationOption $option): bool => $option->availability_status === 'available')
            ->values();
    }

    /**
     * @return Collection<int, BookingRelocationOption>
     */
    public function findSameRoomOptions(BookingRelocation $relocation): Collection
    {
        return $this->findOptions($relocation, ['room_id' => $relocation->current_room_id]);
    }

    /**
     * @return Collection<int, BookingRelocationOption>
     */
    public function findSamePropertyOptions(BookingRelocation $relocation): Collection
    {
        return $this->findOptions($relocation, ['property_id' => $relocation->current_property_id]);
    }

    /**
     * @return Collection<int, BookingRelocationOption>
     */
    public function findSameHostOptions(BookingRelocation $relocation): Collection
    {
        return $this->findOptions($relocation);
    }

    public function selectOption(User $guest, BookingRelocationOption $option): BookingRelocationOption
    {
        $option->loadMissing('relocation');

        if ((int) $option->relocation->guest_user_id !== (int) $guest->id) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        $option->relocation->options()->update(['guest_selected' => false, 'selected_at' => null]);
        $option->forceFill([
            'guest_selected' => true,
            'selected_at' => now(),
        ])->save();

        return $option->refresh();
    }

    public function createOption(BookingRelocation $relocation, SleepingPlace $place, ?string $hostNote = null): BookingRelocationOption
    {
        $candidate = $this->candidateRelocation($relocation, $place);
        $availability = $this->availability->checkNewPlace($candidate);
        $oldValue = (float) ($relocation->old_remaining_value_amount ?: $this->pricing->calculateOldRemainingValue($relocation));
        $nights = $this->remainingNights($relocation);
        $newValue = round($nights * (float) ($place->base_price_per_night ?: $place->base_price ?: 0), 2);
        $difference = round($newValue - $oldValue, 2);

        return BookingRelocationOption::query()->updateOrCreate([
            'booking_relocation_id' => $relocation->id,
            'sleeping_place_id' => $place->id,
        ], [
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'price_difference_amount' => $difference,
            'additional_payment_amount' => max(0, $difference),
            'refund_amount' => $difference < 0 ? abs($difference) : 0,
            'additional_deposit_amount' => max(0, (float) ($place->deposit_amount ?: 0) - (float) ($relocation->currentSleepingPlace?->deposit_amount ?: 0)),
            'currency' => $place->currency ?: $relocation->currency,
            'availability_status' => $availability['available'] ? 'available' : 'unavailable',
            'compatibility_status' => 'good',
            'pricing_status' => 'calculated',
            'distance_label' => $place->property_id === $relocation->current_property_id ? 'same_property' : 'same_host',
            'room_privacy_level' => $place->privacy_level,
            'comfort_score' => 80,
            'match_score' => $place->room_id === $relocation->current_room_id ? 95 : 80,
            'host_note' => $hostNote,
            'expires_at' => $relocation->expires_at,
        ]);
    }

    private function candidateRelocation(BookingRelocation $relocation, SleepingPlace $place): BookingRelocation
    {
        $candidate = new BookingRelocation($relocation->getAttributes());
        $candidate->exists = true;
        $candidate->setAttribute('id', $relocation->id);
        $candidate->setAttribute('new_property_id', $place->property_id);
        $candidate->setAttribute('new_room_id', $place->room_id);
        $candidate->setAttribute('new_sleeping_place_id', $place->id);
        $candidate->setRelation('newSleepingPlace', $place);
        $candidate->setRelation('originalBooking', $relocation->originalBooking);

        return $candidate;
    }

    private function remainingNights(BookingRelocation $relocation): int
    {
        $start = CarbonImmutable::parse($relocation->new_period_check_in_date)->startOfDay();
        $end = CarbonImmutable::parse($relocation->new_period_check_out_date)->startOfDay();

        return max(0, (int) $start->diffInDays($end));
    }

    private function optionPriority(BookingRelocation $relocation, SleepingPlace $place): int
    {
        if ((int) $place->room_id === (int) $relocation->current_room_id) {
            return 0;
        }

        if ((int) $place->property_id === (int) $relocation->current_property_id) {
            return 1;
        }

        return 2;
    }
}
