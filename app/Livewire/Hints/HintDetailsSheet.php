<?php

namespace App\Livewire\Hints;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HintDetailsSheet extends Component
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
        return view('livewire.hints.hint-details-sheet');
    }
}
