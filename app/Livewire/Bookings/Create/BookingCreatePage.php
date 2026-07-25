<?php

namespace App\Livewire\Bookings\Create;

use App\Livewire\Bookings\Concerns\BuildsBookingViewData;
use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Services\Bookings\BookingCreationService;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class BookingCreatePage extends Component
{
    use BuildsBookingViewData;

    #[Locked]
    public ?int $quoteId = null;

    #[Locked]
    public ?int $bookingId = null;

    #[Locked]
    public ?int $sleepingPlaceId = null;

    #[Url(as: 'in', except: '')]
    public string $checkInDate = '';

    #[Url(as: 'out', except: '')]
    public string $checkOutDate = '';

    #[Url(as: 'guests', except: 1)]
    public int $guestsCount = 1;

    public int $step = 1;

    public function mount(
        ?SleepingPlace $sleepingPlace = null,
        BookingQuote|int|null $quoteId = null,
        Booking|int|null $bookingId = null,
    ): void {
        if ($sleepingPlace instanceof SleepingPlace) {
            $this->sleepingPlaceId = $sleepingPlace->id;
        }

        if ($quoteId instanceof BookingQuote) {
            $this->quoteId = $quoteId->id;
        }

        if (is_int($quoteId)) {
            $this->quoteId = $quoteId;
        }

        if ($bookingId instanceof Booking) {
            $this->bookingId = $bookingId->id;
        }

        if (is_int($bookingId)) {
            $this->bookingId = $bookingId;
        }
    }

    public function confirmInstantBooking(BookingCreationService $creation): void
    {
        if (! $this->quoteId) {
            return;
        }

        $quote = BookingQuote::query()
            ->select(['id', 'user_id'])
            ->with('guest:id,name,email')
            ->findOrFail($this->quoteId);
        $booking = $creation->createInstantBooking($quote->guest, $quote, [
            'guest_agreed_to_rules' => true,
        ]);

        $this->bookingId = $booking->id;
        $this->step = 5;
    }

    public function render(): View
    {
        $booking = $this->bookingId ? $this->loadBooking($this->bookingId) : null;
        $quote = $this->quoteId && ! $booking
            ? BookingQuote::query()
                ->select(['id', 'quote_number', 'status', 'check_in_date', 'check_out_date', 'nights_count', 'total_payable', 'deposit_amount', 'currency'])
                ->findOrFail($this->quoteId)
            : null;
        $sleepingPlace = $this->sleepingPlaceId && ! $booking && ! $quote
            ? $this->loadSleepingPlace($this->sleepingPlaceId)
            : null;

        return view('livewire.bookings.create.booking-create-page', [
            'summary' => $booking ? $this->bookingSummary($booking) : null,
            'quote' => $quote ? [
                'quote_number' => $quote->quote_number,
                'total_payable' => Number::currency((float) $quote->total_payable, $quote->currency, app()->getLocale()),
            ] : null,
            'sleepingPlaceId' => $sleepingPlace?->id,
            'sleepingPlaceTitle' => $sleepingPlace ? $this->sleepingPlaceTitle($sleepingPlace) : null,
        ])->layout('layouts.app', [
            'title' => __('bookings.create.title'),
        ]);
    }

    private function loadSleepingPlace(int $sleepingPlaceId): SleepingPlace
    {
        $locales = array_values(array_unique(array_filter([
            app()->getLocale(),
            config('localization.fallback_locale'),
        ])));

        return SleepingPlace::query()
            ->select(['id', 'title', 'display_name'])
            ->with([
                'translations' => fn ($query) => $query
                    ->select(['id', 'sleeping_place_id', 'locale', 'title'])
                    ->whereIn('locale', $locales),
            ])
            ->findOrFail($sleepingPlaceId);
    }

    private function sleepingPlaceTitle(SleepingPlace $sleepingPlace): string
    {
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $sleepingPlace->translations,
            app()->getLocale(),
            config('localization.fallback_locale'),
        );

        return (string) ($translation instanceof SleepingPlaceTranslation
            ? $translation->title
            : ($sleepingPlace->display_name ?: $sleepingPlace->title));
    }
}
