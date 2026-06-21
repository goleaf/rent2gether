<?php

namespace App\Livewire\Host\BookingRequests;

use App\Models\BookingRequest;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestConversionService;
use App\Services\BookingRequests\BookingRequestHostResponseService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class HostBookingRequestResponsePanel extends Component
{
    #[Locked]
    public int $requestId;

    public string $message = '';

    public string $rejectionReason = 'other';

    public string $proposedCheckInTime = '';

    public string $proposedCheckOutTime = '';

    public string $proposedCheckInDate = '';

    public string $proposedCheckOutDate = '';

    public function mount(int|BookingRequest $request): void
    {
        $this->requestId = $request instanceof BookingRequest ? $request->id : $request;
    }

    public function approve(BookingRequestHostResponseService $responses): void
    {
        $this->runHostResponse(fn (User $host, BookingRequest $request) => $responses->approve($host, $request, $this->message ?: null));
    }

    public function reject(BookingRequestHostResponseService $responses): void
    {
        $this->validate(['rejectionReason' => ['required', 'string', 'max:80']]);
        $this->runHostResponse(fn (User $host, BookingRequest $request) => $responses->reject($host, $request, $this->rejectionReason));
    }

    public function askQuestion(BookingRequestHostResponseService $responses): void
    {
        $this->validate(['message' => ['required', 'string', 'max:1200']]);
        $this->runHostResponse(fn (User $host, BookingRequest $request) => $responses->askQuestion($host, $request, $this->message));
    }

    public function proposeTimeChange(BookingRequestHostResponseService $responses): void
    {
        $this->runHostResponse(fn (User $host, BookingRequest $request) => $responses->proposeTimeChange($host, $request, [
            'message' => $this->message ?: null,
            'proposed_check_in_time' => $this->proposedCheckInTime ?: null,
            'proposed_check_out_time' => $this->proposedCheckOutTime ?: null,
        ]));
    }

    public function proposeDateChange(BookingRequestHostResponseService $responses): void
    {
        $this->runHostResponse(fn (User $host, BookingRequest $request) => $responses->proposeDateChange($host, $request, [
            'message' => $this->message ?: null,
            'proposed_check_in_date' => $this->proposedCheckInDate ?: null,
            'proposed_check_out_date' => $this->proposedCheckOutDate ?: null,
        ]));
    }

    public function convert(BookingRequestConversionService $conversion): void
    {
        try {
            $conversion->convertApprovedRequestToBooking($this->request());
        } catch (ValidationException $exception) {
            $this->addError('message', collect($exception->errors())->flatten()->first() ?: __('booking_requests.validation.conversion_failed'));
        }
    }

    public function render(): View
    {
        return view('livewire.host.booking-requests.host-booking-request-response-panel', [
            'request' => $this->request(),
        ]);
    }

    private function request(): BookingRequest
    {
        return BookingRequest::query()->findOrFail($this->requestId);
    }

    private function runHostResponse(callable $callback): void
    {
        $host = auth()->user();

        if (! $host instanceof User) {
            $this->addError('message', __('booking_requests.validation.login_required'));

            return;
        }

        try {
            $callback($host, $this->request());
            $this->message = '';
        } catch (ValidationException $exception) {
            $this->addError('message', collect($exception->errors())->flatten()->first() ?: __('booking_requests.validation.host_cannot_respond'));
        }
    }
}
