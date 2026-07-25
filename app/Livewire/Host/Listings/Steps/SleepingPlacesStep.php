<?php

namespace App\Livewire\Host\Listings\Steps;

use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlacesStep extends Component
{
    public int $propertyId;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
    }

    public function render(): View
    {
        $property = Property::query()
            ->select(['id', 'host_user_id', 'user_id'])
            ->findOrFail($this->propertyId);

        $host = auth()->user();
        abort_unless($host instanceof User && $property->isOwnedBy($host), 403);

        return view('livewire.host.listings.steps.sleeping-places-step', [
            'rooms' => $property->rooms()
                ->select(['id', 'property_id', 'title', 'sleeping_places_count', 'sort_order'])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }
}
