<?php

namespace App\Livewire\Bookings\Availability;

use App\Models\SleepingPlace;
use App\Services\Availability\SleepingPlaceAvailabilitySuggestionService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AvailableCheckoutDates extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public function mount(int $sleepingPlaceId, ?string $checkIn = null): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->checkIn = $checkIn ?? now()->toDateString();
    }

    #[Computed]
    public function dates(): array
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);

        if (! $place instanceof SleepingPlace) {
            return [];
        }

        return app(SleepingPlaceAvailabilitySuggestionService::class)
            ->suggestAvailableCheckOutDates($place, CarbonImmutable::parse($this->checkIn))
            ->all();
    }

    public function render(): View
    {
        return view('livewire.bookings.availability.available-checkout-dates');
    }
}
