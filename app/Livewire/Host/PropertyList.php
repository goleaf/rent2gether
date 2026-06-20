<?php

namespace App\Livewire\Host;

use App\Models\User;
use App\Services\HostListings\HostListingDashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PropertyList extends Component
{
    public function render(HostListingDashboardService $dashboard): View
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return view('livewire.host.property-list', [
            'properties' => $dashboard->propertyCards($user),
        ])->layout('layouts.app', [
            'title' => __('host.my_properties'),
        ]);
    }
}
