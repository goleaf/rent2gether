<?php

namespace App\Livewire\Host\Listings;

use App\Models\SleepingPlace;
use App\Services\HostListings\Creation\ListingSuggestionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ListingSuggestionsPanel extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function render(ListingSuggestionService $suggestions): View
    {
        $place = SleepingPlace::query()->find($this->sleepingPlaceId);
        $items = $place instanceof SleepingPlace ? $suggestions->generateForSleepingPlace($place) : collect();

        return view('livewire.host.listings.listing-suggestions-panel', [
            'suggestions' => $items->take(5),
        ]);
    }
}
