<?php

namespace App\Livewire\Host\Stays;

use App\Services\Stays\HostCurrentResidentsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostCheckoutSoonPanel extends Component
{
    public function render(): View
    {
        return view('livewire.host.stays.host-current-resident-card', [
            'variant' => 'checkout_soon',
            'stays' => auth()->user() ? app(HostCurrentResidentsService::class)->getCheckoutSoonResidents(auth()->user()) : collect(),
        ]);
    }
}
