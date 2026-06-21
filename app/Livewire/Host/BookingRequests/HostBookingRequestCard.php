<?php

namespace App\Livewire\Host\BookingRequests;

use App\Models\BookingRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostBookingRequestCard extends Component
{
    #[Locked]
    public int $requestId;

    public function mount(int|BookingRequest $request): void
    {
        $this->requestId = $request instanceof BookingRequest ? $request->id : $request;
    }

    public function render(): View
    {
        $request = BookingRequest::query()
            ->with(['guest:id,name,avatar_path', 'sleepingPlace:id,title,display_name'])
            ->findOrFail($this->requestId);

        return view('livewire.host.booking-requests.host-booking-request-card', [
            'request' => $request,
            'total' => Number::currency((float) $request->total_amount, $request->currency, app()->getLocale()),
            'statusLabel' => __('booking_requests.statuses.'.$request->status),
            'purposeLabel' => $request->trip_purpose ? __('booking_requests.trip_purposes.'.$request->trip_purpose) : __('booking_requests.empty.trip_purpose_missing'),
        ]);
    }
}
