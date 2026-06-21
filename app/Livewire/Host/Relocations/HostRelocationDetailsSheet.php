<?php

namespace App\Livewire\Host\Relocations;

use App\Models\BookingRelocation;
use App\Services\Bookings\BookingRelocationHostResponseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class HostRelocationDetailsSheet extends Component
{
    public ?int $relocationId = null;

    public string $variant = 'details_sheet';

    public ?string $hostMessage = null;

    public function mount(?BookingRelocation $relocation = null): void
    {
        $this->relocationId = $relocation?->id;
    }

    public function approve(BookingRelocationHostResponseService $responses): void
    {
        $relocation = $this->relocation();

        if (! $relocation || ! Auth::user()) {
            return;
        }

        $responses->approve(Auth::user(), $relocation, $this->hostMessage);
    }

    public function reject(BookingRelocationHostResponseService $responses): void
    {
        $relocation = $this->relocation();

        if (! $relocation || ! Auth::user()) {
            return;
        }

        $responses->reject(Auth::user(), $relocation, $this->hostMessage ?: 'not_possible');
    }

    public function render(): View
    {
        return view('livewire.host.relocations.card', [
            'relocation' => $this->relocation(),
            'relocations' => $this->relocations(),
            'variant' => $this->variant,
        ]);
    }

    protected function relocation(): ?BookingRelocation
    {
        if (! $this->relocationId) {
            return null;
        }

        return BookingRelocation::query()
            ->with(['guest:id,name', 'currentRoom:id,title', 'newRoom:id,title', 'currentSleepingPlace:id,display_name,title', 'newSleepingPlace:id,display_name,title', 'priceLines', 'inventoryTransfers'])
            ->find($this->relocationId);
    }

    /**
     * @return Collection<int, BookingRelocation>
     */
    protected function relocations(): Collection
    {
        if (! Auth::id()) {
            return collect();
        }

        return BookingRelocation::query()
            ->with(['guest:id,name', 'currentRoom:id,title', 'newRoom:id,title', 'currentSleepingPlace:id,display_name,title', 'newSleepingPlace:id,display_name,title'])
            ->where('host_user_id', Auth::id())
            ->latest('id')
            ->limit(10)
            ->get();
    }
}
