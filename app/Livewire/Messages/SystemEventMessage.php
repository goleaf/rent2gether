<?php

namespace App\Livewire\Messages;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class SystemEventMessage extends Component
{
    private string $translationKey = '';

    /** @var array<string, mixed> */
    private array $params = [];

    /**
     * @param  array<string, mixed>  $params
     */
    public function mount(string $translationKey = '', array $params = []): void
    {
        $this->translationKey = $translationKey;
        $this->params = $params;
    }

    public function render(): View
    {
        return view('livewire.messages.system-event-message', [
            'params' => $this->params,
            'translationKey' => $this->translationKey,
        ]);
    }
}
