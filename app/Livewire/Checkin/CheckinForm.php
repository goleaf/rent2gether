<?php

namespace App\Livewire\Checkin;

use App\Models\Booking;
use App\Models\CheckinRecord;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CheckinForm extends Component
{
    #[Locked]
    public Booking $booking;

    public bool $keysReceived = false;

    public bool $bedInspected = false;

    public bool $rulesExplained = false;

    public bool $conditionAcceptable = true;

    public string $notes = '';

    public function mount(Booking $booking): void
    {
        $this->booking = $booking;
    }

    public function submit(): void
    {
        CheckinRecord::create([
            'booking_id' => $this->booking->id,
            'checked_in_by' => auth()->id(),
            'keys_received' => $this->keysReceived,
            'bed_inspected' => $this->bedInspected,
            'rules_explained' => $this->rulesExplained,
            'condition_acceptable' => $this->conditionAcceptable,
            'notes' => $this->notes ?: null,
            'photos' => [],
            'checked_in_at' => now(),
        ]);

        $this->booking->update([
            'guest_checked_in_at' => now(),
            'status' => 'checked_in',
        ]);

        session()->flash('success', 'Check-in recorded.');
        $this->redirect(route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $this->booking]));
    }

    public function render(): View
    {
        return view('livewire.checkin.checkin-form');
    }
}
