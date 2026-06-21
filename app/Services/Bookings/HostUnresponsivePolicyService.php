<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\HostUnresponsivePolicy;
use App\Models\Property;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HostUnresponsivePolicyService
{
    public function getForBooking(Booking $booking): HostUnresponsivePolicy
    {
        $booking->loadMissing('sleepingPlace', 'property');

        if ($booking->sleepingPlace instanceof SleepingPlace) {
            return $this->getForSleepingPlace($booking->sleepingPlace)
                ?? $this->createDefaultForSleepingPlace($booking->sleepingPlace);
        }

        if ($booking->property instanceof Property) {
            return $this->getForProperty($booking->property)
                ?? $this->createDefaultForProperty($booking->property);
        }

        throw ValidationException::withMessages([
            'booking' => __('host_unresponsive.validation.booking_missing_place'),
        ]);
    }

    public function getForSleepingPlace(SleepingPlace $place): ?HostUnresponsivePolicy
    {
        return HostUnresponsivePolicy::query()
            ->where('sleeping_place_id', $place->id)
            ->where('active', true)
            ->latest('id')
            ->first()
            ?? ($place->property_id ? $this->getForProperty($place->property()->firstOrFail()) : null);
    }

    public function getForProperty(Property $property): ?HostUnresponsivePolicy
    {
        return HostUnresponsivePolicy::query()
            ->where('property_id', $property->id)
            ->whereNull('sleeping_place_id')
            ->where('active', true)
            ->latest('id')
            ->first();
    }

    public function createDefaultForSleepingPlace(SleepingPlace $place): HostUnresponsivePolicy
    {
        return HostUnresponsivePolicy::query()->create([
            'sleeping_place_id' => $place->id,
            'property_id' => $place->property_id,
            ...$this->defaults(),
        ]);
    }

    public function createDefaultForProperty(Property $property): HostUnresponsivePolicy
    {
        return HostUnresponsivePolicy::query()->create([
            'property_id' => $property->id,
            ...$this->defaults(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateForSleepingPlace(User $host, SleepingPlace $place, array $data): HostUnresponsivePolicy
    {
        $place->loadMissing('property:id,host_user_id,user_id');

        if ((int) ($place->user_id ?: $place->property?->host_user_id ?: $place->property?->user_id) !== (int) $host->id) {
            throw ValidationException::withMessages([
                'sleeping_place' => __('host_unresponsive.validation.not_host_place'),
            ]);
        }

        $validated = Validator::make($data, [
            'pre_check_in_response_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'check_in_response_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'guest_waiting_outside_response_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
            'night_entry_response_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
            'urgent_response_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'notify_representative_if_available' => ['nullable', 'boolean'],
            'auto_show_instructions_if_allowed' => ['nullable', 'boolean'],
            'auto_block_no_show_while_active' => ['nullable', 'boolean'],
            'allow_guest_cancellation_after_deadline' => ['nullable', 'boolean'],
            'allow_guest_relocation_after_deadline' => ['nullable', 'boolean'],
            'guest_friendly_refund_if_confirmed' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ], [], __('host_unresponsive.validation.attributes'))->validate();

        HostUnresponsivePolicy::query()
            ->where('sleeping_place_id', $place->id)
            ->where('active', true)
            ->update(['active' => false]);

        return HostUnresponsivePolicy::query()->create([
            'sleeping_place_id' => $place->id,
            'property_id' => $place->property_id,
            ...array_merge($this->defaults(), $validated),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'pre_check_in_response_minutes' => 240,
            'check_in_response_minutes' => 60,
            'guest_waiting_outside_response_minutes' => 20,
            'night_entry_response_minutes' => 15,
            'urgent_response_minutes' => 10,
            'notify_representative_if_available' => true,
            'auto_show_instructions_if_allowed' => true,
            'auto_block_no_show_while_active' => true,
            'allow_guest_cancellation_after_deadline' => true,
            'allow_guest_relocation_after_deadline' => true,
            'guest_friendly_refund_if_confirmed' => true,
            'active' => true,
        ];
    }
}
