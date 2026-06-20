<?php

namespace App\Livewire\Hints;

use App\Data\Hints\HintContext;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Hints\GuestHintService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ListingDetailHints extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public function mount(int $sleepingPlaceId, string $checkIn = '', string $checkOut = ''): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
    }

    public function render(GuestHintService $hints): View
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);
        $user = auth()->user();

        return view('livewire.hints.listing-detail-hints', [
            'hints' => $place instanceof SleepingPlace
                ? $hints->getHintsForDetail($user instanceof User ? $user : null, $place, $this->context())->map->toArray(app()->getLocale())->all()
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
            surface: 'detail',
        );
    }
}
