<?php

namespace App\Livewire\Host\Listings;

use App\Models\Property;
use App\Services\HostListings\Wizard\HostListingWizardService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ListingWizardProgress extends Component
{
    #[Locked]
    public int $propertyId;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
    }

    public function render(HostListingWizardService $wizard): View
    {
        $property = Property::query()->find($this->propertyId);
        $progress = $property instanceof Property
            ? $wizard->getProgress($property)
            : ['current' => 'property', 'completed' => [], 'percentage' => 0, 'steps' => ['property', 'rooms', 'sleeping_places', 'calendar', 'publish']];

        $current = (int) array_search($progress['current'], $progress['steps'], true) + 1;

        return view('livewire.host.listings.listing-wizard-progress', [
            'progress' => $progress,
            'current' => $current,
            'total' => count($progress['steps']),
        ]);
    }
}
