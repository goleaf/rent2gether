<?php

namespace App\Livewire\Shell;

use App\Actions\Bookings\AcceptBookingRequest;
use App\Actions\Bookings\DeclineBookingRequest;
use App\Actions\Bookings\SetBookingRequestExpiry;
use App\Enums\BookingStatus;
use App\Enums\MessageThreadType;
use App\Models\Booking;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\CompatibilityService;
use App\Services\MessageService;
use BackedEnum;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class HostRequestsPage extends Component
{
    use WithPagination;

    #[Url(as: 'request', except: null)]
    public ?int $selectedBookingId = null;

    public string $declineReason = '';

    public string $declineMessage = '';

    public string $hostMessage = '';

    public string $acceptMessage = '';

    public string $expiryAt = '';

    public function selectRequest(int $bookingId): void
    {
        $booking = $this->requestQuery()
            ->whereKey($bookingId)
            ->firstOrFail();

        $this->selectedBookingId = $booking->id;
        $this->declineReason = '';
        $this->declineMessage = '';
        $this->hostMessage = '';
        $this->acceptMessage = '';
        $this->expiryAt = $booking->availability_hold_expires_at?->format('Y-m-d\TH:i')
            ?: now()->addDay()->format('Y-m-d\TH:i');

        $this->resetErrorBag();
    }

    public function closeDetail(): void
    {
        $this->selectedBookingId = null;
        $this->declineReason = '';
        $this->declineMessage = '';
        $this->hostMessage = '';
        $this->acceptMessage = '';
        $this->expiryAt = '';
        $this->resetErrorBag();
    }

    public function acceptSelected(): void
    {
        $booking = $this->selectedRequestOrFail();

        app(AcceptBookingRequest::class)->handle(
            host: $this->hostUser(),
            booking: $booking,
            paymentDeadline: $this->expiryAt !== '' ? $this->expiryAt : null,
            hostMessage: trim($this->acceptMessage) !== '' ? trim($this->acceptMessage) : null,
        );

        session()->flash('host-request-status', __('notifications.flash.host_request_accepted'));
        $this->closeDetail();
        $this->resetPage();
    }

    public function declineSelected(): void
    {
        $this->validate([
            'declineReason' => ['required', Rule::in(DeclineBookingRequest::reasonKeys())],
            'declineMessage' => ['nullable', 'string', 'max:1000'],
        ], attributes: $this->validationAttributes());

        $booking = $this->selectedRequestOrFail();

        app(DeclineBookingRequest::class)->handle(
            host: $this->hostUser(),
            booking: $booking,
            reason: $this->declineReason,
            message: trim($this->declineMessage) !== '' ? trim($this->declineMessage) : null,
        );

        session()->flash('host-request-status', __('notifications.flash.host_request_declined'));
        $this->closeDetail();
        $this->resetPage();
    }

    public function sendMessage(): void
    {
        $validated = $this->validate([
            'hostMessage' => ['required', 'string', 'min:2', 'max:1000'],
        ], attributes: $this->validationAttributes());

        $booking = $this->selectedRequestOrFail();
        $host = $this->hostUser();
        $guest = $booking->guest;

        if (! $guest instanceof User) {
            throw ValidationException::withMessages([
                'hostMessage' => __('host.requests.errors.guest_missing'),
            ]);
        }

        $thread = app(MessageService::class)->getOrCreateThread(
            guest: $guest,
            host: $host,
            type: MessageThreadType::Booking,
            booking: $booking,
            property: $booking->property,
            sleepingPlace: $booking->sleepingPlace,
        );

        app(MessageService::class)->send($thread, $host, $validated['hostMessage']);

        $this->hostMessage = '';
        session()->flash('host-request-status', __('notifications.flash.host_request_message_sent'));
    }

    public function saveExpiry(): void
    {
        $this->validate([
            'expiryAt' => ['required', 'date', 'after:now'],
        ], attributes: $this->validationAttributes());

        app(SetBookingRequestExpiry::class)->handle(
            host: $this->hostUser(),
            booking: $this->selectedRequestOrFail(),
            expiresAt: $this->expiryAt,
        );

        session()->flash('host-request-status', __('notifications.flash.host_request_expiry_saved'));
    }

    public function render(): View
    {
        $selectedRequest = $this->selectedRequest();

        return view('livewire.shell.host-requests-page', [
            'page' => Lang::get('shell.pages.host.requests'),
            'requests' => $this->requests(),
            'selectedRequest' => $selectedRequest,
            'declineReasons' => $this->declineReasons(),
            'guestSummary' => $selectedRequest ? $this->guestSummary($selectedRequest) : null,
            'compatibility' => $selectedRequest ? $this->compatibilityFor($selectedRequest) : null,
        ])->layout('layouts.app', [
            'title' => __('shell.pages.host.requests.title'),
        ]);
    }

    private function requests(): Paginator
    {
        return $this->requestQuery()
            ->orderBy('created_at')
            ->simplePaginate(8);
    }

    private function selectedRequest(): ?Booking
    {
        if ($this->selectedBookingId === null) {
            return null;
        }

        return $this->requestQuery()
            ->with([
                'priceLines:id,booking_id,type,label_key,amount,currency,is_refundable',
                'statusHistories:id,booking_id,from_status,to_status,changed_by_user_id,note,created_at',
            ])
            ->whereKey($this->selectedBookingId)
            ->first();
    }

    private function selectedRequestOrFail(): Booking
    {
        $booking = $this->selectedRequest();

        if (! $booking instanceof Booking) {
            throw ValidationException::withMessages([
                'booking' => __('host.requests.errors.request_missing'),
            ]);
        }

        return $booking;
    }

    private function requestQuery(): Builder
    {
        return Booking::query()
            ->select([
                'id',
                'reference',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'check_out_time',
                'arrival_time',
                'nights',
                'nights_count',
                'calendar_days_count',
                'guests_count',
                'currency',
                'total',
                'total_amount',
                'deposit',
                'deposit_amount',
                'guest_message',
                'host_response',
                'availability_hold_expires_at',
                'payment_deadline_at',
                'created_at',
            ])
            ->where('host_user_id', $this->hostUser()->id)
            ->whereIn('status', AcceptBookingRequest::requestStatuses())
            ->with([
                'guest:id,name,email,email_verified_at,phone_verified,avatar,languages,rating_as_guest,completed_stays_count,complaints_count,identity_verified,identity_verified_at,travel_purpose',
                'guest.profile:id,user_id,display_name,avatar_path,phone_verified_at,email_verified_at,identity_verified_at,about,languages_json,travel_purpose,prefers_quiet,sleep_schedule,social_level,smokes,has_pets,allergies,rating_average,reviews_count,complaints_count',
                'guest.guestPreference:id,user_id,avoids_smoking,avoids_pets,avoids_mixed_room,needs_quiet_hours,wants_locker,wants_lower_bunk,needs_workspace,needs_accessibility,allergies,sleep_schedule,social_level,baggage_size',
                'guest.setting:id,user_id,locale',
                'sleepingPlace:id,room_id,property_id,type,status,display_name,place_number,bunk_level,has_locker,is_accessible,has_luggage_space,base_price_per_night,currency,max_guests',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title,summary',
                'sleepingPlace.room:id,property_id,title,type,gender_type,gender_policy,beds_count,max_guests,occupied_places_count,has_desk,has_chair,noise_level,rules,status',
                'sleepingPlace.room.property:id,host_user_id,title,type,city,district,rules,amenities,distance_to_transport_meters,status',
                'sleepingPlace.property:id,host_user_id,title,type,city,district,rules,amenities,distance_to_transport_meters,status',
            ]);
    }

    /**
     * @return array<string, string>
     */
    private function declineReasons(): array
    {
        return collect(DeclineBookingRequest::reasonKeys())
            ->mapWithKeys(fn (string $key): array => [$key => __('host.requests.decline_reasons.'.$key)])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function guestSummary(Booking $booking): array
    {
        $guest = $booking->guest;
        $profile = $guest?->profile;

        return [
            'name' => $profile?->display_name ?: $guest?->name ?: __('host.requests.profile.guest_fallback'),
            'avatar_path' => $profile?->avatar_path ?: $guest?->avatar,
            'about' => $profile?->about,
            'languages' => $this->languages($profile?->languages_json ?: $guest?->languages),
            'rating' => $this->ratingLabel($profile?->rating_average ?: $guest?->rating_as_guest),
            'reviews_count' => (int) ($profile?->reviews_count ?? 0),
            'previous_stays_count' => (int) ($guest?->completed_stays_count ?? 0),
            'complaints_count' => (int) ($profile?->complaints_count ?? $guest?->complaints_count ?? 0),
            'travel_purpose' => $this->travelPurpose($profile?->travel_purpose ?: $guest?->travel_purpose),
            'verification' => $this->verificationItems($booking),
            'preferences' => $this->preferenceItems($booking),
        ];
    }

    /**
     * @return array{score:int,fit_level:string,warnings:list<string>}
     */
    private function compatibilityFor(Booking $booking): array
    {
        if (! $booking->guest instanceof User || ! $booking->sleepingPlace instanceof SleepingPlace) {
            return [
                'score' => 0,
                'fit_level' => __('host.requests.compatibility.not_ready'),
                'warnings' => [],
            ];
        }

        $result = app(CompatibilityService::class)->check($booking->guest, $booking->sleepingPlace);

        return [
            'score' => (int) $result['score'],
            'fit_level' => __('host.requests.fit_levels.'.$result['fit_level']),
            'warnings' => $result['warning_reasons'],
        ];
    }

    /**
     * @return list<array{label:string,verified:bool}>
     */
    private function verificationItems(Booking $booking): array
    {
        $guest = $booking->guest;
        $profile = $guest?->profile;

        return [
            [
                'label' => __('host.requests.profile.email_verified'),
                'verified' => (bool) ($profile?->email_verified_at ?: $guest?->email_verified_at),
            ],
            [
                'label' => __('host.requests.profile.phone_verified'),
                'verified' => (bool) ($profile?->phone_verified_at ?: $guest?->phone_verified),
            ],
            [
                'label' => __('host.requests.profile.identity_verified'),
                'verified' => (bool) ($profile?->identity_verified_at ?: $guest?->identity_verified_at ?: $guest?->identity_verified),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function preferenceItems(Booking $booking): array
    {
        $preferences = $booking->guest?->guestPreference;
        $profile = $booking->guest?->profile;

        if (! $preferences && ! $profile) {
            return [__('host.requests.profile.no_preferences')];
        }

        $items = collect([
            'avoids_smoking' => (bool) ($preferences?->avoids_smoking || $profile?->smokes === false),
            'avoids_pets' => (bool) ($preferences?->avoids_pets),
            'avoids_mixed_room' => (bool) ($preferences?->avoids_mixed_room),
            'needs_quiet_hours' => (bool) ($preferences?->needs_quiet_hours || $profile?->prefers_quiet),
            'wants_locker' => (bool) ($preferences?->wants_locker),
            'wants_lower_bunk' => (bool) ($preferences?->wants_lower_bunk),
            'needs_workspace' => (bool) ($preferences?->needs_workspace),
            'needs_accessibility' => (bool) ($preferences?->needs_accessibility),
        ])
            ->filter()
            ->keys()
            ->map(fn (string $key): string => __('host.requests.preference_labels.'.$key));

        $allergies = trim((string) ($preferences?->allergies ?: $profile?->allergies));

        if ($allergies !== '') {
            $items->push(__('host.requests.preference_labels.allergies', ['value' => $allergies]));
        }

        $sleepSchedule = $preferences?->sleep_schedule ?: $profile?->sleep_schedule;

        if ($sleepSchedule) {
            $items->push(__('host.requests.preference_labels.sleep_schedule', [
                'value' => __('host.requests.preference_values.sleep_schedule.'.$sleepSchedule),
            ]));
        }

        $socialLevel = $preferences?->social_level ?: $profile?->social_level;

        if ($socialLevel) {
            $items->push(__('host.requests.preference_labels.social_level', [
                'value' => __('host.requests.preference_values.social_level.'.$socialLevel),
            ]));
        }

        return $items->isNotEmpty()
            ? $items->values()->all()
            : [__('host.requests.profile.no_preferences')];
    }

    /**
     * @param  array<int, mixed>|string|null  $languages
     * @return list<string>
     */
    private function languages(array|string|null $languages): array
    {
        if (is_string($languages)) {
            $languages = array_filter(array_map('trim', explode(',', $languages)));
        }

        if (! is_array($languages)) {
            return [];
        }

        return collect($languages)
            ->filter()
            ->map(fn (mixed $language): string => mb_strtoupper((string) $language))
            ->values()
            ->all();
    }

    private function ratingLabel(mixed $rating): string
    {
        $rating = (float) $rating;

        return $rating > 0
            ? number_format($rating, 1)
            : __('host.requests.profile.new_guest');
    }

    private function travelPurpose(?string $purpose): string
    {
        if (! $purpose) {
            return __('host.requests.profile.not_shared');
        }

        $key = 'host.requests.travel_purpose.'.$purpose;

        return __($key) !== $key ? __($key) : $purpose;
    }

    public function placeTitle(Booking $booking): string
    {
        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace) {
            return __('host.requests.place_fallback');
        }

        $translation = $place->translations
            ->firstWhere('locale', app()->getLocale())
            ?: $place->translations->firstWhere('locale', (string) config('app.fallback_locale', 'en'))
            ?: $place->translations->first();

        return $translation?->title
            ?: $place->display_name
            ?: __('host.requests.place_fallback');
    }

    public function guestName(Booking $booking): string
    {
        return $booking->guest?->profile?->display_name
            ?: $booking->guest?->name
            ?: __('host.requests.profile.guest_fallback');
    }

    public function placeMeta(Booking $booking): string
    {
        $place = $booking->sleepingPlace;
        $room = $place?->room;
        $property = $place?->property ?: $room?->property;

        return collect([
            $this->valueLabel('listing.room_types.', $room?->type),
            $this->valueLabel('listing.sleeping_place_types.', $place?->type),
            $property?->district ?: $property?->city,
        ])
            ->filter()
            ->implode(' · ');
    }

    public function dateSummary(Booking $booking): string
    {
        return __('host.requests.date_summary', [
            'check_in' => $booking->check_in_date?->format('d M Y'),
            'check_out' => $booking->check_out_date?->format('d M Y'),
        ]);
    }

    public function nightsSummary(Booking $booking): string
    {
        return trans_choice('host.requests.nights_summary', (int) $booking->nights_count, [
            'count' => (int) $booking->nights_count,
            'days' => (int) $booking->calendar_days_count,
        ]);
    }

    public function money(mixed $amount, ?string $currency): string
    {
        return number_format((float) $amount, 2).' '.($currency ?: 'EUR');
    }

    public function statusLabel(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->label()
            : __('statuses.booking.'.$booking->status);
    }

    public function expiryLabel(Booking $booking): string
    {
        return $booking->availability_hold_expires_at
            ? __('host.requests.expires_at', ['time' => $booking->availability_hold_expires_at->format('d M H:i')])
            : __('host.requests.no_expiry');
    }

    private function valueLabel(string $prefix, mixed $value): ?string
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        $key = $prefix.$value;

        return __($key) !== $key ? __($key) : $value;
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = Lang::get('host.requests.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }

    private function hostUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
