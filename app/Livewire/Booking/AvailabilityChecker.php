<?php

namespace App\Livewire\Booking;

use App\Models\SleepingPlace;
use App\Services\Availability\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AvailabilityChecker extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public bool $availabilityRequested = false;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function updatedCheckIn(): void
    {
        $this->resetAvailabilityResult();
    }

    public function updatedCheckOut(): void
    {
        $this->resetAvailabilityResult();
    }

    public function checkAvailability(): void
    {
        $this->resetValidation();
        $this->availabilityRequested = true;
        unset($this->availabilityResult);

        $this->availabilityResult;
    }

    public function render(): View
    {
        return view('livewire.booking.availability-checker', [
            'result' => $this->availabilityResult,
        ]);
    }

    /**
     * @return array{available:bool,unavailable_dates:list<array{date:string,label:string}>,nearest_ranges:list<array{check_in:string,check_out:string,nights:int,label:string}>}|null
     */
    #[Computed]
    public function availabilityResult(): ?array
    {
        if (! $this->availabilityRequested) {
            return null;
        }

        $validated = $this->validatedDateInputs();

        if ($validated === null) {
            return null;
        }

        $place = $this->sleepingPlace;
        $availability = app(AvailabilityService::class);
        $available = $availability->isAvailable($place, $validated['checkIn'], $validated['checkOut']);

        if ($available) {
            return [
                'available' => true,
                'unavailable_dates' => [],
                'nearest_ranges' => [],
            ];
        }

        $nights = max(1, (int) CarbonImmutable::parse($validated['checkIn'])->diffInDays($validated['checkOut']));

        return [
            'available' => false,
            'unavailable_dates' => $this->formatUnavailableDates(
                $availability->unavailableDates($place, $validated['checkIn'], $validated['checkOut']),
            ),
            'nearest_ranges' => $this->formatNearestRanges(
                $availability->nearestAvailableRanges($place, $validated['checkIn'], $nights),
            ),
        ];
    }

    #[Computed]
    public function sleepingPlace(): SleepingPlace
    {
        return SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'status'])
            ->with([
                'room:id,property_id,status',
                'property:id,status',
            ])
            ->findOrFail($this->sleepingPlaceId);
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('availability.checker.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }

    private function resetAvailabilityResult(): void
    {
        $this->resetValidation();
        $this->availabilityRequested = false;
        unset($this->availabilityResult);
    }

    /**
     * @return array{checkIn:string,checkOut:string}|null
     */
    private function validatedDateInputs(): ?array
    {
        $validator = validator([
            'checkIn' => $this->checkIn,
            'checkOut' => $this->checkOut,
        ], [
            'checkIn' => ['required', 'date', 'after_or_equal:today'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
        ], [], $this->validationAttributes());

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                $this->addError($field, (string) $messages[0]);
            }

            return null;
        }

        /** @var array{checkIn:string,checkOut:string} $validated */
        $validated = $validator->validated();

        return $validated;
    }

    /**
     * @param  list<string>  $dates
     * @return list<array{date:string,label:string}>
     */
    private function formatUnavailableDates(array $dates): array
    {
        return collect($dates)
            ->map(fn (string $date): array => [
                'date' => $date,
                'label' => CarbonImmutable::parse($date)->translatedFormat('d M'),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{check_in:string,check_out:string,nights:int}>  $ranges
     * @return list<array{check_in:string,check_out:string,nights:int,label:string}>
     */
    private function formatNearestRanges(array $ranges): array
    {
        return collect($ranges)
            ->map(fn (array $range): array => [
                'check_in' => $range['check_in'],
                'check_out' => $range['check_out'],
                'nights' => $range['nights'],
                'label' => __('availability.checker.range_label', [
                    'check_in' => CarbonImmutable::parse($range['check_in'])->translatedFormat('d M'),
                    'check_out' => CarbonImmutable::parse($range['check_out'])->translatedFormat('d M'),
                    'nights' => $range['nights'],
                ]),
            ])
            ->values()
            ->all();
    }
}
