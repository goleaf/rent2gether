<?php

namespace App\Livewire\Waitlist;

use App\Models\Bed;
use App\Models\WaitlistEntry;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class WaitlistManager extends Component
{
    #[Locked]
    public Bed $bed;

    public string $desiredCheckIn = '';

    public string $desiredCheckOut = '';

    public ?float $maxPrice = null;

    public bool $autoRequest = false;

    public bool $showForm = false;

    public function mount(Bed $bed): void
    {
        $this->bed = $bed;
        $this->maxPrice = $bed->price_per_night;
    }

    #[Computed]
    public function existingEntry()
    {
        return WaitlistEntry::where('user_id', auth()->id())
            ->where('bed_id', $this->bed->id)
            ->where('status', 'waiting')
            ->first();
    }

    public function join(): void
    {
        $this->validate([
            'desiredCheckIn' => ['required', 'date', 'after:today'],
            'desiredCheckOut' => ['required', 'date', 'after:desiredCheckIn'],
        ]);

        WaitlistEntry::create([
            'user_id' => auth()->id(),
            'bed_id' => $this->bed->id,
            'desired_check_in' => $this->desiredCheckIn,
            'desired_check_out' => $this->desiredCheckOut,
            'max_price' => $this->maxPrice,
            'auto_request' => $this->autoRequest,
            'status' => 'waiting',
        ]);

        $this->showForm = false;
        unset($this->existingEntry);
    }

    public function leave(): void
    {
        WaitlistEntry::where('user_id', auth()->id())
            ->where('bed_id', $this->bed->id)
            ->where('status', 'waiting')
            ->update(['status' => 'cancelled']);

        unset($this->existingEntry);
    }

    public function render(): View
    {
        return view('livewire.waitlist.waitlist-manager');
    }
}
