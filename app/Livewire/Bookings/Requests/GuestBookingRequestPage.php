<?php

namespace App\Livewire\Bookings\Requests;

use App\Models\BookingRequest;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestExpirationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class GuestBookingRequestPage extends Component
{
    #[Locked]
    public int $requestId;

    public function mount(int|BookingRequest $request): void
    {
        $this->requestId = $request instanceof BookingRequest ? $request->id : $request;
    }

    public function render(BookingRequestExpirationService $expiration): View
    {
        $guest = auth()->user();

        if ($guest instanceof User) {
            $expiration->expireDueRequestsForUser($guest);
        }

        return view('livewire.bookings.requests.guest-booking-request-page', [
            'request' => BookingRequest::query()->findOrFail($this->requestId),
        ]);
    }
}
