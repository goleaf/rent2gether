<?php

namespace App\Livewire\Host\Listings\Steps;

use App\Models\Property;
use App\Models\User;
use App\Services\HostListings\Wizard\HostPropertyDraftService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PropertyStep extends Component
{
    #[Locked]
    public int $propertyId;

    public string $title = '';

    public string $description = '';

    public string $district = '';

    public function mount(int $propertyId): void
    {
        $property = Property::query()->findOrFail($propertyId);
        $this->propertyId = $property->id;
        $this->title = (string) $property->title;
        $this->description = (string) $property->description;
        $this->district = (string) $property->district;
    }

    public function save(HostPropertyDraftService $drafts): void
    {
        $host = auth()->user();

        if ($host instanceof User) {
            $drafts->createOrUpdateProperty($host, [
                'property_id' => $this->propertyId,
                'title' => $this->title,
                'description' => $this->description,
                'district' => $this->district,
            ]);
            $this->dispatch('listing-step-saved');
        }
    }

    public function render(): View
    {
        return view('livewire.host.listings.steps.property-step');
    }
}
