<?php

namespace App\Livewire\SleepingPlaces;

use App\Models\SleepingPlace;
use App\Services\SleepingPlaceHierarchyService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SleepingPlacePublicPage extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->sleepingPlaceId = $sleepingPlace->id;
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function context(): ?array
    {
        $place = SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'user_id', 'title', 'display_name', 'place_type', 'type', 'base_price', 'base_price_per_night', 'currency', 'status', 'publication_status'])
            ->with([
                'room:id,property_id,title,room_type,gender_policy,free_sleeping_places_count',
                'property:id,user_id,host_user_id,title,property_type,city_id,district',
                'host:id,name',
            ])
            ->find($this->sleepingPlaceId);

        if (! $place) {
            return null;
        }

        $context = app(SleepingPlaceHierarchyService::class)->getFullContext($place);

        return [
            'place' => [
                'id' => $place->id,
                'title' => $place->title ?: $place->display_name,
                'type' => $place->place_type ?: ($place->type?->value ?? $place->type),
                'price' => $place->base_price ?: $place->base_price_per_night,
                'currency' => $place->currency,
            ],
            'room' => [
                'title' => $context['room']->title,
                'type' => $context['room']->room_type?->label() ?? $context['room']->room_type,
                'free_places' => $context['room']->free_sleeping_places_count,
            ],
            'property' => [
                'title' => $context['property']->title,
                'type' => $context['property']->property_type?->label() ?? $context['property']->property_type,
                'district' => $context['property']->district,
            ],
            'host' => [
                'name' => $context['host']->name,
            ],
        ];
    }

    public function render(): View
    {
        return view('livewire.sleeping-places.sleeping-place-public-page');
    }
}
