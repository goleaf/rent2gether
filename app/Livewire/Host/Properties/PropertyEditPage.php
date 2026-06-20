<?php

namespace App\Livewire\Host\Properties;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PropertyEditPage extends Component
{
    #[Locked]
    public int $propertyId;

    public function mount(Property $property): void
    {
        $this->propertyId = $property->id;
    }

    #[Computed]
    public function property(): ?Property
    {
        return Property::query()
            ->select(['id', 'title', 'type', 'property_type', 'status', 'publication_status', 'city_id', 'district', 'rooms_count'])
            ->withCount(['rooms', 'sleepingPlaces'])
            ->find($this->propertyId);
    }

    public function render(): View
    {
        return view('livewire.host.properties.property-edit-page');
    }
}
