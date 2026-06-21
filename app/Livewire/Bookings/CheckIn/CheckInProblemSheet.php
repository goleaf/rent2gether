<?php

namespace App\Livewire\Bookings\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Services\CheckIn\BookingCheckInProblemService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class CheckInProblemSheet extends Component
{
    use LoadsBookingCheckIn;

    public string $problemType = 'other';

    public string $severity = 'medium';

    public string $description = '';

    public function report(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn && Auth::user()) {
            app(BookingCheckInProblemService::class)->reportProblem(Auth::user(), $checkIn, [
                'problem_type' => $this->problemType,
                'severity' => $this->severity,
                'description' => $this->description,
                'guest_wants_help' => true,
            ]);
            $this->refreshCheckInState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-in.card', $this->checkInViewData('problem_sheet'));
    }
}
