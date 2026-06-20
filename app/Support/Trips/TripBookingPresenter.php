<?php

namespace App\Support\Trips;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\PropertyTranslation;
use App\Models\RoomTranslation;
use App\Models\Rule;
use App\Models\SleepingPlaceTranslation;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

final readonly class TripBookingPresenter
{
    public function __construct(
        private LocalizedModelContentResolver $translations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function card(Booking $booking): array
    {
        return [
            'title' => $this->sleepingPlaceTitle($booking),
            'property' => $this->propertyTitle($booking),
            'city' => $this->city($booking),
            'dates' => $this->dateRange($booking),
            'nights' => trans_choice('booking.nights_count', (int) $booking->nights_count, ['count' => (int) $booking->nights_count]),
            'status' => $booking->status?->label() ?? __('booking.trips.status_unknown'),
            'total' => $this->money($booking->total_amount ?: $booking->total, $booking->currency ?: 'EUR'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(Booking $booking): array
    {
        $address = $this->canShowAddress($booking) ? $this->address($booking) : null;

        return [
            'title' => $this->sleepingPlaceTitle($booking),
            'property' => $this->propertyTitle($booking),
            'room' => $this->roomTitle($booking),
            'city' => $this->city($booking),
            'dates' => $this->dateRange($booking),
            'nights' => trans_choice('booking.nights_count', (int) $booking->nights_count, ['count' => (int) $booking->nights_count]),
            'check_in_time' => $this->time($booking->check_in_time),
            'check_out_time' => $this->time($booking->check_out_time),
            'address' => $address,
            'address_hidden' => $address === null,
            'host_name' => $this->hostName($booking),
            'host_contact' => $this->hostContact($booking),
            'instructions' => $this->instructions($booking),
            'wifi' => $this->wifiInfo($booking),
            'rules' => $this->rules($booking),
            'line_items' => $this->lineItems($booking),
            'deposit_status' => $this->depositStatus($booking),
            'actions' => $this->actions($booking),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function currentStay(Booking $booking): array
    {
        $checkout = $booking->check_out_date ?: $booking->check_out;
        $remaining = $checkout ? max(0, now()->startOfDay()->diffInDays($checkout, false)) : 0;

        return [
            ...$this->detail($booking),
            'room_number' => $booking->room?->room_number ?: __('booking.trips.not_provided'),
            'place_number' => $booking->sleepingPlace?->place_number ?: __('booking.trips.not_provided'),
            'nights_remaining' => trans_choice('booking.trips.current.nights_remaining_count', $remaining, ['count' => $remaining]),
            'checkout_reminder' => $checkout
                ? __('booking.trips.current.checkout_reminder_date', ['date' => $checkout->translatedFormat('d M Y')])
                : __('booking.trips.current.checkout_reminder_missing'),
        ];
    }

    public function canShowAddress(Booking $booking): bool
    {
        $property = $booking->property;

        if (! $property) {
            return false;
        }

        if ($property->show_exact_address_before_booking || $property->show_exact_address) {
            return true;
        }

        if (! $property->show_exact_address_after_payment) {
            return false;
        }

        return $this->paymentStatusValue($booking) === PaymentStatus::Paid->value
            && in_array($this->statusValue($booking), $this->addressAllowedStatuses(), true);
    }

    /**
     * @return list<string>
     */
    public static function activeStayStatuses(): array
    {
        return [
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
            BookingStatus::LeavingSoon->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function upcomingStatuses(): array
    {
        return [
            BookingStatus::AwaitingHostApproval->value,
            BookingStatus::AwaitingPayment->value,
            BookingStatus::PendingHostConfirmation->value,
            BookingStatus::PendingGuestResponse->value,
            BookingStatus::PendingPayment->value,
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function pastStatuses(): array
    {
        return [
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::AwaitingReview->value,
            BookingStatus::Closed->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function cancelledStatuses(): array
    {
        return [
            BookingStatus::DeclinedByHost->value,
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::Expired->value,
            BookingStatus::CancelledByGuest->value,
            BookingStatus::CancelledByHost->value,
            BookingStatus::CancelledBySystem->value,
            BookingStatus::CancelledByService->value,
            BookingStatus::NoShow->value,
            BookingStatus::HostNoShow->value,
        ];
    }

    /**
     * @return list<string>
     */
    private function addressAllowedStatuses(): array
    {
        return [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            ...self::activeStayStatuses(),
            ...self::pastStatuses(),
        ];
    }

    private function sleepingPlaceTitle(Booking $booking): string
    {
        $place = $booking->sleepingPlace;

        if (! $place) {
            return (string) ($booking->bed?->title ?: __('booking.payment_page.summary.unnamed_place'));
        }

        $translation = $this->resolve($place->translations);

        if ($translation instanceof SleepingPlaceTranslation && filled($translation->title)) {
            return $translation->title;
        }

        return $place->display_name ?: $place->place_number ?: __('booking.payment_page.summary.unnamed_place');
    }

    private function roomTitle(Booking $booking): string
    {
        $room = $booking->room ?: $booking->bed?->room;

        if (! $room) {
            return __('booking.room');
        }

        $translation = $this->resolve($room->translations);

        if ($translation instanceof RoomTranslation && filled($translation->title)) {
            return $translation->title;
        }

        return $room->title ?: $room->room_number ?: __('booking.room');
    }

    private function propertyTitle(Booking $booking): string
    {
        $property = $booking->property ?: $booking->bed?->room?->property;

        if (! $property) {
            return __('booking.property');
        }

        $translation = $this->resolve($property->translations);

        if ($translation instanceof PropertyTranslation && filled($translation->title)) {
            return $translation->title;
        }

        return $property->title ?: __('booking.property');
    }

    private function city(Booking $booking): string
    {
        $property = $booking->property ?: $booking->bed?->room?->property;

        return $property?->city ?: __('booking.trips.not_provided');
    }

    private function dateRange(Booking $booking): string
    {
        $checkIn = $booking->check_in_date ?: $booking->check_in;
        $checkOut = $booking->check_out_date ?: $booking->check_out;

        if (! $checkIn || ! $checkOut) {
            return __('booking.trips.not_provided');
        }

        return $checkIn->translatedFormat('d M Y').' - '.$checkOut->translatedFormat('d M Y');
    }

    private function time(mixed $time): string
    {
        return $time ? $time->format('H:i') : __('booking.trips.not_provided');
    }

    private function address(Booking $booking): ?string
    {
        $property = $booking->property ?: $booking->bed?->room?->property;

        if (! $property) {
            return null;
        }

        $parts = collect([
            $property->city,
            $property->district,
            $property->address_line_1 ?: $property->street,
            $property->house_number ?: $property->building,
            $property->apartment_number ?: $property->apartment,
        ])->filter()->implode(', ');

        return $parts !== '' ? $parts : null;
    }

    private function hostName(Booking $booking): string
    {
        return $booking->host?->hostProfile?->display_name
            ?: $booking->host?->name
            ?: __('booking.trips.host_unknown');
    }

    private function hostContact(Booking $booking): string
    {
        return $booking->host?->profile?->phone
            ?: $booking->host?->phone
            ?: __('booking.trips.contact_in_messages');
    }

    private function instructions(Booking $booking): string
    {
        if (! $this->canShowAddress($booking)) {
            return __('booking.trips.instructions_hidden');
        }

        if (filled($booking->check_in_instructions)) {
            return (string) $booking->check_in_instructions;
        }

        $translation = $this->resolve($booking->property?->translations);

        if ($translation instanceof PropertyTranslation && filled($translation->check_in_instructions)) {
            return $translation->check_in_instructions;
        }

        return __('booking.trips.instructions_missing');
    }

    private function wifiInfo(Booking $booking): string
    {
        $slugs = $this->amenitySlugs($booking);

        if (array_intersect($slugs, ['fast_wifi'])) {
            return __('booking.trips.wifi.fast');
        }

        if (array_intersect($slugs, ['wifi', 'wi-fi'])) {
            return __('booking.trips.wifi.available');
        }

        $legacyAmenities = collect($booking->property?->getAttribute('amenities') ?? [])
            ->filter()
            ->map(fn (mixed $value): string => (string) $value)
            ->all();

        return array_intersect($legacyAmenities, ['wifi', 'fast_wifi'])
            ? __('booking.trips.wifi.available')
            : __('booking.trips.wifi.missing');
    }

    /**
     * @return list<string>
     */
    private function amenitySlugs(Booking $booking): array
    {
        return collect([
            $booking->property?->relationLoaded('amenities') ? $booking->property->getRelation('amenities') : null,
            $booking->room?->relationLoaded('amenities') ? $booking->room->getRelation('amenities') : null,
            $booking->sleepingPlace?->relationLoaded('amenities') ? $booking->sleepingPlace->getRelation('amenities') : null,
        ])
            ->filter()
            ->flatMap(fn (Collection|EloquentCollection $amenities): Collection => $amenities->map(fn (Amenity $amenity): string => $amenity->slug))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function rules(Booking $booking): array
    {
        $localizedRules = collect([
            $booking->property?->relationLoaded('rules') ? $booking->property->getRelation('rules') : null,
            $booking->room?->relationLoaded('rules') ? $booking->room->getRelation('rules') : null,
            $booking->sleepingPlace?->relationLoaded('rules') ? $booking->sleepingPlace->getRelation('rules') : null,
        ])
            ->filter()
            ->flatMap(fn (Collection|EloquentCollection $rules): Collection => $rules)
            ->unique('id')
            ->map(fn (Rule $rule): string => $this->ruleLabel($rule))
            ->filter()
            ->values();

        $propertyTranslation = $this->resolve($booking->property?->translations);
        $textRules = collect([
            $propertyTranslation instanceof PropertyTranslation ? $propertyTranslation->house_rules_text : null,
            $booking->room?->room_rules_text,
        ])->filter();

        return $localizedRules->merge($textRules)->values()->all();
    }

    private function ruleLabel(Rule $rule): string
    {
        $translation = $this->resolve($rule->translations);

        return (string) ($translation?->name ?: $rule->slug);
    }

    /**
     * @return list<array{label:string,amount:string,refundable:bool}>
     */
    private function lineItems(Booking $booking): array
    {
        if ($booking->priceLines->isNotEmpty()) {
            return $booking->priceLines
                ->map(fn ($line): array => [
                    'label' => __($line->label_key),
                    'amount' => $this->money($line->amount, $line->currency ?: $booking->currency ?: 'EUR'),
                    'refundable' => (bool) $line->is_refundable,
                ])
                ->values()
                ->all();
        }

        return [
            [
                'label' => __('booking.total'),
                'amount' => $this->money($booking->total_amount ?: $booking->total, $booking->currency ?: 'EUR'),
                'refundable' => false,
            ],
        ];
    }

    /**
     * @return array{label:string,helper:string}
     */
    private function depositStatus(Booking $booking): array
    {
        $latest = $booking->depositRecords->sortByDesc('id')->first();
        $amount = $this->money($booking->deposit_amount ?: $booking->deposit, $booking->currency ?: 'EUR');

        if ((float) ($booking->deposit_amount ?: $booking->deposit) <= 0.0) {
            return [
                'label' => __('booking.trips.deposit.none'),
                'helper' => __('booking.trips.deposit.none_helper'),
            ];
        }

        if ($booking->deposit_released_at || $latest?->released_at || $latest?->status === 'released') {
            return [
                'label' => __('booking.trips.deposit.released'),
                'helper' => __('booking.trips.deposit.released_helper', ['amount' => $amount]),
            ];
        }

        if ($latest?->status === 'withheld') {
            return [
                'label' => __('booking.trips.deposit.withheld'),
                'helper' => __('booking.trips.deposit.withheld_helper', ['amount' => $amount]),
            ];
        }

        return [
            'label' => __('booking.trips.deposit.held'),
            'helper' => __('booking.trips.deposit.held_helper', ['amount' => $amount]),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function actions(Booking $booking): array
    {
        $status = $this->statusValue($booking);
        $isCancelled = in_array($status, self::cancelledStatuses(), true);
        $isCurrent = in_array($status, self::activeStayStatuses(), true);
        $isPast = in_array($status, self::pastStatuses(), true);

        return [
            'message' => ! $isCancelled,
            'check_in' => in_array($status, [
                BookingStatus::Confirmed->value,
                BookingStatus::Paid->value,
                BookingStatus::ReadyForCheckIn->value,
            ], true),
            'report_problem' => ! $isCancelled && ! $isPast,
            'extend' => $isCurrent,
            'cancel' => $booking->isCancellable(),
            'check_out' => $isCurrent,
            'review' => $isPast && ! $booking->guest_review_left,
            'payment' => in_array($status, [BookingStatus::AwaitingPayment->value, BookingStatus::PendingPayment->value], true),
        ];
    }

    private function resolve(null|EloquentCollection|Collection $translations): mixed
    {
        if (! $translations instanceof EloquentCollection) {
            $translations = new EloquentCollection($translations?->all() ?? []);
        }

        return $this->translations->resolve(
            $translations,
            app()->getLocale(),
            config('localization.fallback_locale'),
        );
    }

    private function money(float|int|string|null $amount, string $currency): string
    {
        return Number::currency((float) ($amount ?: 0), $currency, app()->getLocale());
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }

    private function paymentStatusValue(Booking $booking): string
    {
        return $booking->payment_status instanceof PaymentStatus
            ? $booking->payment_status->value
            : (string) $booking->payment_status;
    }
}
