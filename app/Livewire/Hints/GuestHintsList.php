<?php

namespace App\Livewire\Hints;

use App\Services\Hints\GuestHintPayloadFormatter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class GuestHintsList extends Component
{
    private string $hintsPayload = '[]';

    #[Locked]
    public string $context = 'detail';

    public function mount(array $hints = [], string $context = 'detail'): void
    {
        $this->hintsPayload = app(GuestHintPayloadFormatter::class)->encodeList(array_values($hints));
        $this->context = $this->normalizeContext($context);
    }

    public function render(GuestHintPayloadFormatter $formatter): View
    {
        return view('livewire.hints.guest-hints-list', [
            'hints' => $formatter->displayList($this->hintsPayload),
        ]);
    }

    private function normalizeContext(string $context): string
    {
        return in_array($context, ['card', 'detail', 'before_booking', 'favorites', 'saved_search'], true)
            ? $context
            : 'detail';
    }
}
