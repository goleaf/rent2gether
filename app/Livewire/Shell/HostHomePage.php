<?php

namespace App\Livewire\Shell;

use App\Models\User;
use App\Services\HostListings\HostListingDashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostHomePage extends Component
{
    public function render(HostListingDashboardService $dashboard): View
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return view('livewire.shell.host-home-page', [
            'dashboard' => $dashboard->home($user),
        ])->layout('layouts.app', [
            'title' => __('shell.pages.host.home.title'),
        ]);
    }
}
