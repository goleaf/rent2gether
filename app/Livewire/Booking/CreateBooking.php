<?php

namespace App\Livewire\Booking;

use App\Models\Bed;
use App\Services\BookingPriceCalculator;
use App\Services\BookingService;
use App\Services\CompatibilityService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CreateBooking extends Component
{
    #[Locked]
    public Bed $bed;

    public string $checkIn = '';

    public string $checkOut = '';

    public int $guestsCount = 1;

    public string $guestMessage = '';

    public function mount(Bed $bed): void
    {
        $this->bed = $bed->load('room.property.host');
    }

    #[Computed]
    public function nights(): int
    {
        if ($this->checkIn && $this->checkOut) {
            try {
                $n = (int) now()->parse($this->checkIn)->diffInDays($this->checkOut);

                return max(0, $n);
            } catch (\Throwable) {
                return 0;
            }
        }

        return 0;
    }

    #[Computed]
    public function priceBreakdown(): ?array
    {
        if ($this->nights <= 0) {
            return null;
        }

        return app(BookingPriceCalculator::class)->calculate($this->bed, $this->checkIn, $this->checkOut);
    }

    #[Computed]
    public function compatibility(): ?array
    {
        if (! auth()->check()) {
            return null;
        }

        return app(CompatibilityService::class)->check(auth()->user(), $this->bed);
    }

    #[Computed]
    public function roomOccupancy(): array
    {
        $room = $this->bed->room;
        $totalBeds = $room->beds()->active()->count();
        $bookedBeds = 0;

        if ($this->checkIn && $this->checkOut) {
            $bookedBeds = $room->beds()
                ->active()
                ->where('id', '!=', $this->bed->id)
                ->whereHas('bookings', function ($q) {
                    $q->whereNotIn('status', ['cancelled_guest', 'cancelled_host', 'cancelled_system', 'no_show'])
                        ->whereDate('check_in', '<', $this->checkOut)
                        ->whereDate('check_out', '>', $this->checkIn);
                })
                ->count();
        }

        return [
            'total' => $totalBeds,
            'occupied' => $bookedBeds,
            'free' => $totalBeds - $bookedBeds,
            'with_you' => $bookedBeds + 1,
        ];
    }

    public function book(): void
    {
        $this->validate([
            'checkIn' => ['required', 'date', 'after_or_equal:today'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
            'guestsCount' => ['required', 'integer', 'min:1', 'max:'.$this->bed->max_guests],
        ]);

        $result = app(BookingService::class)->create(
            auth()->user(),
            $this->bed,
            $this->checkIn,
            $this->checkOut,
            $this->guestsCount,
            $this->guestMessage ?: null,
        );

        if (! $result['success']) {
            $this->addError('booking', $result['error']);

            return;
        }

        session()->flash('success', __('notifications.flash.booking_created'));
        $this->redirect(route('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $result['booking'],
        ]));
    }

    public function render(): View
    {
        return view('livewire.booking.create-booking');
    }
}
