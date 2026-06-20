<?php

namespace App\Livewire\Host\Calendar;

use Illuminate\Contracts\View\View;
use Livewire\Component;

abstract class BaseHostCalendarComponent extends Component
{
    public string $section = 'page';

    public function render(): View
    {
        return view('livewire.host.calendar.shell', [
            'section' => $this->section,
        ]);
    }
}
