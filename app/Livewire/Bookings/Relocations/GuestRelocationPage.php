<?php

namespace App\Livewire\Bookings\Relocations;

use App\Models\Booking;
use App\Models\BookingRelocation;
use App\Services\Bookings\BookingRelocationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class GuestRelocationPage extends Component
{
    public ?int $bookingId = null;

    public ?int $relocationId = null;

    public string $variant = 'page';

    public string $reason = 'other';

    public ?string $relocationDate = null;

    public ?string $guestComment = null;

    public function mount(?Booking $booking = null, ?BookingRelocation $relocation = null): void
    {
        $this->bookingId = $booking?->id ?? $relocation?->original_booking_id;
        $this->relocationId = $relocation?->id;
        $this->relocationDate = now()->toDateString();
    }

    public function requestRelocation(BookingRelocationService $relocations): void
    {
        $booking = $this->booking();

        if (! $booking || ! Auth::user()) {
            return;
        }

        $relocation = $relocations->createFromGuestRequest(Auth::user(), $booking, [
            'reason' => $this->reason,
            'relocation_date' => $this->relocationDate ?: now()->toDateString(),
            'guest_comment' => $this->guestComment,
        ]);

        $this->relocationId = $relocation->id;
    }

    public function render(): View
    {
        return view('livewire.bookings.relocations.card', [
            'booking' => $this->booking(),
            'relocation' => $this->relocation(),
            'relocations' => $this->relocations(),
            'variant' => $this->variant,
        ]);
    }

    protected function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        return Booking::query()
            ->with(['room:id,title', 'sleepingPlace:id,display_name,title'])
            ->find($this->bookingId);
    }

    protected function relocation(): ?BookingRelocation
    {
        if (! $this->relocationId) {
            return null;
        }

        return BookingRelocation::query()
            ->with(['currentRoom:id,title', 'newRoom:id,title', 'currentSleepingPlace:id,display_name,title', 'newSleepingPlace:id,display_name,title', 'priceLines'])
            ->find($this->relocationId);
    }

    /**
     * @return Collection<int, BookingRelocation>
     */
    protected function relocations(): Collection
    {
        if (! $this->bookingId) {
            return collect();
        }

        return BookingRelocation::query()
            ->with(['currentRoom:id,title', 'newRoom:id,title', 'currentSleepingPlace:id,display_name,title', 'newSleepingPlace:id,display_name,title'])
            ->where('original_booking_id', $this->bookingId)
            ->latest('id')
            ->limit(5)
            ->get();
    }
}
