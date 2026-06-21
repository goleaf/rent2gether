<?php

namespace App\Livewire\Bookings\Requests;

use App\Models\BookingQuote;
use App\Models\BookingRequest;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestCreationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingRequestForm extends Component
{
    #[Locked]
    public int $quoteId;

    public string $requestType = BookingRequest::TYPE_HOST_APPROVAL;

    public string $tripPurpose = '';

    public string $plannedArrivalTime = '';

    public string $plannedDepartureTime = '';

    public string $guestMessage = '';

    public bool $hasBaggage = false;

    public bool $needsLuggageStorage = false;

    public bool $needsEarlyCheckIn = false;

    public bool $needsLateCheckout = false;

    public bool $needsResidenceRegistration = false;

    public bool $needsReportingDocuments = false;

    public bool $guestAgreedToRules = false;

    public bool $guestAgreedToCancellationPolicy = false;

    public bool $guestAgreedToDepositPolicy = false;

    public bool $holdDates = true;

    public ?int $submittedRequestId = null;

    public function mount(int|BookingQuote $quote): void
    {
        $this->quoteId = $quote instanceof BookingQuote ? $quote->id : $quote;
    }

    public function submit(BookingRequestCreationService $requests): void
    {
        $this->validate([
            'requestType' => ['required', 'in:host_approval,stay_request,preliminary_inquiry,long_term_request,same_day_urgent,request_only'],
            'tripPurpose' => ['nullable', 'string', 'max:80'],
            'plannedArrivalTime' => ['nullable', 'date_format:H:i'],
            'plannedDepartureTime' => ['nullable', 'date_format:H:i'],
            'guestMessage' => ['nullable', 'string', 'max:1200'],
            'guestAgreedToRules' => ['accepted'],
            'guestAgreedToCancellationPolicy' => ['accepted'],
            'guestAgreedToDepositPolicy' => ['accepted'],
        ], [], [
            'tripPurpose' => __('booking_requests.fields.trip_purpose'),
            'plannedArrivalTime' => __('booking_requests.fields.planned_arrival_time'),
            'guestMessage' => __('booking_requests.fields.message_to_host'),
            'guestAgreedToRules' => __('booking_requests.fields.guest_agreed_to_rules'),
            'guestAgreedToCancellationPolicy' => __('booking_requests.fields.guest_agreed_to_cancellation_policy'),
            'guestAgreedToDepositPolicy' => __('booking_requests.fields.guest_agreed_to_deposit_policy'),
        ]);

        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->addError('guestMessage', __('booking_requests.validation.login_required'));

            return;
        }

        try {
            $request = $requests->createFromQuote($guest, $this->quote(), $this->payload());
            $this->submittedRequestId = $request->id;
        } catch (ValidationException $exception) {
            $this->addError('guestMessage', collect($exception->errors())->flatten()->first() ?: __('booking_requests.validation.create_failed'));
        }
    }

    public function render(): View
    {
        $quote = $this->quote();

        return view('livewire.bookings.requests.booking-request-form', [
            'summary' => [
                'place' => $quote->sleepingPlace?->display_name ?: $quote->sleepingPlace?->title,
                'room' => $quote->room?->title,
                'dates' => $quote->check_in_date?->toDateString().' - '.$quote->check_out_date?->toDateString(),
                'nights_count' => (int) $quote->nights_count,
                'guests_count' => (int) $quote->guests_count,
                'total' => Number::currency((float) $quote->total_payable, $quote->currency, app()->getLocale()),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'request_type' => $this->requestType,
            'trip_purpose' => $this->tripPurpose ?: null,
            'planned_arrival_time' => $this->plannedArrivalTime ?: null,
            'planned_departure_time' => $this->plannedDepartureTime ?: null,
            'guest_message' => $this->guestMessage ?: null,
            'has_baggage' => $this->hasBaggage,
            'needs_luggage_storage' => $this->needsLuggageStorage,
            'needs_early_check_in' => $this->needsEarlyCheckIn,
            'needs_late_checkout' => $this->needsLateCheckout,
            'needs_residence_registration' => $this->needsResidenceRegistration,
            'needs_reporting_documents' => $this->needsReportingDocuments,
            'guest_agreed_to_rules' => $this->guestAgreedToRules,
            'guest_agreed_to_cancellation_policy' => $this->guestAgreedToCancellationPolicy,
            'guest_agreed_to_deposit_policy' => $this->guestAgreedToDepositPolicy,
            'hold_dates' => $this->holdDates,
        ];
    }

    private function quote(): BookingQuote
    {
        return BookingQuote::query()
            ->with(['sleepingPlace:id,title,display_name', 'room:id,title'])
            ->findOrFail($this->quoteId);
    }
}
