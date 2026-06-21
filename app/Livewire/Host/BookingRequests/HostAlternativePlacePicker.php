<?php

namespace App\Livewire\Host\BookingRequests;

use App\Models\BookingRequest;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestAlternativeService;
use App\Services\BookingRequests\BookingRequestHostResponseService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostAlternativePlacePicker extends Component
{
    #[Locked]
    public int $requestId;

    public function mount(int|BookingRequest $request): void
    {
        $this->requestId = $request instanceof BookingRequest ? $request->id : $request;
    }

    public function offer(int $sleepingPlaceId, BookingRequestHostResponseService $responses): void
    {
        $host = auth()->user();

        if (! $host instanceof User) {
            $this->addError('alternative', __('booking_requests.validation.login_required'));

            return;
        }

        try {
            $responses->offerAlternativePlace($host, $this->request(), SleepingPlace::query()->findOrFail($sleepingPlaceId));
        } catch (ValidationException $exception) {
            $this->addError('alternative', collect($exception->errors())->flatten()->first() ?: __('booking_requests.validation.alternative_place_not_available'));
        }
    }

    public function render(BookingRequestAlternativeService $alternatives): View
    {
        $request = $this->request();

        return view('livewire.host.booking-requests.host-alternative-place-picker', [
            'places' => $alternatives->suggestSameHostAlternatives($request),
        ]);
    }

    private function request(): BookingRequest
    {
        return BookingRequest::query()->with('sleepingPlace')->findOrFail($this->requestId);
    }
}
