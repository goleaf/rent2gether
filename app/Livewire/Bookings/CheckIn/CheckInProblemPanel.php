<?php

namespace App\Livewire\Bookings\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use App\Models\BookingCheckInProblemReport;
use App\Services\CheckIn\BookingCheckInProblemService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class CheckInProblemPanel extends Component
{
    use LoadsBookingCheckIn;

    public function markResolved(int $reportId): void
    {
        $report = BookingCheckInProblemReport::query()->findOrFail($reportId);

        if (Auth::user()) {
            app(BookingCheckInProblemService::class)->markResolved(Auth::user(), $report);
            $this->refreshCheckInState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-in.card', $this->checkInViewData('problem_panel'));
    }
}
