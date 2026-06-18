<?php

namespace App\Livewire\Waitlist;

use App\Models\WaitlistEntry;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MyWaitlist extends Component
{
    #[Computed]
    public function entries()
    {
        return WaitlistEntry::where('user_id', auth()->id())
            ->where('status', 'waiting')
            ->with('bed.room.property')
            ->latest()
            ->get();
    }

    public function cancel(int $id): void
    {
        WaitlistEntry::where('id', $id)
            ->where('user_id', auth()->id())
            ->update(['status' => 'cancelled']);

        unset($this->entries);
    }

    public function render(): View
    {
        return view('livewire.waitlist.my-waitlist');
    }
}
