<?php

namespace App\Livewire\Hints;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestHintsList extends Component
{
    /** @var list<array<string, mixed>> */
    public array $hints = [];

    public string $context = 'detail';

    public function mount(array $hints = [], string $context = 'detail'): void
    {
        $this->hints = array_values($hints);
        $this->context = $context;
    }

    public function render(): View
    {
        return view('livewire.hints.guest-hints-list');
    }
}
