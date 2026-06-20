<?php

namespace App\Services\HostListings;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\MediaItem;
use App\Models\Message;
use App\Models\Property;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Localization\LocalizedModelContentResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HostListingDashboardService
{
    public function __construct(
        private readonly LocalizedModelContentResolver $translations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function home(User $host): array
    {
        $properties = $this->propertyCards($host, 'all', 5);

        return [
            'metrics' => $this->metrics($host),
            'properties' => $properties,
            'upcoming_checkins' => $this->bookingCards($host, 'checkin'),
            'upcoming_checkouts' => $this->bookingCards($host, 'checkout'),
            'tips' => collect($properties)
                ->flatMap(fn (array $property): array => $property['tips'])
                ->unique('key')
                ->take(4)
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{scope:string,title_key:string,helper_key:string,empty_title_key:string,empty_text_key:string,properties:list<array<string,mixed>>,metrics:array<string,mixed>}
     */
    public function listingPage(User $host, string $scope = 'all'): array
    {
        $scope = in_array($scope, ['all', 'drafts', 'hidden'], true) ? $scope : 'all';

        return [
            'scope' => $scope,
            'title_key' => 'host.listings.scopes.'.$scope.'.title',
            'helper_key' => 'host.listings.scopes.'.$scope.'.helper',
            'empty_title_key' => 'host.listings.scopes.'.$scope.'.empty_title',
            'empty_text_key' => 'host.listings.scopes.'.$scope.'.empty_text',
            'properties' => $this->propertyCards($host, $scope),
            'metrics' => $this->metrics($host),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function propertyDetail(User $host, Property $property): array
    {
        abort_unless($property->isOwnedBy($host), 403);

        $loaded = $this->basePropertyQuery($host)
            ->whereKey($property->id)
            ->firstOrFail();

        $cards = $this->cardsForProperties(collect([$loaded]));

        return $cards[0] ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function propertyCards(User $host, string $scope = 'all', ?int $limit = null): array
    {
        $query = $this->basePropertyQuery($host);

        match ($scope) {
            'drafts' => $query->where(function (Builder $query): void {
                $query->where('status', PropertyStatus::Draft->value)
                    ->orWhereHas('rooms', fn (Builder $room): Builder => $room->where('status', RoomStatus::Draft->value))
                    ->orWhereHas('sleepingPlaces', fn (Builder $place): Builder => $place->where('status', SleepingPlaceStatus::Draft->value));
            }),
            'hidden' => $query->where(function (Builder $query): void {
                $query->whereIn('status', [PropertyStatus::Hidden->value, PropertyStatus::Suspended->value])
                    ->orWhereHas('rooms', fn (Builder $room): Builder => $room->whereIn('status', [
                        RoomStatus::Hidden->value,
                        RoomStatus::Unavailable->value,
                        RoomStatus::Maintenance->value,
                        RoomStatus::Closed->value,
                    ]))
                    ->orWhereHas('sleepingPlaces', fn (Builder $place): Builder => $place->whereIn('status', [
                        SleepingPlaceStatus::Hidden->value,
                        SleepingPlaceStatus::Unavailable->value,
                        SleepingPlaceStatus::Repair->value,
                        SleepingPlaceStatus::Maintenance->value,
                        SleepingPlaceStatus::Closed->value,
                    ]));
            }),
            default => null,
        };

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $this->cardsForProperties($query->latest()->get());
    }

    /**
     * @return array<string, mixed>
     */
    public function metrics(User $host): array
    {
        $propertyIds = $this->hostPropertyIds($host);

        return [
            'properties' => $propertyIds->count(),
            'rooms' => Room::query()->whereIn('property_id', $propertyIds)->count(),
            'sleeping_places' => SleepingPlace::query()->whereIn('property_id', $propertyIds)->count(),
            'active_places' => SleepingPlace::query()
                ->whereIn('property_id', $propertyIds)
                ->where('status', SleepingPlaceStatus::Active->value)
                ->count(),
            'draft_places' => SleepingPlace::query()
                ->whereIn('property_id', $propertyIds)
                ->where('status', SleepingPlaceStatus::Draft->value)
                ->count(),
            'hidden_places' => SleepingPlace::query()
                ->whereIn('property_id', $propertyIds)
                ->whereIn('status', [
                    SleepingPlaceStatus::Hidden->value,
                    SleepingPlaceStatus::Unavailable->value,
                    SleepingPlaceStatus::Repair->value,
                    SleepingPlaceStatus::Maintenance->value,
                    SleepingPlaceStatus::Closed->value,
                ])
                ->count(),
            'free_places' => $this->placeCountByProperty($propertyIds, 'free')->sum(),
            'occupied_places' => $this->placeCountByProperty($propertyIds, 'occupied')->sum(),
            'pending_requests' => $this->bookingCount($host, $this->pendingRequestStatuses()),
            'upcoming_checkins' => $this->bookingCount($host, $this->upcomingCheckInStatuses(), 'check_in_date'),
            'upcoming_checkouts' => $this->bookingCount($host, $this->upcomingCheckOutStatuses(), 'check_out_date'),
            'unread_messages' => Message::query()
                ->where('recipient_user_id', $host->id)
                ->whereNull('read_at')
                ->count(),
            'monthly_income' => $this->monthlyIncome($host),
            'currency' => 'EUR',
        ];
    }

    /**
     * @param  Collection<int, Property>  $properties
     * @return list<array<string, mixed>>
     */
    private function cardsForProperties(Collection $properties): array
    {
        $propertyIds = $properties->pluck('id');
        $maps = [
            'free' => $this->placeCountByProperty($propertyIds, 'free'),
            'occupied' => $this->placeCountByProperty($propertyIds, 'occupied'),
            'pending' => $this->bookingCountByProperty($propertyIds, $this->pendingRequestStatuses()),
            'checkins' => $this->bookingCountByProperty($propertyIds, $this->upcomingCheckInStatuses(), 'check_in_date'),
            'checkouts' => $this->bookingCountByProperty($propertyIds, $this->upcomingCheckOutStatuses(), 'check_out_date'),
            'available_days' => $this->availableDayCountByProperty($propertyIds),
            'place_photos' => $this->sleepingPlacePhotoCountByProperty($propertyIds),
            'bathroom_photos' => $this->bathroomPhotoCountByProperty($propertyIds),
            'weekly_discount' => $this->weeklyDiscountCountByProperty($propertyIds),
        ];

        return $properties
            ->map(fn (Property $property): array => $this->propertyCard($property, $maps))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, Collection<int, int>>  $maps
     * @return array<string, mixed>
     */
    private function propertyCard(Property $property, array $maps): array
    {
        $translation = $this->translations->resolve($property->translations, app()->getLocale(), config('app.fallback_locale', 'en'));
        $fallbackTitle = $translation?->title ?: $property->title ?: __('host.listings.unnamed_property');
        $checks = $this->readinessChecks($property, $maps);
        $done = collect($checks)->where('done', true)->count();
        $readiness = count($checks) > 0 ? (int) round(($done / count($checks)) * 100) : 0;

        return [
            'id' => $property->id,
            'title' => $fallbackTitle,
            'location' => collect([$property->city, $property->district, $property->country])->filter()->implode(', '),
            'status' => $property->status?->value ?? (string) $property->status,
            'status_label' => $property->status?->label() ?? (string) $property->status,
            'type_label' => $property->type?->label() ?? '',
            'readiness' => $readiness,
            'checks' => $checks,
            'tips' => $this->tips($property, $checks, $maps),
            'counts' => [
                'rooms' => (int) $property->host_rooms_count,
                'sleeping_places' => (int) $property->host_sleeping_places_count,
                'active_places' => (int) $property->active_sleeping_places_count,
                'draft_places' => (int) $property->draft_sleeping_places_count,
                'hidden_places' => (int) $property->hidden_sleeping_places_count,
                'free_places' => (int) ($maps['free'][$property->id] ?? 0),
                'occupied_places' => (int) ($maps['occupied'][$property->id] ?? 0),
                'pending_requests' => (int) ($maps['pending'][$property->id] ?? 0),
                'upcoming_checkins' => (int) ($maps['checkins'][$property->id] ?? 0),
                'upcoming_checkouts' => (int) ($maps['checkouts'][$property->id] ?? 0),
            ],
        ];
    }

    /**
     * @param  array<string, Collection<int, int>>  $maps
     * @return list<array{key:string,label_key:string,done:bool}>
     */
    private function readinessChecks(Property $property, array $maps): array
    {
        $translation = $this->translations->resolve($property->translations, app()->getLocale(), config('app.fallback_locale', 'en'));
        $hasTitleDescription = filled($translation?->title ?: $property->title)
            && filled($translation?->description ?: $property->description);
        $hasCheckInInstructions = $property->translations->contains(fn ($translation): bool => filled($translation->check_in_instructions))
            || filled($property->access_instructions);

        return [
            $this->check('title_description', $hasTitleDescription),
            $this->check('address', filled($property->address_line_1 ?: $property->street) && filled($property->city ?: $property->city_id)),
            $this->check('photos', (int) $property->property_photos_count > 0),
            $this->check('room_added', (int) $property->host_rooms_count > 0),
            $this->check('sleeping_place_added', (int) $property->host_sleeping_places_count > 0),
            $this->check('price_set', (int) $property->priced_sleeping_places_count > 0),
            $this->check('calendar_opened', (int) ($maps['available_days'][$property->id] ?? 0) > 0),
            $this->check('rules_set', (int) $property->property_rules_count > 0 || $property->translations->contains(fn ($translation): bool => filled($translation->house_rules_text))),
            $this->check('amenities_set', (int) $property->property_amenities_count > 0),
            $this->check('checkin_instructions', $hasCheckInInstructions),
            $this->check('cancellation_policy', filled($property->host?->hostProfile?->default_cancellation_policy)),
        ];
    }

    /**
     * @return array{key:string,label_key:string,done:bool}
     */
    private function check(string $key, bool $done): array
    {
        return [
            'key' => $key,
            'label_key' => 'host.listings.readiness.'.$key,
            'done' => $done,
        ];
    }

    /**
     * @param  list<array{key:string,label_key:string,done:bool}>  $checks
     * @param  array<string, Collection<int, int>>  $maps
     * @return list<array{key:string,label_key:string}>
     */
    private function tips(Property $property, array $checks, array $maps): array
    {
        $tips = [];

        if (($maps['place_photos'][$property->id] ?? 0) === 0) {
            $tips[] = $this->tip('exact_sleeping_place_photo');
        }

        if (($maps['bathroom_photos'][$property->id] ?? 0) === 0) {
            $tips[] = $this->tip('bathroom_photo');
        }

        if (! $this->hasQuietHours($property)) {
            $tips[] = $this->tip('quiet_hours');
        }

        if (! $this->hasTranslation($property, 'ru')) {
            $tips[] = $this->tip('complete_ru');
        }

        if (! $this->hasTranslation($property, 'en')) {
            $tips[] = $this->tip('complete_en');
        }

        if (($maps['weekly_discount'][$property->id] ?? 0) === 0) {
            $tips[] = $this->tip('weekly_discount');
        }

        if (($maps['available_days'][$property->id] ?? 0) < 14) {
            $tips[] = $this->tip('open_more_dates');
        }

        if ($tips === [] && collect($checks)->every(fn (array $check): bool => $check['done'])) {
            $tips[] = $this->tip('ready');
        }

        return collect($tips)->unique('key')->take(4)->values()->all();
    }

    /**
     * @return array{key:string,label_key:string}
     */
    private function tip(string $key): array
    {
        return [
            'key' => $key,
            'label_key' => 'host.listings.tips.'.$key,
        ];
    }

    private function hasTranslation(Property $property, string $locale): bool
    {
        $translation = $property->translations->firstWhere('locale', $locale);

        return filled($translation?->title) && filled($translation?->description);
    }

    private function hasQuietHours(Property $property): bool
    {
        if ($property->translations->contains(fn ($translation): bool => str_contains(mb_strtolower((string) $translation->house_rules_text), 'quiet')
            || str_contains(mb_strtolower((string) $translation->house_rules_text), 'тих'))) {
            return true;
        }

        return Rule::query()
            ->whereIn('slug', ['quiet_hours', 'quiet-hours'])
            ->whereHas('properties', fn (Builder $query): Builder => $query->whereKey($property->id))
            ->exists();
    }

    private function basePropertyQuery(User $host): Builder
    {
        return Property::query()
            ->select([
                'id',
                'host_user_id',
                'user_id',
                'title',
                'type',
                'description',
                'country',
                'city',
                'city_id',
                'district',
                'street',
                'address_line_1',
                'access_instructions',
                'status',
                'created_at',
                'updated_at',
            ])
            ->where(function (Builder $query) use ($host): void {
                $query->where('host_user_id', $host->id)
                    ->orWhere('user_id', $host->id);
            })
            ->with([
                'translations:id,property_id,locale,title,description,check_in_instructions,house_rules_text',
                'host:id,name',
                'host.hostProfile:id,user_id,default_cancellation_policy',
            ])
            ->withCount([
                'rooms as host_rooms_count',
                'rooms as draft_rooms_count' => fn (Builder $query): Builder => $query->where('status', RoomStatus::Draft->value),
                'sleepingPlaces as host_sleeping_places_count',
                'sleepingPlaces as active_sleeping_places_count' => fn (Builder $query): Builder => $query->where('status', SleepingPlaceStatus::Active->value),
                'sleepingPlaces as draft_sleeping_places_count' => fn (Builder $query): Builder => $query->where('status', SleepingPlaceStatus::Draft->value),
                'sleepingPlaces as hidden_sleeping_places_count' => fn (Builder $query): Builder => $query->whereIn('status', [
                    SleepingPlaceStatus::Hidden->value,
                    SleepingPlaceStatus::Unavailable->value,
                    SleepingPlaceStatus::Repair->value,
                    SleepingPlaceStatus::Maintenance->value,
                    SleepingPlaceStatus::Closed->value,
                ]),
                'sleepingPlaces as priced_sleeping_places_count' => fn (Builder $query): Builder => $query->where('base_price_per_night', '>', 0),
                'mediaItems as property_photos_count' => fn (Builder $query): Builder => $query->active(),
                'amenities as property_amenities_count',
                'rules as property_rules_count',
            ]);
    }

    /**
     * @return Collection<int, int>
     */
    private function hostPropertyIds(User $host): Collection
    {
        return Property::query()
            ->select(['id'])
            ->where(function (Builder $query) use ($host): void {
                $query->where('host_user_id', $host->id)
                    ->orWhere('user_id', $host->id);
            })
            ->pluck('id');
    }

    /**
     * @param  Collection<int, int>  $propertyIds
     * @return Collection<int, int>
     */
    private function placeCountByProperty(Collection $propertyIds, string $mode): Collection
    {
        if ($propertyIds->isEmpty()) {
            return collect();
        }

        $query = SleepingPlace::query()
            ->select(['id', 'property_id'])
            ->whereIn('property_id', $propertyIds)
            ->where('status', SleepingPlaceStatus::Active->value)
            ->whereHas('room', fn (Builder $room): Builder => $room->where('status', RoomStatus::Active->value))
            ->whereHas('property', fn (Builder $property): Builder => $property->where('status', PropertyStatus::Active->value));

        $bookingConstraint = fn (Builder $booking): Builder => $booking
            ->whereIn('status', $this->occupiedStatuses())
            ->whereDate('check_in_date', '<=', now()->toDateString())
            ->whereDate('check_out_date', '>', now()->toDateString());

        if ($mode === 'occupied') {
            $query->whereHas('bookings', $bookingConstraint);
        } else {
            $query->whereDoesntHave('bookings', $bookingConstraint);
        }

        return $query->get()
            ->countBy('property_id');
    }

    /**
     * @param  Collection<int, int>  $propertyIds
     * @param  list<string>  $statuses
     * @return Collection<int, int>
     */
    private function bookingCountByProperty(Collection $propertyIds, array $statuses, ?string $dateColumn = null): Collection
    {
        if ($propertyIds->isEmpty()) {
            return collect();
        }

        $query = Booking::query()
            ->select(['id', 'property_id'])
            ->whereIn('property_id', $propertyIds)
            ->whereIn('status', $statuses);

        if ($dateColumn !== null) {
            $query->whereDate($dateColumn, '>=', now()->toDateString())
                ->whereDate($dateColumn, '<=', now()->addDays(7)->toDateString());
        }

        return $query->get()->countBy('property_id');
    }

    /**
     * @param  Collection<int, int>  $propertyIds
     * @return Collection<int, int>
     */
    private function availableDayCountByProperty(Collection $propertyIds): Collection
    {
        if ($propertyIds->isEmpty()) {
            return collect();
        }

        $places = SleepingPlace::query()
            ->select(['id', 'property_id'])
            ->whereIn('property_id', $propertyIds)
            ->get()
            ->pluck('property_id', 'id');

        return AvailabilityDay::query()
            ->select(['id', 'sleeping_place_id'])
            ->whereIn('sleeping_place_id', $places->keys())
            ->where('status', 'available')
            ->whereDate('date', '>=', now()->toDateString())
            ->get()
            ->map(fn (AvailabilityDay $day): ?int => $places[$day->sleeping_place_id] ?? null)
            ->filter()
            ->countBy();
    }

    /**
     * @param  Collection<int, int>  $propertyIds
     * @return Collection<int, int>
     */
    private function sleepingPlacePhotoCountByProperty(Collection $propertyIds): Collection
    {
        return $this->mediaCountByProperty($propertyIds, SleepingPlace::class);
    }

    /**
     * @param  Collection<int, int>  $propertyIds
     * @return Collection<int, int>
     */
    private function bathroomPhotoCountByProperty(Collection $propertyIds): Collection
    {
        if ($propertyIds->isEmpty()) {
            return collect();
        }

        $roomPropertyIds = Room::query()
            ->select(['id', 'property_id'])
            ->whereIn('property_id', $propertyIds)
            ->get()
            ->pluck('property_id', 'id');

        $propertyMedia = MediaItem::query()
            ->select(['id', 'mediable_id'])
            ->active()
            ->where('mediable_type', Property::class)
            ->whereIn('mediable_id', $propertyIds)
            ->where('collection', 'bathroom')
            ->get()
            ->countBy('mediable_id');

        $roomMedia = MediaItem::query()
            ->select(['id', 'mediable_id'])
            ->active()
            ->where('mediable_type', Room::class)
            ->whereIn('mediable_id', $roomPropertyIds->keys())
            ->where('collection', 'bathroom')
            ->get()
            ->map(fn (MediaItem $media): ?int => $roomPropertyIds[$media->mediable_id] ?? null)
            ->filter()
            ->countBy();

        foreach ($roomMedia as $propertyId => $count) {
            $propertyMedia[$propertyId] = (int) ($propertyMedia[$propertyId] ?? 0) + (int) $count;
        }

        return $propertyMedia;
    }

    /**
     * @param  Collection<int, int>  $propertyIds
     * @return Collection<int, int>
     */
    private function weeklyDiscountCountByProperty(Collection $propertyIds): Collection
    {
        if ($propertyIds->isEmpty()) {
            return collect();
        }

        return SleepingPlace::query()
            ->select(['id', 'property_id'])
            ->whereIn('property_id', $propertyIds)
            ->whereNotNull('weekly_price')
            ->where('weekly_price', '>', 0)
            ->get()
            ->countBy('property_id');
    }

    /**
     * @param  Collection<int, int>  $propertyIds
     * @return Collection<int, int>
     */
    private function mediaCountByProperty(Collection $propertyIds, string $mediableType): Collection
    {
        if ($propertyIds->isEmpty()) {
            return collect();
        }

        $owners = $mediableType === SleepingPlace::class
            ? SleepingPlace::query()->select(['id', 'property_id'])->whereIn('property_id', $propertyIds)->get()->pluck('property_id', 'id')
            : collect();

        return MediaItem::query()
            ->select(['id', 'mediable_id'])
            ->active()
            ->where('mediable_type', $mediableType)
            ->whereIn('mediable_id', $owners->keys())
            ->get()
            ->map(fn (MediaItem $media): ?int => $owners[$media->mediable_id] ?? null)
            ->filter()
            ->countBy();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function bookingCards(User $host, string $type): array
    {
        $dateColumn = $type === 'checkout' ? 'check_out_date' : 'check_in_date';
        $statuses = $type === 'checkout' ? $this->upcomingCheckOutStatuses() : $this->upcomingCheckInStatuses();

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
                'check_in_date',
                'check_out_date',
                'total_amount',
                'total',
                'currency',
            ])
            ->where('host_user_id', $host->id)
            ->whereIn('status', $statuses)
            ->whereDate($dateColumn, '>=', now()->toDateString())
            ->whereDate($dateColumn, '<=', now()->addDays(7)->toDateString())
            ->with([
                'guest:id,name',
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->orderBy($dateColumn)
            ->limit(5)
            ->get()
            ->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'guest' => $booking->guest?->name ?: __('host.listings.guest'),
                'place' => $this->placeTitle($booking),
                'date' => $booking->{$dateColumn}?->translatedFormat('d M'),
                'amount' => (float) ($booking->total_amount ?: $booking->total ?: 0),
                'currency' => $booking->currency ?: 'EUR',
            ])
            ->all();
    }

    private function placeTitle(Booking $booking): string
    {
        $translation = $this->translations->resolve(
            $booking->sleepingPlace?->translations,
            app()->getLocale(),
            config('app.fallback_locale', 'en'),
        );

        return $translation?->title
            ?: $booking->sleepingPlace?->display_name
            ?: $booking->sleepingPlace?->place_number
            ?: __('host.sleeping_places.default_name');
    }

    /**
     * @param  list<string>  $statuses
     */
    private function bookingCount(User $host, array $statuses, ?string $dateColumn = null): int
    {
        $query = Booking::query()
            ->where('host_user_id', $host->id)
            ->whereIn('status', $statuses);

        if ($dateColumn !== null) {
            $query->whereDate($dateColumn, '>=', now()->toDateString())
                ->whereDate($dateColumn, '<=', now()->addDays(7)->toDateString());
        }

        return $query->count();
    }

    private function monthlyIncome(User $host): float
    {
        return Booking::query()
            ->select(['id', 'total_amount', 'total'])
            ->where('host_user_id', $host->id)
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereDate('check_in_date', '>=', CarbonImmutable::now()->startOfMonth()->toDateString())
            ->whereDate('check_in_date', '<=', CarbonImmutable::now()->endOfMonth()->toDateString())
            ->get()
            ->sum(fn (Booking $booking): float => (float) ($booking->total_amount ?: $booking->total ?: 0));
    }

    /**
     * @return list<string>
     */
    private function pendingRequestStatuses(): array
    {
        return [
            BookingStatus::AwaitingHostApproval->value,
            BookingStatus::PendingHostConfirmation->value,
        ];
    }

    /**
     * @return list<string>
     */
    private function upcomingCheckInStatuses(): array
    {
        return [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
        ];
    }

    /**
     * @return list<string>
     */
    private function upcomingCheckOutStatuses(): array
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
    private function occupiedStatuses(): array
    {
        return [
            ...$this->upcomingCheckInStatuses(),
            ...$this->upcomingCheckOutStatuses(),
        ];
    }
}
