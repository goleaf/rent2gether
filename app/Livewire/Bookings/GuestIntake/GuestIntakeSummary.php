<?php

namespace App\Livewire\Bookings\GuestIntake;

use App\Models\BookingGuestIntake;
use App\Services\BookingGuestIntake\BookingGuestIntakeSummaryService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class GuestIntakeSummary extends Component
{
    #[Locked]
    public int $intakeId;

    public function mount(int $intakeId): void
    {
        $this->intakeId = $intakeId;
    }

    public function render(BookingGuestIntakeSummaryService $summaryService): View
    {
        $intake = BookingGuestIntake::query()->findOrFail($this->intakeId);

        return view('livewire.bookings.guest-intake.guest-intake-summary', [
            'summary' => $summaryService->buildGuestReviewSummary($intake),
        ]);
    }
}
