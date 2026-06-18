<?php

namespace App\Livewire\Host;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\CancellationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManageBooking extends Component
{
    #[Locked]
    public Booking $booking;

    public string $rejectReason = '';

    public string $cancelReason = '';

    public string $hostReply = '';

    public bool $showRejectModal = false;

    public bool $showCancelModal = false;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['guest', 'bed.room.property']);
    }

    public function approve(): void
    {
        app(BookingService::class)->confirmByHost($this->booking);
        $this->booking->refresh();
        session()->flash('success', 'Booking approved.');
    }

    public function reject(): void
    {
        app(BookingService::class)->rejectByHost($this->booking, $this->rejectReason ?: null);
        $this->booking->refresh();
        $this->showRejectModal = false;
        session()->flash('success', 'Booking rejected.');
    }

    public function cancel(): void
    {
        app(CancellationService::class)->cancelByHost($this->booking, $this->cancelReason ?: null);
        $this->booking->refresh();
        $this->showCancelModal = false;
        session()->flash('success', 'Booking cancelled with full refund.');
    }

    public function confirmCheckIn(): void
    {
        $this->booking->update(['host_confirmed_checkin_at' => now()]);
        $this->booking->refresh();
    }

    public function confirmCheckOut(): void
    {
        $this->booking->update(['host_confirmed_checkout_at' => now()]);
        app(BookingService::class)->complete($this->booking);
        $this->booking->refresh();
    }

    public function sendReply(): void
    {
        $this->validate(['hostReply' => ['required', 'string', 'max:2000']]);
        $this->booking->update(['host_reply' => $this->hostReply]);
        $this->hostReply = '';
        $this->booking->refresh();
    }

    public function render(): View
    {
        return view('livewire.host.manage-booking');
    }
}
