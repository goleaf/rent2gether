<?php

namespace App\Livewire\Bookings\Requests;

use App\Models\BookingRequest;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestExpirationService;
use App\Services\BookingRequests\BookingRequestPrivacyService;
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

    public function render(BookingRequestExpirationService $expiration, BookingRequestPrivacyService $privacy): View
    {
        $guest = auth()->user();
        abort_unless($guest instanceof User, 403);

        $expiration->expireDueRequestsForUser($guest);

        $request = BookingRequest::query()
            ->select(['id', 'guest_user_id'])
            ->findOrFail($this->requestId);

        abort_unless($privacy->canGuestView($guest, $request), 403);

        return view('livewire.bookings.requests.guest-booking-request-page', [
            'request' => $request,
        ])->layout('layouts.app', [
            'title' => __('booking_requests.guest_page.title'),
        ]);
    }
}
