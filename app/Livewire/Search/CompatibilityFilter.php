<?php

namespace App\Livewire\Search;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CompatibilityFilter extends Component
{
    public string $minimumFit = 'attention';

    public bool $hideNotSuitable = true;

    public bool $showWarnings = true;

    public function apply(): void
    {
        $this->dispatch('compatibility-filter-updated', [
            'minimum_fit' => $this->minimumFit,
            'hide_not_suitable' => $this->hideNotSuitable,
            'show_warnings' => $this->showWarnings,
        ]);
    }

    public function render(): View
    {
        return view('livewire.search.compatibility-filter');
    }
}
