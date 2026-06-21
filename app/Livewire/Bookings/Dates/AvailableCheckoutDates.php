<?php

namespace App\Livewire\Bookings\Dates;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Bookings\BookingDateSelectionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AvailableCheckoutDates extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkInDate = '';

    public function mount(int|SleepingPlace $sleepingPlace, string $checkInDate = ''): void
    {
        $this->sleepingPlaceId = $sleepingPlace instanceof SleepingPlace ? $sleepingPlace->id : $sleepingPlace;
        $this->checkInDate = $checkInDate;
    }

    public function render(): View
    {
        $dates = [];
        $guest = auth()->user();

        if ($guest instanceof User && $this->checkInDate !== '') {
            $place = SleepingPlace::query()->findOrFail($this->sleepingPlaceId);
            $dates = app(BookingDateSelectionService::class)
                ->availableCheckoutDates($guest, $place, $this->checkInDate)
                ->all();
        }

        return view('livewire.bookings.dates.available-checkout-dates', [
            'dates' => $dates,
        ]);
    }
}
