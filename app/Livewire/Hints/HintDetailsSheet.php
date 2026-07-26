<?php

namespace App\Livewire\Hints;

use App\Services\Hints\GuestHintPayloadFormatter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HintDetailsSheet extends Component
{
    #[Locked]
    public string $hintPayload = '{}';

    public bool $open = false;

    public function mount(array $hint = []): void
    {
        $this->hintPayload = app(GuestHintPayloadFormatter::class)->encodeOne($hint);
    }

    public function open(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render(GuestHintPayloadFormatter $formatter): View
    {
        return view('livewire.hints.hint-details-sheet', [
            'hint' => $formatter->displayOne($this->hintPayload),
        ]);
    }
}
