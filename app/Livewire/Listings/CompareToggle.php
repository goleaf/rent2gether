<?php

namespace App\Livewire\Listings;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CompareToggle extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public bool $selected = false;

    public function mount(int $sleepingPlaceId, bool $selected = false): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->selected = $selected || in_array($sleepingPlaceId, $this->ids(), true);
    }

    public function toggle(): void
    {
        $ids = collect($this->ids());

        if ($ids->contains($this->sleepingPlaceId)) {
            $ids = $ids->reject(fn (int $id): bool => $id === $this->sleepingPlaceId);
            $this->selected = false;
        } else {
            $ids->push($this->sleepingPlaceId);
            $this->selected = true;
        }

        session()->put('comparison_places', $ids->unique()->take(4)->values()->all());
        $this->dispatch('listing-compare-updated', ids: session('comparison_places', []));
    }

    public function render(): View
    {
        return view('livewire.listings.compare-toggle');
    }

    /**
     * @return list<int>
     */
    private function ids(): array
    {
        return collect(session('comparison_places', []))
            ->map(fn (mixed $id): int => (int) $id)
            ->filter()
            ->values()
            ->all();
    }
}
