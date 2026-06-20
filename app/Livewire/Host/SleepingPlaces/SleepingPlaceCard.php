<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Models\SleepingPlace;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SleepingPlaceCard extends Component
{
    public ?int $sleepingPlaceId = null;

    public function mount(?int $sleepingPlaceId = null): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function card(): ?array
    {
        if (! $this->sleepingPlaceId) {
            return null;
        }

        $place = SleepingPlace::query()
            ->select(['id', 'title', 'display_name', 'place_type', 'type', 'status', 'publication_status', 'base_price', 'base_price_per_night', 'currency'])
            ->find($this->sleepingPlaceId);

        if (! $place) {
            return null;
        }

        return [
            'title' => $place->title ?: $place->display_name,
            'type' => $place->place_type ?: ($place->type?->value ?? $place->type),
            'status' => $place->publication_status ?: ($place->status?->value ?? $place->status),
            'base_price' => $place->base_price ?: $place->base_price_per_night,
            'currency' => $place->currency,
        ];
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-card');
    }
}
