<?php

namespace App\Livewire\Shell;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\NotificationService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HostCalendarPage extends Component
{
    public ?int $selectedPropertyId = null;

    public ?int $selectedRoomId = null;

    public ?int $selectedSleepingPlaceId = null;

    public string $viewMode = 'list';

    public string $month = '';

    public string $rangeStart = '';

    public string $rangeEnd = '';

    public string $bulkStatus = 'blocked_by_host';

    public ?float $priceOverride = null;

    public ?int $minNightsOverride = null;

    public ?int $maxNightsOverride = null;

    public bool $checkInAllowed = true;

    public bool $checkOutAllowed = true;

    public string $note = '';

    public bool $dateActionsOpen = false;

    public function mount(): void
    {
        $today = CarbonImmutable::today();

        $this->month = $today->format('Y-m');
        $this->rangeStart = $today->toDateString();
        $this->rangeEnd = $today->toDateString();
        $this->selectedPropertyId = $this->firstHostPropertyId();
        $this->selectedRoomId = $this->firstHostRoomId($this->selectedPropertyId);
        $this->selectedSleepingPlaceId = $this->firstHostSleepingPlaceId($this->selectedPropertyId, $this->selectedRoomId);
    }

    public function updatedSelectedPropertyId(): void
    {
        $this->selectedPropertyId = $this->selectedPropertyId ? (int) $this->selectedPropertyId : null;
        $this->selectedRoomId = $this->firstHostRoomId($this->selectedPropertyId);
        $this->selectedSleepingPlaceId = $this->firstHostSleepingPlaceId($this->selectedPropertyId, $this->selectedRoomId);
        $this->flushCalendarState();
    }

    public function updatedSelectedRoomId(): void
    {
        $this->selectedRoomId = $this->selectedRoomId ? (int) $this->selectedRoomId : null;
        $this->selectedSleepingPlaceId = $this->firstHostSleepingPlaceId($this->selectedPropertyId, $this->selectedRoomId);
        $this->flushCalendarState();
    }

    public function updatedSelectedSleepingPlaceId(): void
    {
        $this->selectedSleepingPlaceId = $this->selectedSleepingPlaceId ? (int) $this->selectedSleepingPlaceId : null;
        $this->flushCalendarState();
    }

    public function previousMonth(): void
    {
        $this->month = $this->monthDate()->subMonthNoOverflow()->format('Y-m');
        $this->flushCalendarState();
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthDate()->addMonthNoOverflow()->format('Y-m');
        $this->flushCalendarState();
    }

    public function selectDate(string $date): void
    {
        try {
            $selected = CarbonImmutable::parse($date)->toDateString();
        } catch (\Throwable) {
            return;
        }

        $this->rangeStart = $selected;
        $this->rangeEnd = $selected;
        $this->dateActionsOpen = true;
    }

    public function closeDateActions(): void
    {
        $this->dateActionsOpen = false;
    }

    public function openRange(): void
    {
        $this->bulkStatus = AvailabilityStatus::Available->value;
        $this->priceOverride = null;
        $this->applyRange();
    }

    public function closeRange(): void
    {
        $this->bulkStatus = AvailabilityStatus::BlockedByHost->value;
        $this->applyRange();
    }

    public function markRepairRange(): void
    {
        $this->bulkStatus = AvailabilityStatus::Repair->value;
        $this->applyRange();
    }

    public function markCleaningRange(): void
    {
        $this->bulkStatus = AvailabilityStatus::Cleaning->value;
        $this->applyRange();
    }

    public function applyRange(): void
    {
        $validated = $this->validate([
            'selectedSleepingPlaceId' => ['required', 'integer'],
            'rangeStart' => ['required', 'date'],
            'rangeEnd' => ['required', 'date', 'after_or_equal:rangeStart'],
            'bulkStatus' => ['required', Rule::in(AvailabilityStatus::hostEditableValues())],
            'priceOverride' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'minNightsOverride' => ['nullable', 'integer', 'min:1', 'max:365'],
            'maxNightsOverride' => ['nullable', 'integer', 'min:1', 'max:365'],
            'checkInAllowed' => ['boolean'],
            'checkOutAllowed' => ['boolean'],
            'note' => ['nullable', 'string', 'max:160'],
        ], attributes: $this->validationAttributes());

        if ($validated['maxNightsOverride'] !== null
            && $validated['minNightsOverride'] !== null
            && $validated['maxNightsOverride'] < $validated['minNightsOverride']) {
            $this->addError('maxNightsOverride', __('availability.calendar.validation.max_nights_after_min'));

            return;
        }

        $place = $this->hostSleepingPlaceQuery()->find($validated['selectedSleepingPlaceId']);

        if (! $place instanceof SleepingPlace) {
            $this->addError('selectedSleepingPlaceId', __('availability.calendar.validation.not_your_place'));

            return;
        }

        $start = CarbonImmutable::parse($validated['rangeStart'])->startOfDay();
        $end = CarbonImmutable::parse($validated['rangeEnd'])->startOfDay();
        $applied = 0;
        $skipped = 0;

        foreach ($this->inclusiveDateRange($start, $end) as $date) {
            $existing = $place->availabilityDays()
                ->whereDate('date', $date)
                ->first(['id', 'booking_id', 'status']);

            if ($existing && ($existing->booking_id || in_array($existing->status?->value ?? $existing->status, AvailabilityStatus::bookingHoldValues(), true))) {
                $skipped++;

                continue;
            }

            $availabilityDay = $existing ?? $place->availabilityDays()->make(['date' => $date]);
            $availabilityDay->fill([
                'booking_id' => null,
                'status' => $validated['bulkStatus'],
                'price_override' => $validated['priceOverride'],
                'min_nights_override' => $validated['minNightsOverride'],
                'max_nights_override' => $validated['maxNightsOverride'],
                'check_in_allowed' => $validated['checkInAllowed'],
                'check_out_allowed' => $validated['checkOutAllowed'],
                'note' => $this->blankToNull($validated['note']),
            ]);
            $availabilityDay->save();

            $applied++;
        }

        $this->dateActionsOpen = false;
        $this->flushCalendarState();

        $user = auth()->user();

        if ($applied > 0 && $user instanceof User) {
            app(NotificationService::class)->notifyPlaceAvailabilityChanged($place, $user);
        }

        session()->flash('availability-status', __('availability.calendar.flash.saved', [
            'count' => $applied,
            'skipped' => $skipped,
        ]));
    }

    #[Computed]
    public function properties(): array
    {
        return $this->hostPropertyQuery()
            ->withCount([
                'rooms as host_rooms_count',
                'sleepingPlaces as host_sleeping_places_count',
            ])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn (Property $property): array => [
                'id' => $property->id,
                'label' => $property->title ?: __('availability.calendar.property_fallback'),
                'rooms_count' => (int) $property->host_rooms_count,
                'sleeping_places_count' => (int) $property->host_sleeping_places_count,
            ])
            ->all();
    }

    #[Computed]
    public function rooms(): array
    {
        if (! $this->selectedPropertyId) {
            return [];
        }

        return $this->hostRoomQuery($this->selectedPropertyId)
            ->withCount('sleepingPlaces')
            ->orderBy('room_number')
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(fn (Room $room): array => [
                'id' => $room->id,
                'label' => $room->title ?: $room->room_number ?: __('availability.calendar.room_fallback'),
                'sleeping_places_count' => (int) $room->sleeping_places_count,
            ])
            ->all();
    }

    #[Computed]
    public function sleepingPlaces(): array
    {
        $resolver = app(LocalizedModelContentResolver::class);

        return $this->hostSleepingPlaceQuery()
            ->with([
                'translations:id,sleeping_place_id,locale,title',
                'room:id,property_id,title,room_number',
                'property:id,title',
            ])
            ->orderBy('property_id')
            ->orderBy('room_id')
            ->orderBy('place_number')
            ->limit(50)
            ->get()
            ->map(function (SleepingPlace $place) use ($resolver): array {
                $translation = $resolver->resolve($place->translations, app()->getLocale(), 'en');
                $roomLabel = $place->room?->title ?: $place->room?->room_number ?: __('availability.calendar.room_fallback');

                return [
                    'id' => $place->id,
                    'label' => trim(($translation?->title ?: $place->display_name ?: $place->place_number ?: __('availability.calendar.place_fallback')).' · '.$roomLabel),
                ];
            })
            ->all();
    }

    #[Computed]
    public function summary(): array
    {
        $placeIds = $this->scopePlaceIds();
        $month = $this->monthDate();
        $start = $month->startOfMonth();
        $end = $month->endOfMonth()->addDay();
        $daysInMonth = $month->daysInMonth;
        $occupiedNights = $this->occupiedNightCountsByPlace($placeIds, $start, $end)->sum();
        $totalPlaceDays = max(0, $placeIds->count() * $daysInMonth);
        $percentage = $totalPlaceDays > 0 ? (int) round(($occupiedNights / $totalPlaceDays) * 100) : 0;
        $availableDays = $this->availabilityCountByStatus($placeIds, AvailabilityStatus::Available, $start, $end);

        return [
            'places_count' => $placeIds->count(),
            'occupied_nights' => (int) $occupiedNights,
            'available_days' => $availableDays,
            'total_place_days' => $totalPlaceDays,
            'occupancy_percentage' => min(100, $percentage),
        ];
    }

    #[Computed]
    public function hierarchyOverview(): array
    {
        $month = $this->monthDate();
        $start = $month->startOfMonth();
        $end = $month->endOfMonth()->addDay();
        $properties = $this->hostPropertyQuery()
            ->with([
                'rooms:id,property_id,title,room_number',
                'rooms.sleepingPlaces:id,room_id,property_id,display_name,place_number,status',
                'rooms.sleepingPlaces.translations:id,sleeping_place_id,locale,title',
            ])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        $placeIds = $properties
            ->flatMap(fn (Property $property): Collection => $property->rooms->flatMap(fn (Room $room): Collection => $room->sleepingPlaces->pluck('id')))
            ->values();
        $occupiedByPlace = $this->occupiedNightCountsByPlace($placeIds, $start, $end);
        $resolver = app(LocalizedModelContentResolver::class);

        return $properties->map(function (Property $property) use ($occupiedByPlace, $month, $resolver): array {
            $rooms = $property->rooms->map(function (Room $room) use ($occupiedByPlace, $month, $resolver): array {
                $places = $room->sleepingPlaces->map(function (SleepingPlace $place) use ($occupiedByPlace, $month, $resolver): array {
                    $translation = $resolver->resolve($place->translations, app()->getLocale(), 'en');

                    return [
                        'id' => $place->id,
                        'label' => $translation?->title ?: $place->display_name ?: $place->place_number ?: __('availability.calendar.place_fallback'),
                        'occupied_nights' => (int) ($occupiedByPlace[$place->id] ?? 0),
                        'occupancy_percentage' => $this->percentage((int) ($occupiedByPlace[$place->id] ?? 0), $month->daysInMonth),
                    ];
                })->values();
                $occupied = $places->sum('occupied_nights');

                return [
                    'id' => $room->id,
                    'label' => $room->title ?: $room->room_number ?: __('availability.calendar.room_fallback'),
                    'places_count' => $places->count(),
                    'occupancy_percentage' => $this->percentage($occupied, max(1, $places->count()) * $month->daysInMonth),
                    'places' => $places->all(),
                ];
            })->values();
            $propertyPlaceCount = $rooms->sum('places_count');
            $propertyOccupied = $property->rooms
                ->flatMap(fn (Room $room): Collection => $room->sleepingPlaces->pluck('id'))
                ->sum(fn (int $placeId): int => (int) ($occupiedByPlace[$placeId] ?? 0));

            return [
                'id' => $property->id,
                'label' => $property->title ?: __('availability.calendar.property_fallback'),
                'rooms_count' => $rooms->count(),
                'places_count' => $propertyPlaceCount,
                'occupancy_percentage' => $this->percentage($propertyOccupied, max(1, $propertyPlaceCount) * $month->daysInMonth),
                'rooms' => $rooms->all(),
            ];
        })->values()->all();
    }

    #[Computed]
    public function calendarDays(): array
    {
        $place = $this->selectedSleepingPlace();

        if (! $place) {
            return [];
        }

        $month = $this->monthDate();
        $gridStart = $month->startOfMonth()->startOfWeek(CarbonInterface::MONDAY);
        $gridEnd = $gridStart->addDays(42);
        $availabilityByDate = $place->availabilityDays()
            ->select(['id', 'sleeping_place_id', 'booking_id', 'date', 'status', 'price_override', 'min_nights_override', 'max_nights_override', 'check_in_allowed', 'check_out_allowed', 'note'])
            ->whereDate('date', '>=', $gridStart->toDateString())
            ->whereDate('date', '<', $gridEnd->toDateString())
            ->orderBy('date')
            ->get()
            ->keyBy(fn (AvailabilityDay $day): string => $day->date->toDateString());
        $bookingsByDate = $this->bookingMap($place, $gridStart, $gridEnd);
        $days = [];
        $cursor = $gridStart;

        while ($cursor->lessThan($gridEnd)) {
            $date = $cursor->toDateString();
            $day = $availabilityByDate->get($date);
            $booking = $bookingsByDate[$date] ?? null;
            $status = $day?->status instanceof AvailabilityStatus
                ? $day->status->value
                : ($day?->status ?: ($booking['availability_status'] ?? AvailabilityStatus::Available->value));

            $days[] = [
                'date' => $date,
                'day' => $cursor->day,
                'is_current_month' => $cursor->isSameMonth($month),
                'is_today' => $cursor->isToday(),
                'status' => $status,
                'status_label' => $this->statusLabel($status),
                'color' => $this->statusColor($status),
                'price_override' => $day?->price_override,
                'min_nights_override' => $day?->min_nights_override,
                'max_nights_override' => $day?->max_nights_override,
                'check_in_allowed' => $day?->check_in_allowed ?? true,
                'check_out_allowed' => $day?->check_out_allowed ?? true,
                'booking' => $booking,
            ];

            $cursor = $cursor->addDay();
        }

        return $days;
    }

    #[Computed]
    public function listDays(): array
    {
        return collect($this->calendarDays)
            ->filter(fn (array $day): bool => $day['is_current_month'])
            ->values()
            ->all();
    }

    #[Computed]
    public function upcomingCheckIns(): array
    {
        return $this->bookingCards('check_in_date');
    }

    #[Computed]
    public function upcomingCheckOuts(): array
    {
        return $this->bookingCards('check_out_date');
    }

    public function statusOptions(): array
    {
        return collect(AvailabilityStatus::hostEditableValues())
            ->mapWithKeys(fn (string $status): array => [$status => $this->statusLabel($status)])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.shell.host-calendar-page', [
            'page' => $this->page(),
            'summary' => $this->summary,
            'monthLabel' => $this->monthDate()->translatedFormat('F Y'),
        ])
            ->layout('layouts.app', ['title' => __('shell.pages.host.calendar.title')]);
    }

    /**
     * @return array<string, string>
     */
    private function page(): array
    {
        return [
            'eyebrow' => __('shell.pages.host.calendar.eyebrow'),
            'title' => __('shell.pages.host.calendar.title'),
            'helper' => __('shell.pages.host.calendar.helper'),
            'action' => __('shell.pages.host.calendar.action'),
            'empty_title' => __('shell.pages.host.calendar.empty_title'),
            'empty_text' => __('shell.pages.host.calendar.empty_text'),
            'note' => __('shell.pages.host.calendar.note'),
            'icon' => __('shell.pages.host.calendar.icon'),
        ];
    }

    private function selectedSleepingPlace(): ?SleepingPlace
    {
        if (! $this->selectedSleepingPlaceId) {
            return null;
        }

        return $this->hostSleepingPlaceQuery()->find($this->selectedSleepingPlaceId);
    }

    private function firstHostPropertyId(): ?int
    {
        return $this->hostPropertyQuery()->value('id');
    }

    private function firstHostRoomId(?int $propertyId = null): ?int
    {
        if (! $propertyId) {
            return null;
        }

        return $this->hostRoomQuery($propertyId)->value('id');
    }

    private function firstHostSleepingPlaceId(?int $propertyId = null, ?int $roomId = null): ?int
    {
        return $this->hostSleepingPlaceQuery($propertyId, $roomId)->value('id');
    }

    private function hostPropertyQuery(): Builder
    {
        return Property::query()
            ->select(['id', 'host_user_id', 'user_id', 'title', 'created_at'])
            ->where(function (Builder $property): void {
                $property->where('host_user_id', auth()->id())
                    ->orWhere('user_id', auth()->id());
            });
    }

    private function hostRoomQuery(int $propertyId): Builder
    {
        return Room::query()
            ->select(['id', 'property_id', 'title', 'room_number'])
            ->where('property_id', $propertyId)
            ->whereHas('property', fn (Builder $property): Builder => $property
                ->where('host_user_id', auth()->id())
                ->orWhere('user_id', auth()->id()));
    }

    private function hostSleepingPlaceQuery(?int $propertyId = null, ?int $roomId = null): Builder
    {
        return SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'status', 'place_number', 'display_name'])
            ->when($propertyId ?? $this->selectedPropertyId, fn (Builder $query, int $id): Builder => $query->where('property_id', $id))
            ->when($roomId ?? $this->selectedRoomId, fn (Builder $query, int $id): Builder => $query->where('room_id', $id))
            ->whereHas('property', fn (Builder $property): Builder => $property
                ->where('host_user_id', auth()->id())
                ->orWhere('user_id', auth()->id()));
    }

    /**
     * @return Collection<int, int>
     */
    private function scopePlaceIds(): Collection
    {
        return $this->hostSleepingPlaceQuery()
            ->pluck('id')
            ->values();
    }

    /**
     * @param  Collection<int, int>  $placeIds
     * @return Collection<int, int>
     */
    private function occupiedNightCountsByPlace(Collection $placeIds, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        if ($placeIds->isEmpty()) {
            return collect();
        }

        $counts = collect();

        Booking::query()
            ->select(['id', 'sleeping_place_id', 'status', 'check_in_date', 'check_out_date'])
            ->whereIn('sleeping_place_id', $placeIds)
            ->whereNotIn('status', $this->nonBlockingBookingStatuses())
            ->whereDate('check_in_date', '<', $end->toDateString())
            ->whereDate('check_out_date', '>', $start->toDateString())
            ->get()
            ->each(function (Booking $booking) use ($counts, $start, $end): void {
                $bookingStart = CarbonImmutable::parse($booking->check_in_date)->startOfDay()->max($start);
                $bookingEnd = CarbonImmutable::parse($booking->check_out_date)->startOfDay()->min($end);
                $nights = max(0, (int) $bookingStart->diffInDays($bookingEnd));

                $counts[$booking->sleeping_place_id] = (int) ($counts[$booking->sleeping_place_id] ?? 0) + $nights;
            });

        return $counts;
    }

    /**
     * @param  Collection<int, int>  $placeIds
     */
    private function availabilityCountByStatus(Collection $placeIds, AvailabilityStatus $status, CarbonImmutable $start, CarbonImmutable $end): int
    {
        if ($placeIds->isEmpty()) {
            return 0;
        }

        return AvailabilityDay::query()
            ->whereIn('sleeping_place_id', $placeIds)
            ->where('status', $status->value)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<', $end->toDateString())
            ->count();
    }

    private function bookingMap(SleepingPlace $place, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $map = [];

        $this->bookingQueryForPlace($place, $start, $end)
            ->with(['guest:id,name'])
            ->get()
            ->each(function (Booking $booking) use (&$map, $start, $end): void {
                $cursor = CarbonImmutable::parse($booking->check_in_date)->startOfDay()->max($start);
                $bookingEnd = CarbonImmutable::parse($booking->check_out_date)->startOfDay()->min($end);
                $status = $this->availabilityStatusForBooking($booking)->value;

                while ($cursor->lessThan($bookingEnd)) {
                    $map[$cursor->toDateString()] = [
                        'id' => $booking->id,
                        'guest' => $booking->guest?->name ?: __('availability.calendar.guest_fallback'),
                        'guests_count' => (int) $booking->guests_count,
                        'status' => $booking->status?->label() ?? (string) $booking->status,
                        'availability_status' => $status,
                        'check_in' => $booking->check_in_date?->toDateString(),
                        'check_out' => $booking->check_out_date?->toDateString(),
                    ];
                    $cursor = $cursor->addDay();
                }
            });

        return $map;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function bookingCards(string $dateColumn): array
    {
        $placeIds = $this->scopePlaceIds();

        if ($placeIds->isEmpty()) {
            return [];
        }

        return Booking::query()
            ->select(['id', 'guest_user_id', 'sleeping_place_id', 'status', 'check_in_date', 'check_out_date', 'guests_count'])
            ->whereIn('sleeping_place_id', $placeIds)
            ->whereNotIn('status', $this->nonBlockingBookingStatuses())
            ->whereDate($dateColumn, '>=', now()->toDateString())
            ->whereDate($dateColumn, '<=', now()->addDays(14)->toDateString())
            ->with([
                'guest:id,name',
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->orderBy($dateColumn)
            ->limit(6)
            ->get()
            ->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'guest' => $booking->guest?->name ?: __('availability.calendar.guest_fallback'),
                'place' => $this->bookingPlaceLabel($booking),
                'date' => $booking->{$dateColumn}?->translatedFormat('d M'),
                'guests_count' => (int) $booking->guests_count,
                'status' => $booking->status?->label() ?? (string) $booking->status,
            ])
            ->all();
    }

    private function bookingPlaceLabel(Booking $booking): string
    {
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $booking->sleepingPlace?->translations ?? collect(),
            app()->getLocale(),
            'en',
        );

        return $translation?->title
            ?: $booking->sleepingPlace?->display_name
            ?: $booking->sleepingPlace?->place_number
            ?: __('availability.calendar.place_fallback');
    }

    /**
     * @return HasMany<Booking, SleepingPlace>
     */
    private function bookingQueryForPlace(SleepingPlace $place, CarbonImmutable $start, CarbonImmutable $end): HasMany
    {
        return $place->bookings()
            ->select(['id', 'guest_user_id', 'sleeping_place_id', 'status', 'check_in_date', 'check_out_date', 'guests_count'])
            ->whereNotIn('status', $this->nonBlockingBookingStatuses())
            ->whereDate('check_in_date', '<', $end->toDateString())
            ->whereDate('check_out_date', '>', $start->toDateString());
    }

    private function availabilityStatusForBooking(Booking $booking): AvailabilityStatus
    {
        return match ($booking->status) {
            BookingStatus::AwaitingPayment,
            BookingStatus::PendingPayment => AvailabilityStatus::PendingPayment,
            BookingStatus::AwaitingHostApproval,
            BookingStatus::PendingHostConfirmation,
            BookingStatus::PendingGuestResponse,
            BookingStatus::Created => AvailabilityStatus::PendingApproval,
            default => AvailabilityStatus::Booked,
        };
    }

    private function monthDate(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->month.'-01') ?: CarbonImmutable::today()->startOfMonth();
    }

    /**
     * @return list<string>
     */
    private function inclusiveDateRange(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $dates = [];
        $cursor = $start;

        while ($cursor->lessThanOrEqualTo($end)) {
            $dates[] = $cursor->toDateString();
            $cursor = $cursor->addDay();
        }

        return $dates;
    }

    private function percentage(int $value, int $total): int
    {
        return $total > 0 ? min(100, (int) round(($value / $total) * 100)) : 0;
    }

    private function statusLabel(string $status): string
    {
        return __('statuses.availability.'.$status);
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            AvailabilityStatus::Available->value,
            AvailabilityStatus::CheckInOnly->value,
            AvailabilityStatus::CheckOutOnly->value => 'green',
            AvailabilityStatus::Booked->value,
            AvailabilityStatus::PendingPayment->value,
            AvailabilityStatus::PendingApproval->value => 'blue',
            AvailabilityStatus::Cleaning->value => 'amber',
            AvailabilityStatus::Repair->value,
            AvailabilityStatus::Unavailable->value => 'red',
            default => 'zinc',
        };
    }

    /**
     * @return list<string>
     */
    private function nonBlockingBookingStatuses(): array
    {
        return [
            BookingStatus::Draft->value,
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
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::AwaitingReview->value,
            BookingStatus::Closed->value,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('availability.calendar.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function flushCalendarState(): void
    {
        unset(
            $this->properties,
            $this->rooms,
            $this->sleepingPlaces,
            $this->summary,
            $this->hierarchyOverview,
            $this->calendarDays,
            $this->listDays,
            $this->upcomingCheckIns,
            $this->upcomingCheckOuts,
        );
    }
}
