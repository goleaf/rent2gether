<?php

namespace App\Livewire\Host\Properties;

use App\Models\Property;
use App\Models\User;
use App\Services\Properties\PropertyCompletionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PropertyCompletionPanel extends Component
{
    public int $propertyId;

    public function mount(Property $property): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $property->isOwnedBy($user), 403);

        $this->propertyId = $property->id;
    }

    public function render(PropertyCompletionService $completion): View
    {
        $property = Property::query()
            ->with(['translations', 'locationDetails', 'conditionDetails', 'accessDetails', 'mediaItems'])
            ->findOrFail($this->propertyId);

        return view('livewire.host.properties.property-completion-panel', [
            'completion' => $completion->evaluate($property),
        ]);
    }
}
