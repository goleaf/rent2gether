<?php

namespace App\Livewire\Host\Properties;

use App\Livewire\Host\Properties\Concerns\HandlesPropertyStep;
use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PropertyStructureStep extends Component
{
    use HandlesPropertyStep;

    public ?float $livingArea = null;

    public ?float $totalArea = null;

    public ?int $roomsCount = null;

    public ?int $bedroomsCount = null;

    public ?int $sharedRoomsCount = null;

    public ?int $passThroughRoomsCount = null;

    public ?int $bathroomsCount = null;

    public ?int $showersCount = null;

    public ?int $kitchensCount = null;

    public ?int $balconiesCount = null;

    public ?int $maxResidents = null;

    public bool $canBookWholeProperty = false;

    public bool $canBookPrivateRoom = true;

    public bool $canBookSleepingPlace = true;

    public function mount(Property $property): void
    {
        $this->mountProperty($property);

        $this->totalArea = $property->total_area === null ? null : (float) $property->total_area;
        $this->livingArea = $property->living_area === null ? null : (float) $property->living_area;
        $this->roomsCount = $property->rooms_count;
        $this->bedroomsCount = $property->bedrooms_count;
        $this->sharedRoomsCount = $property->shared_rooms_count;
        $this->passThroughRoomsCount = $property->pass_through_rooms_count;
        $this->bathroomsCount = $property->bathrooms_count;
        $this->showersCount = $property->showers_count;
        $this->kitchensCount = $property->kitchens_count;
        $this->balconiesCount = $property->balconies_count;
        $this->maxResidents = $property->max_residents;
        $this->canBookWholeProperty = (bool) $property->can_book_whole_property;
        $this->canBookPrivateRoom = (bool) $property->can_book_private_room;
        $this->canBookSleepingPlace = (bool) $property->can_book_sleeping_place;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'totalArea' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'livingArea' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'roomsCount' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'bedroomsCount' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'sharedRoomsCount' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'passThroughRoomsCount' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'bathroomsCount' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'showersCount' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'kitchensCount' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'balconiesCount' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'maxResidents' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'canBookWholeProperty' => ['boolean'],
            'canBookPrivateRoom' => ['boolean'],
            'canBookSleepingPlace' => ['boolean'],
        ]);

        $this->property()->update([
            'total_area' => $validated['totalArea'],
            'living_area' => $validated['livingArea'],
            'rooms_count' => $validated['roomsCount'],
            'bedrooms_count' => $validated['bedroomsCount'],
            'shared_rooms_count' => $validated['sharedRoomsCount'],
            'pass_through_rooms_count' => $validated['passThroughRoomsCount'],
            'bathrooms_count' => $validated['bathroomsCount'],
            'showers_count' => $validated['showersCount'],
            'kitchens_count' => $validated['kitchensCount'],
            'balconies_count' => $validated['balconiesCount'],
            'max_residents' => $validated['maxResidents'],
            'can_book_whole_property' => $validated['canBookWholeProperty'],
            'can_book_private_room' => $validated['canBookPrivateRoom'],
            'can_book_sleeping_place' => $validated['canBookSleepingPlace'],
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.properties.property-structure-step');
    }
}
