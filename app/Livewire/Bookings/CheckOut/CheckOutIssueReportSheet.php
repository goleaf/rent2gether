<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use App\Services\CheckOut\BookingCheckOutIssueService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class CheckOutIssueReportSheet extends Component
{
    use LoadsBookingCheckOut;

    public string $issueType = 'other';

    public string $severity = 'medium';

    public string $description = '';

    public bool $depositRelated = false;

    public bool $repairNeeded = false;

    public bool $cleaningNeeded = false;

    /** @var list<string> */
    public array $photoPaths = [];

    public function report(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut && Auth::user()) {
            app(BookingCheckOutIssueService::class)->reportIssue(Auth::user(), $checkOut, [
                'issue_type' => $this->issueType,
                'severity' => $this->severity,
                'description' => $this->description,
                'deposit_related' => $this->depositRelated,
                'repair_needed' => $this->repairNeeded,
                'cleaning_needed' => $this->cleaningNeeded,
                'photo_paths' => $this->photoPaths,
            ]);
            $this->refreshCheckOutState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('issue_sheet'));
    }
}
