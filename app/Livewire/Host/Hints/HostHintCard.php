<?php

namespace App\Livewire\Host\Hints;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostHintCard extends Component
{
    /** @var array<string, mixed> */
    public array $hint = [];

    public string $context = 'dashboard';

    public function mount(array $hint = [], string $context = 'dashboard'): void
    {
        $this->hint = $hint;
        $this->context = $context;
    }

    public function render(): View
    {
        return view('livewire.host.hints.host-hint-card');
    }
}
