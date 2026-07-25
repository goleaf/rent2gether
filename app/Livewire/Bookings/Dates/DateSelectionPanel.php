<?php

namespace App\Livewire\Bookings\Dates;

use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\BookingRequest;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestCreationService;
use App\Services\Bookings\BookingDateSelectionService;
use App\Services\Bookings\BookingQuoteConversionService;
use App\Services\Bookings\BookingQuotePrivacyService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DateSelectionPanel extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkInDate = '';

    public string $checkInTime = '';

    public string $checkOutDate = '';

    public string $checkOutTime = '';

    public int $guestsCount = 1;

    public bool $earlyCheckInRequested = false;

    public bool $lateCheckOutRequested = false;

    public bool $flexibleCheckIn = false;

    public bool $flexibleCheckOut = false;

    public string $checkInComment = '';

    public string $checkOutComment = '';

    public ?int $quoteId = null;

    /** @var list<array{check_out:string,nights:int,chargeable_days:int,calendar_presence_days:int}> */
    public array $availableCheckoutDates = [];

    public function mount(
        int|SleepingPlace $sleepingPlace,
        string $checkInDate = '',
        string $checkOutDate = '',
        int $guestsCount = 1,
    ): void {
        $this->sleepingPlaceId = $sleepingPlace instanceof SleepingPlace ? $sleepingPlace->id : $sleepingPlace;
        $this->checkInDate = trim($checkInDate);
        $this->checkOutDate = trim($checkOutDate);
        $this->guestsCount = max(1, $guestsCount);

        if ($this->checkInDate !== '' && $this->checkOutDate !== '') {
            try {
                $this->recalculateQuote();
            } catch (ValidationException $exception) {
                $this->addError('checkInDate', collect($exception->errors())->flatten()->first() ?: __('booking_quotes.messages.quote_recalculate_required'));
            }

            return;
        }

        if ($this->checkInDate !== '') {
            $this->refreshAvailableCheckoutDates();
        }
    }

    public function updatedCheckInDate(): void
    {
        $this->checkOutDate = '';
        $this->quoteId = null;
        $this->refreshAvailableCheckoutDates();
    }

    public function updatedCheckOutDate(): void
    {
        $this->recalculateQuote();
    }

    public function updatedGuestsCount(): void
    {
        $this->recalculateQuote();
    }

    public function updatedEarlyCheckInRequested(): void
    {
        $this->recalculateQuote();
    }

    public function updatedLateCheckOutRequested(): void
    {
        $this->recalculateQuote();
    }

    public function updatedFlexibleCheckIn(): void
    {
        $this->recalculateQuote();
    }

    public function updatedFlexibleCheckOut(): void
    {
        $this->recalculateQuote();
    }

    public function selectCheckoutDate(string $date): void
    {
        $this->checkOutDate = $date;
        $this->recalculateQuote();
    }

    public function recalculateQuote(): void
    {
        $dates = app(BookingDateSelectionService::class);

        $this->resetValidation();

        if ($this->checkInDate === '') {
            $this->quoteId = null;
            $this->availableCheckoutDates = [];

            return;
        }

        $this->refreshAvailableCheckoutDates();

        if ($this->checkOutDate === '') {
            $this->quoteId = null;

            return;
        }

        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->addError('checkInDate', __('booking_dates.validation.login_required'));

            return;
        }

        try {
            $quote = $this->quoteId
                ? $dates->updateQuotePreview($this->quote(), $this->quotePayload())
                : $dates->createQuotePreview($guest, $this->sleepingPlace(), $this->quotePayload());

            $this->quoteId = $quote->id;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable) {
            $this->addError('checkInDate', __('booking_quotes.messages.quote_recalculate_required'));
        }
    }

    public function confirmBooking(BookingQuoteConversionService $conversion): mixed
    {
        $guest = auth()->user();

        if (! $guest instanceof User || $this->quoteId === null) {
            $this->addError('checkInDate', __('booking_quotes.messages.quote_not_available'));

            return null;
        }

        try {
            $booking = $conversion->convertToBooking($guest, $this->quote());
        } catch (ValidationException $exception) {
            $this->addError('checkInDate', collect($exception->errors())->flatten()->first() ?: __('booking_quotes.messages.quote_recalculate_required'));

            return null;
        }

        return $this->redirectRoute('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $booking,
        ], navigate: true);
    }

    public function sendRequest(BookingRequestCreationService $requests): mixed
    {
        $guest = auth()->user();

        if (! $guest instanceof User || $this->quoteId === null) {
            $this->addError('checkInDate', __('booking_quotes.messages.quote_not_available'));

            return null;
        }

        try {
            $request = $requests->createFromQuote($guest, $this->quote(), [
                'guest_message' => $this->checkInComment,
                'request_type' => BookingRequest::TYPE_REQUEST_ONLY,
                'hold_dates' => true,
            ]);

            return $this->redirectRoute('guest.booking-requests.show', [
                'locale' => app()->getLocale(),
                'request' => $request,
            ], navigate: true);
        } catch (ValidationException $exception) {
            $this->addError('checkInDate', collect($exception->errors())->flatten()->first() ?: __('booking_quotes.messages.quote_recalculate_required'));

            return null;
        }
    }

    public function render(): View
    {
        $privacy = app(BookingQuotePrivacyService::class);
        $quote = $this->quoteId ? $this->quote() : null;
        $guest = auth()->user();

        return view('livewire.bookings.dates.date-selection-panel', [
            'quote' => $quote,
            'quoteSummary' => $quote instanceof BookingQuote && $guest instanceof User
                ? $privacy->filterForGuest($guest, $quote)
                : null,
            'formattedTotal' => $quote instanceof BookingQuote ? $this->money($quote->total_payable, $quote->currency) : null,
            'formattedDeposit' => $quote instanceof BookingQuote ? $this->money($quote->deposit_amount, $quote->currency) : null,
            'isRequestOnly' => $quote instanceof BookingQuote && $quote->availability_status === 'request_only',
        ]);
    }

    public function money(mixed $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }

    private function refreshAvailableCheckoutDates(): void
    {
        $dates = app(BookingDateSelectionService::class);

        if ($this->checkInDate === '') {
            $this->availableCheckoutDates = [];

            return;
        }

        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->availableCheckoutDates = [];

            return;
        }

        $this->availableCheckoutDates = $dates
            ->availableCheckoutDates($guest, $this->sleepingPlace(), $this->checkInDate)
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function quotePayload(): array
    {
        return [
            'rental_mode' => 'nightly',
            'check_in_date' => $this->checkInDate,
            'check_in_time' => $this->checkInTime ?: null,
            'check_out_date' => $this->checkOutDate,
            'check_out_time' => $this->checkOutTime ?: null,
            'guests_count' => max(1, $this->guestsCount),
            'early_check_in_requested' => $this->earlyCheckInRequested,
            'late_check_out_requested' => $this->lateCheckOutRequested,
            'flexible_check_in' => $this->flexibleCheckIn,
            'flexible_check_out' => $this->flexibleCheckOut,
            'check_in_comment' => $this->checkInComment ?: null,
            'check_out_comment' => $this->checkOutComment ?: null,
        ];
    }

    private function sleepingPlace(): SleepingPlace
    {
        return SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'user_id',
                'status',
                'title',
                'display_name',
                'base_price',
                'base_price_per_night',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'cleaning_fee',
                'deposit_amount',
                'currency',
                'min_nights',
                'max_nights',
                'max_guests',
                'max_guests_count',
                'min_guest_age',
                'max_guest_age',
                'instant_booking_enabled',
                'requires_host_approval',
                'cancellation_policy',
            ])
            ->with([
                'room:id,property_id,status,gender_policy,gender_type,min_guest_age,max_guest_age',
                'property:id,host_user_id,status',
                'calendarSettings',
                'calendarDays',
            ])
            ->findOrFail($this->sleepingPlaceId);
    }

    private function quote(): BookingQuote
    {
        return BookingQuote::query()
            ->with(['lines', 'validationResults', 'timelineDates', 'suggestions'])
            ->findOrFail($this->quoteId);
    }
}
