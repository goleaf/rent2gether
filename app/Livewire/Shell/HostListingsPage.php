<?php

namespace App\Livewire\Shell;

use App\Models\User;
use App\Services\HostListings\HostListingDashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostListingsPage extends Component
{
    public string $scope = 'all';

    public function mount(string $scope = 'all'): void
    {
        $this->scope = in_array($scope, ['all', 'drafts', 'hidden'], true) ? $scope : 'all';
    }

    public function render(HostListingDashboardService $dashboard): View
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $page = $dashboard->listingPage($user, $this->scope);

        return view('livewire.shell.host-listings-page', [
            'page' => $page,
            'metrics' => $page['metrics'],
        ])->layout('layouts.app', [
            'title' => __('shell.pages.host.listings.title'),
        ]);
    }
}
