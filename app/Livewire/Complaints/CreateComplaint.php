<?php

namespace App\Livewire\Complaints;

use App\Enums\ComplaintType;
use App\Models\Booking;
use App\Models\Complaint;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CreateComplaint extends Component
{
    #[Locked]
    public ?Booking $booking = null;

    public string $type = '';

    public string $description = '';

    public string $urgency = 'normal';

    public string $desiredResolution = '';

    public function mount(?Booking $booking = null): void
    {
        $this->booking = $booking;
    }

    public function submit(): void
    {
        $this->validate([
            'type' => ['required', 'string'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'urgency' => ['required', 'in:low,normal,high,critical'],
        ]);

        $reportedUserId = null;
        if ($this->booking) {
            $reportedUserId = $this->booking->guest_id === auth()->id()
                ? $this->booking->host_id
                : $this->booking->guest_id;
        }

        $complaint = Complaint::create([
            'reporter_id' => auth()->id(),
            'reported_user_id' => $reportedUserId,
            'booking_id' => $this->booking?->id,
            'property_id' => $this->booking?->property_id,
            'room_id' => $this->booking?->room_id,
            'bed_id' => $this->booking?->bed_id,
            'type' => $this->type,
            'description' => $this->description,
            'urgency' => $this->urgency,
            'desired_resolution' => $this->desiredResolution ?: null,
        ]);

        session()->flash('success', 'Complaint submitted. Reference: '.$complaint->reference);
        $this->redirect(route('guest.bookings.index', ['locale' => app()->getLocale()]));
    }

    public function complaintTypes(): array
    {
        return collect(ComplaintType::cases())->mapWithKeys(
            fn (ComplaintType $t) => [$t->value => str_replace('_', ' ', ucfirst($t->value))]
        )->all();
    }

    public function render(): View
    {
        return view('livewire.complaints.create-complaint');
    }
}
