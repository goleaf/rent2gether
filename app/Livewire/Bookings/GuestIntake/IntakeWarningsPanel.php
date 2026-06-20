<?php

namespace App\Livewire\Bookings\GuestIntake;

use App\Models\BookingGuestIntake;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class IntakeWarningsPanel extends Component
{
    #[Locked]
    public int $intakeId;

    public function mount(int $intakeId): void
    {
        $this->intakeId = $intakeId;
    }

    public function render(): View
    {
        $intake = BookingGuestIntake::query()->findOrFail($this->intakeId);

        return view('livewire.bookings.guest-intake.intake-warnings-panel', [
            'warnings' => $intake->warnings_json ?? [],
            'blockingReasons' => $intake->blocking_reasons_json ?? [],
        ]);
    }
}
