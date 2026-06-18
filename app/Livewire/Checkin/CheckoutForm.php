<?php

namespace App\Livewire\Checkin;

use App\Models\Booking;
use App\Models\CheckoutRecord;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CheckoutForm extends Component
{
    #[Locked]
    public Booking $booking;

    public bool $keysReturned = false;

    public bool $bedInspected = false;

    public bool $hasDamage = false;

    public string $damageDescription = '';

    public bool $hasDirt = false;

    public string $dirtDescription = '';

    public string $notes = '';

    public function mount(Booking $booking): void
    {
        $this->booking = $booking;
    }

    public function submit(): void
    {
        CheckoutRecord::create([
            'booking_id' => $this->booking->id,
            'checked_out_by' => auth()->id(),
            'keys_returned' => $this->keysReturned,
            'bed_inspected' => $this->bedInspected,
            'has_damage' => $this->hasDamage,
            'damage_description' => $this->damageDescription ?: null,
            'has_dirt' => $this->hasDirt,
            'dirt_description' => $this->dirtDescription ?: null,
            'notes' => $this->notes ?: null,
            'photos' => [],
            'checked_out_at' => now(),
        ]);

        $this->booking->update([
            'guest_checked_out_at' => now(),
            'status' => 'checked_out',
        ]);

        session()->flash('success', 'Check-out recorded.');
        $this->redirect(route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $this->booking]));
    }

    public function render(): View
    {
        return view('livewire.checkin.checkout-form');
    }
}
