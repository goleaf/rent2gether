<?php

namespace App\Livewire\Host\Hints;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostHintDetailsSheet extends Component
{
    /** @var array<string, mixed> */
    public array $hint = [];

    public bool $open = false;

    public function mount(array $hint = []): void
    {
        $this->hint = $hint;
    }

    public function open(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render(): View
    {
        return view('livewire.host.hints.host-hint-details-sheet');
    }
}
