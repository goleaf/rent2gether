<?php

namespace App\Livewire\Shell;

use App\Models\User;
use App\Services\HostListings\HostListingDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Component;

class HostHomePage extends Component
{
    public function render(HostListingDashboardService $dashboard): View
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $home = $dashboard->home($user);

        return view('livewire.shell.host-home-page', [
            'dashboard' => $home,
            'metrics' => $home['metrics'],
        ])->layout('layouts.app', [
            'title' => __('shell.pages.host.home.title'),
        ]);
    }

    public function money(float|int|string|null $amount, string $currency): string
    {
        return Number::currency((float) ($amount ?: 0), $currency, app()->getLocale());
    }
}
