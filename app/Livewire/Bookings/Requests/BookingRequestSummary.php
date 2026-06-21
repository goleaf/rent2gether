<?php

namespace App\Livewire\Bookings\Requests;

use App\Models\BookingRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingRequestSummary extends Component
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
            ->with(['sleepingPlace:id,title,display_name', 'room:id,title'])
            ->findOrFail($this->requestId);

        return view('livewire.bookings.requests.booking-request-summary', [
            'summary' => [
                'number' => $request->request_number,
                'status' => __('booking_requests.statuses.'.$request->status),
                'type' => __('booking_requests.request_types.'.$request->request_type),
                'place' => $request->sleepingPlace?->display_name ?: $request->sleepingPlace?->title,
                'room' => $request->room?->title,
                'dates' => $request->check_in_date?->toDateString().' - '.$request->check_out_date?->toDateString(),
                'nights_count' => (int) $request->nights_count,
                'guests_count' => (int) $request->guests_count,
                'total' => Number::currency((float) $request->total_amount, $request->currency, app()->getLocale()),
                'expires_at' => $request->expires_at?->translatedFormat('d M H:i'),
            ],
        ]);
    }
}
