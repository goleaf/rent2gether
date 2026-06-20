<?php

namespace App\Livewire\Host\Occupants;

use App\Services\HostOccupants\Data\HostOccupantSummaryData;
use App\Services\HostOccupants\HostOccupantSummaryService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

abstract class BaseHostOccupantsComponent extends Component
{
    public string $section = 'page';

    public function render(): View
    {
        return view('livewire.host.occupants.shell', [
            'section' => $this->section,
            'summary' => $this->summary(),
        ]);
    }

    private function summary(): HostOccupantSummaryData
    {
        $host = auth()->user();

        if (! $host) {
            return new HostOccupantSummaryData;
        }

        return app(HostOccupantSummaryService::class)->getSummary($host);
    }
}
