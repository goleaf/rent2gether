<?php

namespace App\Livewire\Host\Listings;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ListingDraftSaveIndicator extends Component
{
    #[Locked]
    public int $propertyId;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
    }

    public function render(): View
    {
        $lastSavedAt = Property::query()->find($this->propertyId)?->listingWizardSessions()->latest('id')->value('last_saved_at');

        return view('livewire.host.listings.listing-draft-save-indicator', [
            'lastSavedAt' => $lastSavedAt,
        ]);
    }
}
