<?php

namespace App\Livewire\Host\Bulk;

use Illuminate\Contracts\View\View;
use Livewire\Component;

abstract class BaseHostBulkComponent extends Component
{
    public string $section = 'panel';

    public function render(): View
    {
        return view('livewire.host.bulk.shell', [
            'section' => $this->section,
        ]);
    }
}
