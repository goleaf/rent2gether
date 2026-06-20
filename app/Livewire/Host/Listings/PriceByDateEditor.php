<?php

namespace App\Livewire\Host\Listings;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PriceByDateEditor extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $start = '';

    public string $end = '';

    public float $price = 0;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->start = now()->addDay()->toDateString();
        $this->end = now()->addDays(8)->toDateString();
    }

    public function save(HostCalendarDraftService $calendar): void
    {
        $host = auth()->user();
        $place = SleepingPlace::query()->findOrFail($this->sleepingPlaceId);

        abort_unless($host instanceof User, 403);

        $calendar->setPriceForDates($host, $place, ['start' => $this->start, 'end' => $this->end], $this->price);
        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        return view('livewire.host.listings.price-by-date-editor');
    }
}
