<?php

namespace App\Livewire\Host\Listings;

use App\Models\Property;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CalendarRulesEditor extends Component
{
    #[Locked]
    public int $propertyId;

    public ?int $sleepingPlaceId = null;

    public ?float $defaultPrice = null;

    public ?int $minNights = null;

    public ?int $maxNights = null;

    public int $cleaningGapHours = 0;

    public int $cleaningGapDays = 0;

    public string $checkInTimeFrom = '';

    public string $checkOutTimeUntil = '';

    /** @var list<int> */
    public array $checkInDays = [1, 2, 3, 4, 5, 6, 7];

    /** @var list<int> */
    public array $checkOutDays = [1, 2, 3, 4, 5, 6, 7];

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
        $this->sleepingPlaceId = Property::query()
            ->findOrFail($propertyId)
            ->sleepingPlaces()
            ->value('id');
    }

    public function saveSettings(HostCalendarDraftService $calendar): void
    {
        $host = auth()->user();
        $place = $this->place();

        abort_unless($host instanceof User && $place instanceof SleepingPlace, 403);

        $calendar->updateSettings($host, $place, [
            'default_price' => $this->defaultPrice,
            'currency' => $place->currency,
            'min_nights' => $this->minNights,
            'max_nights' => $this->maxNights,
            'cleaning_gap_hours' => $this->cleaningGapHours,
            'cleaning_gap_days' => $this->cleaningGapDays,
            'check_in_time_from' => $this->checkInTimeFrom ?: null,
            'check_out_time_until' => $this->checkOutTimeUntil ?: null,
        ]);

        $calendar->setCheckInDays($host, $place, $this->checkInDays);
        $calendar->setCheckOutDays($host, $place, $this->checkOutDays);

        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        $places = Property::query()
            ->findOrFail($this->propertyId)
            ->sleepingPlaces()
            ->select(['id', 'display_name', 'place_number', 'base_price_per_night'])
            ->orderBy('id')
            ->get();

        return view('livewire.host.listings.calendar-rules-editor', [
            'places' => $places,
            'weekdays' => [1, 2, 3, 4, 5, 6, 7],
        ]);
    }

    private function place(): ?SleepingPlace
    {
        return $this->sleepingPlaceId
            ? SleepingPlace::query()->find($this->sleepingPlaceId)
            : null;
    }
}
