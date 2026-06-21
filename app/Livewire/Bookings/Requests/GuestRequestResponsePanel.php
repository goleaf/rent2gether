<?php

namespace App\Livewire\Bookings\Requests;

use App\Models\BookingRequest;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestGuestResponseService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class GuestRequestResponsePanel extends Component
{
    #[Locked]
    public int $requestId;

    public string $message = '';

    public function mount(int|BookingRequest $request): void
    {
        $this->requestId = $request instanceof BookingRequest ? $request->id : $request;
    }

    public function answer(BookingRequestGuestResponseService $responses): void
    {
        $this->validate(['message' => ['required', 'string', 'max:1200']]);
        $this->runResponse(fn (User $guest, BookingRequest $request) => $responses->answerQuestion($guest, $request, $this->message));
    }

    public function accept(BookingRequestGuestResponseService $responses): void
    {
        $this->runResponse(fn (User $guest, BookingRequest $request) => $responses->acceptProposal($guest, $request, ['message' => $this->message]));
    }

    public function reject(BookingRequestGuestResponseService $responses): void
    {
        $this->validate(['message' => ['required', 'string', 'max:1200']]);
        $this->runResponse(fn (User $guest, BookingRequest $request) => $responses->rejectProposal($guest, $request, $this->message));
    }

    public function withdraw(BookingRequestGuestResponseService $responses): void
    {
        $this->runResponse(fn (User $guest, BookingRequest $request) => $responses->withdrawRequest($guest, $request, $this->message ?: null));
    }

    public function render(): View
    {
        $request = BookingRequest::query()->findOrFail($this->requestId);

        return view('livewire.bookings.requests.guest-request-response-panel', [
            'canRespond' => $request->status === BookingRequest::STATUS_WAITING_GUEST_RESPONSE,
            'canWithdraw' => ! in_array($request->status, [
                BookingRequest::STATUS_REJECTED,
                BookingRequest::STATUS_EXPIRED,
                BookingRequest::STATUS_WITHDRAWN_BY_GUEST,
                BookingRequest::STATUS_CONVERTED_TO_BOOKING,
            ], true),
        ]);
    }

    private function request(): BookingRequest
    {
        return BookingRequest::query()->findOrFail($this->requestId);
    }

    private function runResponse(callable $callback): void
    {
        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->addError('message', __('booking_requests.validation.login_required'));

            return;
        }

        try {
            $callback($guest, $this->request());
            $this->message = '';
        } catch (ValidationException $exception) {
            $this->addError('message', collect($exception->errors())->flatten()->first() ?: __('booking_requests.validation.guest_cannot_respond'));
        }
    }
}
