<?php

namespace App\Livewire\Booking;

use App\Models\SleepingPlace;
use App\Services\Availability\AvailabilityService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AvailabilityChecker extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    /** @var array{available:bool,unavailable_dates:list<string>,nearest_ranges:list<array{check_in:string,check_out:string,nights:int}>}|null */
    public ?array $result = null;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function checkAvailability(AvailabilityService $availability): void
    {
        $validated = $this->validate([
            'checkIn' => ['required', 'date', 'after_or_equal:today'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
        ], attributes: $this->validationAttributes());

        $place = SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'status'])
            ->with([
                'room:id,property_id,status',
                'property:id,status',
            ])
            ->findOrFail($this->sleepingPlaceId);
        $available = $availability->isAvailable($place, $validated['checkIn'], $validated['checkOut']);
        $nights = max(1, (int) now()->parse($validated['checkIn'])->diffInDays($validated['checkOut']));

        $this->result = [
            'available' => $available,
            'unavailable_dates' => $available ? [] : $availability->unavailableDates($place, $validated['checkIn'], $validated['checkOut']),
            'nearest_ranges' => $available ? [] : $availability->nearestAvailableRanges($place, $validated['checkIn'], $nights),
        ];
    }

    public function render(): View
    {
        return view('livewire.booking.availability-checker');
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('availability.checker.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }
}
