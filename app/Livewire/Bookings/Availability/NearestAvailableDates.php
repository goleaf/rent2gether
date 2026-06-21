<?php

namespace App\Livewire\Bookings\Availability;

use App\Models\SleepingPlace;
use App\Services\Availability\SleepingPlaceAvailabilitySuggestionService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class NearestAvailableDates extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $preferredCheckIn = '';

    public int $nights = 1;

    public function mount(int $sleepingPlaceId, ?string $preferredCheckIn = null, int $nights = 1): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->preferredCheckIn = $preferredCheckIn ?? now()->toDateString();
        $this->nights = max(1, min(60, $nights));
    }

    #[Computed]
    public function ranges(): array
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);

        if (! $place instanceof SleepingPlace) {
            return [];
        }

        return app(SleepingPlaceAvailabilitySuggestionService::class)
            ->suggestNearestAvailableRanges($place, CarbonImmutable::parse($this->preferredCheckIn), $this->nights)
            ->all();
    }

    public function render(): View
    {
        return view('livewire.bookings.availability.nearest-available-dates');
    }
}
