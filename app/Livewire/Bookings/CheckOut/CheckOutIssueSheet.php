<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use App\Services\CheckOut\BookingCheckOutIssueService;
use Illuminate\View\View;
use Livewire\Component;

class CheckOutIssueSheet extends Component
{
    use LoadsBookingCheckOut;

    public string $issueType = 'damage';

    public string $severity = 'medium';

    public string $description = '';

    public bool $depositRelated = false;

    public function report(): void
    {
        $checkOut = $this->checkOut();

        if ($checkOut && auth()->user()) {
            app(BookingCheckOutIssueService::class)->reportIssue(auth()->user(), $checkOut, [
                'issue_type' => $this->issueType,
                'severity' => $this->severity,
                'description' => $this->description,
                'deposit_related' => $this->depositRelated,
            ]);
            $this->refreshCheckOutState();
        }
    }

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('issue_sheet'));
    }
}
