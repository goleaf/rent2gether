<?php

namespace App\Livewire\Host;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PropertyList extends Component
{
    #[Computed]
    public function properties()
    {
        return Property::where('user_id', auth()->id())
            ->withCount('rooms', 'beds')
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.host.property-list');
    }
}
