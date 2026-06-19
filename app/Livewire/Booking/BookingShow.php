<?php

namespace App\Livewire\Booking;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\CancellationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingShow extends Component
{
    #[Locked]
    public Booking $booking;

    public string $cancelReason = '';

    public bool $showCancelModal = false;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bed.room.property', 'guest', 'host']);
    }

    #[Computed]
    public function cancellationPreview(): ?array
    {
        if (! $this->booking->isCancellable()) {
            return null;
        }

        return app(CancellationService::class)->calculateRefund($this->booking);
    }

    #[Computed]
    public function daysUntilCheckIn(): int
    {
        return max(0, (int) now()->diffInDays($this->booking->check_in, false));
    }

    public function cancel(): void
    {
        $success = app(CancellationService::class)->cancelByGuest($this->booking, $this->cancelReason ?: null);

        if ($success) {
            $this->showCancelModal = false;
            session()->flash('success', __('notifications.flash.booking_cancelled'));
            $this->booking->refresh();
        }
    }

    public function confirmCheckIn(): void
    {
        app(BookingService::class)->checkIn($this->booking);
        $this->booking->refresh();
    }

    public function confirmCheckOut(): void
    {
        app(BookingService::class)->checkOut($this->booking);
        $this->booking->refresh();
    }

    public function render(): View
    {
        return view('livewire.booking.booking-show');
    }
}
