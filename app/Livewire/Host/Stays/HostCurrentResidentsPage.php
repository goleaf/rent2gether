<?php

namespace App\Livewire\Host\Stays;

use App\Services\Stays\HostCurrentResidentsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostCurrentResidentsPage extends Component
{
    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function render(): View
    {
        $host = auth()->user();
        $residents = $host
            ? app(HostCurrentResidentsService::class)->getCurrentResidents($host, $this->filtersForQuery())
            : null;

        return view('livewire.host.stays.host-current-residents-page', [
            'residents' => $residents,
            'filters' => $this->filters(),
            'activeFilter' => $this->filter,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function filters(): array
    {
        return [
            'all' => __('stays.filters.all'),
            'checkout_today' => __('stays.filters.checkout_today'),
            'checkout_soon' => __('stays.filters.checkout_soon'),
            'complaints' => __('stays.filters.complaints'),
            'payment_issue' => __('stays.filters.payment_issue'),
            'extension_requested' => __('stays.filters.extension_requested'),
            'relocation_requested' => __('stays.filters.relocation_requested'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function filtersForQuery(): array
    {
        return $this->filter === 'all' ? [] : ['scope' => $this->filter];
    }
}
