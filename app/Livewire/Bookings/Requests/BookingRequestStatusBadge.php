<?php

namespace App\Livewire\Bookings\Requests;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class BookingRequestStatusBadge extends Component
{
    public string $status = 'submitted';

    public function mount(string $status): void
    {
        $this->status = $status;
    }

    public function render(): View
    {
        return view('livewire.bookings.requests.booking-request-status-badge', [
            'label' => __('booking_requests.statuses.'.$this->status),
            'variant' => in_array($this->status, ['approved', 'converted_to_booking'], true) ? 'success' : (in_array($this->status, ['rejected', 'expired', 'dates_unavailable'], true) ? 'danger' : 'warning'),
        ]);
    }
}
