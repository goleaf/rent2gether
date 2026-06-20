<?php

namespace App\Livewire\Hints;

use App\Data\Hints\HintContext;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Hints\GuestHintService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ListingCardHints extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public int $limit = 3;

    public function mount(int $sleepingPlaceId, string $checkIn = '', string $checkOut = '', int $limit = 3): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
        $this->limit = max(1, min(3, $limit));
    }

    public function render(GuestHintService $hints): View
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);
        $user = auth()->user();

        return view('livewire.hints.listing-card-hints', [
            'hints' => $place instanceof SleepingPlace
                ? $hints->getHintsForCard($user instanceof User ? $user : null, $place, $this->context())->take($this->limit)->map->toArray(app()->getLocale())->all()
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
            surface: 'card',
        );
    }
}
