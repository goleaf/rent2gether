<?php

namespace App\Livewire\Waitlist;

use App\Models\WaitlistItem;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditWaitlistItemSheet extends Component
{
    #[Locked]
    public int $waitlistItemId;

    public string $maxTotalPrice = '';

    public string $guestMessage = '';

    public function mount(int $waitlistItemId): void
    {
        $this->waitlistItemId = $waitlistItemId;
        $item = WaitlistItem::query()->where('user_id', auth()->id())->findOrFail($waitlistItemId);
        $this->maxTotalPrice = $item->max_total_price !== null ? (string) $item->max_total_price : '';
        $this->guestMessage = (string) $item->guest_message;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'maxTotalPrice' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'guestMessage' => ['nullable', 'string', 'max:1000'],
        ]);

        WaitlistItem::query()
            ->where('id', $this->waitlistItemId)
            ->where('user_id', auth()->id())
            ->update([
                'max_total_price' => $validated['maxTotalPrice'] === '' ? null : (float) $validated['maxTotalPrice'],
                'guest_message' => $validated['guestMessage'] ?: null,
            ]);
    }

    public function render(): View
    {
        return view('livewire.waitlist.edit-waitlist-item-sheet');
    }
}
