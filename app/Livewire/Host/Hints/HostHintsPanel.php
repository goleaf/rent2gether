<?php

namespace App\Livewire\Host\Hints;

use App\Models\User;
use App\Services\HostHints\HostHintService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostHintsPanel extends Component
{
    public function render(HostHintService $hints): View
    {
        $host = auth()->user();

        return view('livewire.host.hints.host-hints-panel', [
            'hints' => $host instanceof User
                ? $hints->getHintsForDashboard($host)->map->toDisplayArray(app()->getLocale())->all()
                : [],
        ]);
    }
}
