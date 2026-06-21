<?php

namespace App\Livewire\Host\Stays;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostCurrentResidentsFilters extends Component
{
    public string $activeFilter = 'all';

    public function render(): View
    {
        return view('livewire.host.stays.host-current-residents-filters', [
            'activeFilter' => $this->activeFilter,
            'filters' => [
                'all' => __('stays.filters.all'),
                'checkout_today' => __('stays.filters.checkout_today'),
                'checkout_soon' => __('stays.filters.checkout_soon'),
                'complaints' => __('stays.filters.complaints'),
                'payment_issue' => __('stays.filters.payment_issue'),
            ],
        ]);
    }
}
