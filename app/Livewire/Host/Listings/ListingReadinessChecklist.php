<?php

namespace App\Livewire\Host\Listings;

use App\Models\Property;
use App\Services\HostListings\Wizard\HostListingReadinessService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ListingReadinessChecklist extends Component
{
    #[Locked]
    public int $propertyId;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
    }

    public function render(HostListingReadinessService $readiness): View
    {
        $property = Property::query()->find($this->propertyId);

        return view('livewire.host.listings.listing-readiness-checklist', [
            'readiness' => $property instanceof Property
                ? $readiness->checkPublishReadiness($property)
                : ['ready' => false, 'score' => 0, 'blocking' => [], 'recommended' => [], 'checks' => []],
        ]);
    }
}
