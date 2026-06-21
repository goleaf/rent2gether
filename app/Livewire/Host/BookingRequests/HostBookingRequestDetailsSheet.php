<?php

namespace App\Livewire\Host\BookingRequests;

use App\Models\BookingRequest;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestHostViewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostBookingRequestDetailsSheet extends Component
{
    #[Locked]
    public int $requestId;

    public function mount(int|BookingRequest $request): void
    {
        $this->requestId = $request instanceof BookingRequest ? $request->id : $request;
    }

    public function render(BookingRequestHostViewService $hostView): View
    {
        $request = BookingRequest::query()
            ->with(['guest', 'sleepingPlace:id,title,display_name'])
            ->findOrFail($this->requestId);
        $host = auth()->user();
        $view = $host instanceof User ? $hostView->buildHostView($host, $request) : [];

        return view('livewire.host.booking-requests.host-booking-request-details-sheet', [
            'request' => $request,
            'hostView' => $view,
            'total' => Number::currency((float) $request->total_amount, $request->currency, app()->getLocale()),
        ]);
    }
}
