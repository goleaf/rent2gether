<?php

namespace App\Livewire\Host\Properties;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PropertyCard extends Component
{
    public ?int $propertyId = null;

    public function mount(?int $propertyId = null): void
    {
        $this->propertyId = $propertyId;
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function card(): ?array
    {
        if (! $this->propertyId) {
            return null;
        }

        $property = Property::query()
            ->select(['id', 'title', 'type', 'property_type', 'status', 'publication_status', 'city_id', 'district'])
            ->withCount(['rooms', 'sleepingPlaces'])
            ->find($this->propertyId);

        if (! $property) {
            return null;
        }

        return [
            'title' => $property->title,
            'type' => $property->property_type?->label() ?? $property->type?->label() ?? $property->property_type,
            'status' => $property->publication_status ?: ($property->status?->value ?? $property->status),
            'rooms_count' => $property->rooms_count,
            'sleeping_places_count' => $property->sleeping_places_count,
            'district' => $property->district,
        ];
    }

    public function render(): View
    {
        return view('livewire.host.properties.property-card');
    }
}
