<?php

namespace App\Livewire\Booking;

use App\Actions\Bookings\BookingSubmit;
use App\Models\BookingGuestIntake;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Services\BookingGuestIntake\BookingGuestIntakeService;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\Pricing\PricingService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class BookingReview extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    #[Url(as: 'in', except: '')]
    public string $checkIn = '';

    #[Url(as: 'out', except: '')]
    public string $checkOut = '';

    #[Url(as: 'guests', except: 1)]
    public int $guestsCount = 1;

    public string $checkInTime = '15:00';

    public string $checkOutTime = '10:00';

    public string $arrivalTime = '';

    public string $guestMessage = '';

    public bool $profileReady = false;

    public bool $rulesAccepted = false;

    /** @var array<string, mixed>|null */
    public ?array $quote = null;

    public ?string $availabilityWarning = null;

    /** @var list<string> */
    public array $unavailableDates = [];

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->sleepingPlaceId = $sleepingPlace->id;
        $this->arrivalTime = $this->checkInTime;
        $this->refreshQuote();
    }

    public function updatedCheckIn(): void
    {
        $this->refreshQuote();
    }

    public function updatedCheckOut(): void
    {
        $this->refreshQuote();
    }

    public function updatedGuestsCount(): void
    {
        $this->refreshQuote();
    }

    public function updatedCheckInTime(string $value): void
    {
        if ($this->arrivalTime === '' || $this->arrivalTime === '15:00') {
            $this->arrivalTime = $value;
        }
    }

    #[On('booking-rules-accepted')]
    public function markRulesAccepted(bool $accepted = false): void
    {
        $this->rulesAccepted = $accepted;
    }

    public function refreshQuote(): void
    {
        $this->resetValidation();
        $this->quote = null;
        $this->availabilityWarning = null;
        $this->unavailableDates = [];

        if ($this->checkIn === '' || $this->checkOut === '') {
            return;
        }

        try {
            $checkIn = CarbonImmutable::parse($this->checkIn)->startOfDay();
            $checkOut = CarbonImmutable::parse($this->checkOut)->startOfDay();
        } catch (\Throwable) {
            $this->availabilityWarning = __('booking.flow.errors.use_valid_dates');

            return;
        }

        if ($checkIn->isBefore(CarbonImmutable::today())) {
            $this->availabilityWarning = __('booking.flow.errors.past_dates');

            return;
        }

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            $this->availabilityWarning = __('booking.flow.errors.checkout_after_checkin');

            return;
        }

        $place = $this->place();
        $nights = (int) $checkIn->diffInDays($checkOut);
        $guestsCount = max(1, $this->guestsCount);

        if ($guestsCount > $place->max_guests) {
            $this->availabilityWarning = trans_choice('booking.date_selector.errors.max_guests', (int) $place->max_guests, [
                'count' => (int) $place->max_guests,
            ]);

            return;
        }

        if ($place->min_nights && $nights < $place->min_nights) {
            $this->availabilityWarning = trans_choice('booking.date_selector.errors.min_nights', (int) $place->min_nights, [
                'count' => (int) $place->min_nights,
            ]);

            return;
        }

        if ($place->max_nights && $nights > $place->max_nights) {
            $this->availabilityWarning = trans_choice('booking.date_selector.errors.max_nights', (int) $place->max_nights, [
                'count' => (int) $place->max_nights,
            ]);

            return;
        }

        $availability = app(AvailabilityService::class);

        if (! $availability->isAvailable($place, $checkIn, $checkOut)) {
            $this->unavailableDates = $availability->unavailableDates($place, $checkIn, $checkOut);
            $this->availabilityWarning = __('booking.flow.errors.not_available');

            return;
        }

        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->availabilityWarning = __('booking.date_selector.errors.login_required');

            return;
        }

        $this->quote = app(PricingService::class)
            ->calculate($guest, $place, $checkIn, $checkOut, $guestsCount)
            ->toArray();
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'checkIn' => ['required', 'date', 'after_or_equal:today'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
            'checkInTime' => ['nullable', 'date_format:H:i'],
            'checkOutTime' => ['nullable', 'date_format:H:i'],
            'arrivalTime' => ['nullable', 'date_format:H:i'],
            'guestsCount' => ['required', 'integer', 'min:1'],
            'guestMessage' => ['nullable', 'string', 'max:1000'],
            'profileReady' => ['accepted'],
            'rulesAccepted' => ['accepted'],
        ], attributes: $this->validationAttributes());

        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        $completedIntake = $this->completedIntake($guest);
        $guestMessage = $validated['guestMessage']
            ?: $completedIntake?->host_message
            ?: $completedIntake?->auto_generated_host_message;

        $booking = app(BookingSubmit::class)->handle($guest, $this->place(), [
            'check_in' => $validated['checkIn'],
            'check_out' => $validated['checkOut'],
            'check_in_time' => $validated['checkInTime'] ?: null,
            'check_out_time' => $validated['checkOutTime'] ?: null,
            'arrival_time' => $validated['arrivalTime'] ?: null,
            'guests_count' => $validated['guestsCount'],
            'guest_message' => $guestMessage ?: null,
            'profile_ready' => $validated['profileReady'],
            'rules_accepted' => $validated['rulesAccepted'],
        ]);

        if ($completedIntake instanceof BookingGuestIntake) {
            app(BookingGuestIntakeService::class)->attachToBooking($completedIntake, $booking);
        }

        session()->flash('success', __('notifications.flash.booking_created'));

        $this->redirect(route('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $booking,
        ]), navigate: true);
    }

    public function render(): View
    {
        $place = $this->place();

        return view('livewire.booking.booking-review', [
            'place' => $place,
            'placeTitle' => $this->title($place),
            'bookingMode' => $place->instant_booking_enabled && ! $place->requires_host_approval
                ? __('booking.flow.mode.instant')
                : __('booking.flow.mode.request'),
            'profileChecklist' => $this->profileChecklist(),
        ])->layout('layouts.app', ['title' => __('booking.flow.title')]);
    }

    public function money(float|int|string $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }

    private function place(): SleepingPlace
    {
        $locales = array_values(array_unique([
            app()->getLocale(),
            config('localization.fallback_locale'),
        ]));

        return SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'type',
                'status',
                'display_name',
                'max_guests',
                'base_price_per_night',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'cleaning_fee',
                'deposit_amount',
                'currency',
                'min_nights',
                'max_nights',
                'instant_booking_enabled',
                'requires_host_approval',
            ])
            ->with([
                'translations' => fn ($query) => $query
                    ->select(['id', 'sleeping_place_id', 'locale', 'title', 'summary'])
                    ->whereIn('locale', $locales),
                'room:id,property_id,status,type,title',
                'property:id,host_user_id,status,type,title,city,district',
                'property.host:id,name',
            ])
            ->findOrFail($this->sleepingPlaceId);
    }

    private function title(SleepingPlace $place): string
    {
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $place->translations,
            app()->getLocale(),
            config('localization.fallback_locale'),
        );

        return (string) ($translation instanceof SleepingPlaceTranslation
            ? $translation->title
            : ($place->display_name ?: __('booking.bed')));
    }

    private function completedIntake(User $guest): ?BookingGuestIntake
    {
        return BookingGuestIntake::query()
            ->select(['id', 'user_id', 'booking_id', 'property_id', 'room_id', 'sleeping_place_id', 'status', 'host_message', 'auto_generated_host_message'])
            ->forUser($guest)
            ->where('sleeping_place_id', $this->sleepingPlaceId)
            ->completed()
            ->whereNull('booking_id')
            ->latest('id')
            ->first();
    }

    /**
     * @return list<array{key:string,label:string,done:bool}>
     */
    private function profileChecklist(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        $user->loadMissing(['profile', 'guestPreference', 'setting']);
        $profile = $user->profile;

        return [
            [
                'key' => 'photo',
                'label' => __('booking.flow.profile.photo'),
                'done' => filled($profile?->avatar_path) || filled($user->avatar),
            ],
            [
                'key' => 'email',
                'label' => __('booking.flow.profile.email'),
                'done' => filled($user->email_verified_at),
            ],
            [
                'key' => 'phone',
                'label' => __('booking.flow.profile.phone'),
                'done' => (bool) ($profile?->phone_verified_at || $user->phone_verified),
            ],
            [
                'key' => 'about',
                'label' => __('booking.flow.profile.about'),
                'done' => filled($profile?->about),
            ],
            [
                'key' => 'preferences',
                'label' => __('booking.flow.profile.preferences'),
                'done' => $user->guestPreference !== null,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = Lang::get('booking.flow.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }
}
