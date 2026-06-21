<?php

namespace App\Livewire\Host\Stays;

use App\Models\Property;
use App\Services\Stays\PropertyOccupancySnapshotService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostPropertyOccupancySummary extends Component
{
    public ?int $propertyId = null;

    public function mount(Property|int|null $property = null): void
    {
        $this->propertyId = $property instanceof Property ? $property->id : ($property ? (int) $property : null);
    }

    public function render(): View
    {
        $property = $this->propertyId ? Property::query()->find($this->propertyId) : null;

        return view('livewire.host.stays.occupancy-summary', [
            'title' => __('stays.components.property_occupancy'),
            'summary' => $property ? app(PropertyOccupancySnapshotService::class)->getForHost($property) : [],
        ]);
    }
}
