<?php

namespace App\Livewire\Hints;

use App\Data\Hints\HintContext;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Hints\GuestHintService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BeforeBookingHints extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public bool $understood = false;

    public function mount(int $sleepingPlaceId, string $checkIn = '', string $checkOut = ''): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
    }

    public function confirm(): void
    {
        $this->understood = true;
        $this->dispatch('guest-hints-understood');
    }

    public function render(GuestHintService $hints): View
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);
        $user = auth()->user();

        return view('livewire.hints.before-booking-hints', [
            'hints' => $place instanceof SleepingPlace && $user instanceof User
                ? $hints->getHintsBeforeBooking($user, $place, $this->context())->map->toArray(app()->getLocale())->all()
                : [],
        ]);
    }

    private function context(): HintContext
    {
        return new HintContext(
            checkInDate: $this->checkIn ?: null,
            checkOutDate: $this->checkOut ?: null,
            userId: auth()->id(),
            locale: app()->getLocale(),
            surface: 'before_booking',
        );
    }
}
