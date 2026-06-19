<?php

namespace App\Livewire\Extensions;

use App\Models\Booking;
use App\Services\ExtensionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RequestExtension extends Component
{
    #[Locked]
    public Booking $booking;

    public string $newCheckOut = '';

    public ?array $preview = null;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking;
        $this->newCheckOut = $booking->check_out->addDays(7)->format('Y-m-d');
    }

    public function updatedNewCheckOut(): void
    {
        $this->preview = null;

        if (! $this->newCheckOut || $this->newCheckOut <= $this->booking->check_out->format('Y-m-d')) {
            return;
        }

        try {
            $service = app(ExtensionService::class);
            $extraNights = $this->booking->check_out->diffInDays($this->newCheckOut);
            $bed = $this->booking->bed;
            $pricePerNight = $bed->price_per_night;

            $this->preview = [
                'extra_nights' => $extraNights,
                'extra_amount' => round($extraNights * $pricePerNight, 2),
                'new_total' => round($this->booking->total + ($extraNights * $pricePerNight), 2),
            ];
        } catch (\Throwable) {
            $this->preview = null;
        }
    }

    public function submit(): void
    {
        $this->validate([
            'newCheckOut' => ['required', 'date', 'after:'.$this->booking->check_out->format('Y-m-d')],
        ]);

        $result = app(ExtensionService::class)->request($this->booking, $this->newCheckOut);

        if (isset($result['error'])) {
            $this->addError('newCheckOut', $result['error']);

            return;
        }

        session()->flash('success', __('notifications.flash.extension_requested'));
        $this->redirect(route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $this->booking]));
    }

    public function render(): View
    {
        return view('livewire.extensions.request-extension');
    }
}
