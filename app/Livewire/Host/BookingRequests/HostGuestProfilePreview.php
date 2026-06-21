<?php

namespace App\Livewire\Host\BookingRequests;

use App\Models\BookingRequest;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestHostViewService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostGuestProfilePreview extends Component
{
    #[Locked]
    public int $requestId;

    public function mount(int|BookingRequest $request): void
    {
        $this->requestId = $request instanceof BookingRequest ? $request->id : $request;
    }

    public function render(BookingRequestHostViewService $hostView): View
    {
        $request = BookingRequest::query()->with('guest')->findOrFail($this->requestId);
        $host = auth()->user();

        return view('livewire.host.booking-requests.host-guest-profile-preview', [
            'profile' => $host instanceof User ? $hostView->buildGuestProfileSnapshot($request->guest) : [],
            'rating' => $host instanceof User ? $hostView->buildGuestRatingSnapshot($request->guest) : [],
        ]);
    }
}
