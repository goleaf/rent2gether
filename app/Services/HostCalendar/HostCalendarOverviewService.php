<?php

namespace App\Services\HostCalendar;

use App\Models\HostCalendarEvent;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostCalendar\Data\HostCalendarFilters;
use App\Services\Localization\LocalizedModelContentResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;

class HostCalendarOverviewService
{
    private const ROW_COLUMNS = [
        'id',
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'booking_id',
        'cleaning_task_id',
        'event_type',
        'event_status',
        'event_date',
        'title_key',
        'guest_user_id',
        'guest_display_name',
        'check_in_date',
        'check_out_date',
        'nights_count',
        'payment_status',
        'check_in_status',
        'place_status',
        'needs_cleaning',
        'needs_inspection',
        'needs_repair',
        'price_amount',
        'currency',
        'payout_status',
        'payout_amount',
        'priority',
        'source',
        'host_note',
        'is_private',
    ];

    /**
     * @var array<string, list<string>>
     */
    private const VIEW_EVENT_TYPES = [
        'property' => [],
        'room' => [],
        'sleeping_place' => [],
        'check_ins' => ['check_in'],
        'check_outs' => ['check_out'],
        'cleaning' => ['cleaning'],
        'repairs' => ['repair'],
        'payouts' => ['payout'],
        'prices' => ['price'],
        'occupancy' => ['booking'],
    ];

    public function __construct(
        private readonly HostCalendarFilterService $filters,
        private readonly LocalizedModelContentResolver $content,
    ) {}

    /**
     * @return list<string>
     */
    public static function viewKeys(): array
    {
        return array_keys(self::VIEW_EVENT_TYPES);
    }

    public static function isSupportedView(string $view): bool
    {
        return array_key_exists($view, self::VIEW_EVENT_TYPES);
    }

    /**
     * @return list<string>
     */
    public static function eventTypesForView(string $view): array
    {
        return self::VIEW_EVENT_TYPES[$view] ?? [];
    }

    public function paginateRows(User $host, array $range, HostCalendarFilters $filters, int $perPage = 20, string $pageName = 'hostCalendarEventsPage'): Paginator
    {
        $paginator = $this->query($host, $range, $filters)
            ->simplePaginate($perPage, pageName: $pageName);

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (HostCalendarEvent $event): array => $this->row($event)),
        );

        return $paginator;
    }

    /**
     * @return array<string, int>
     */
    public function summary(User $host, array $range, HostCalendarFilters $filters): array
    {
        $events = $this->query($host, $range, $filters)
            ->get(['event_type', 'needs_cleaning', 'needs_inspection', 'needs_repair']);

        return [
            'total_events' => $events->count(),
            'check_ins' => $events->where('event_type', 'check_in')->count(),
            'check_outs' => $events->where('event_type', 'check_out')->count(),
            'cleanings' => $events->where('event_type', 'cleaning')->count(),
            'repairs' => $events->where('event_type', 'repair')->count(),
            'payouts' => $events->where('event_type', 'payout')->count(),
            'prices' => $events->where('event_type', 'price')->count(),
            'problem_events' => $events
                ->filter(fn (HostCalendarEvent $event): bool => $event->needs_cleaning || $event->needs_inspection || $event->needs_repair)
                ->count(),
        ];
    }

    public function query(User $host, array $range, HostCalendarFilters $filters): Builder
    {
        $query = HostCalendarEvent::query()
            ->select(self::ROW_COLUMNS)
            ->where('user_id', $host->id)
            ->where('event_date', '>=', $this->dateString($range['start']))
            ->where('event_date', '<', $this->dateString($range['end']))
            ->with([
                'property:id,title',
                'property.translations:id,property_id,locale,title',
                'room:id,property_id,title,room_number',
                'room.translations:id,room_id,locale,title',
                'sleepingPlace:id,property_id,room_id,display_name,place_number,title',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->orderBy('event_date')
            ->orderByDesc('priority')
            ->orderBy('id');

        return $this->filters->apply($query, $filters);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(HostCalendarEvent $event): array
    {
        return [
            'id' => $event->id,
            'wire_key' => 'host-calendar-event-'.$event->id,
            'event_type' => $event->event_type,
            'event_type_label' => $this->translation('host_calendar.event_types.'.$event->event_type),
            'event_type_color' => $this->eventTypeColor($event->event_type),
            'event_status' => $event->event_status,
            'event_status_label' => $this->statusLabel($event->event_status),
            'date' => $event->event_date?->toDateString(),
            'date_label' => $this->dateLabel($event->event_date?->toDateString()),
            'property' => $this->propertyLabel($event->property),
            'room' => $this->roomLabel($event->room),
            'sleeping_place' => $this->sleepingPlaceLabel($event->sleepingPlace),
            'place_status' => $event->place_status,
            'place_status_label' => $this->statusLabel($event->place_status),
            'place_status_color' => $this->placeStatusColor($event->place_status),
            'guest_name' => $event->guest_display_name ?: $this->translation('host_calendar.values.no_guest'),
            'check_in_date' => $event->check_in_date?->toDateString(),
            'check_in_date_label' => $this->dateLabel($event->check_in_date?->toDateString()),
            'check_out_date' => $event->check_out_date?->toDateString(),
            'check_out_date_label' => $this->dateLabel($event->check_out_date?->toDateString()),
            'nights_count' => $event->nights_count,
            'nights_count_label' => $event->nights_count === null
                ? $this->translation('host_calendar.values.none')
                : (string) $event->nights_count,
            'payment_status' => $event->payment_status,
            'payment_status_label' => $this->paymentStatusLabel($event->payment_status),
            'check_in_status' => $event->check_in_status,
            'check_in_status_label' => $this->checkInStatusLabel($event->check_in_status),
            'needs_cleaning' => (bool) $event->needs_cleaning,
            'needs_inspection' => (bool) $event->needs_inspection,
            'needs_repair' => (bool) $event->needs_repair,
            'needs_cleaning_label' => $this->booleanLabel($event->needs_cleaning),
            'needs_inspection_label' => $this->booleanLabel($event->needs_inspection),
            'needs_repair_label' => $this->booleanLabel($event->needs_repair),
            'date_price' => $this->money($event->price_amount, $event->currency),
            'payout' => $this->money($event->payout_amount, $event->currency),
            'host_comment' => $event->host_note ?: $this->translation('host_calendar.values.no_comment'),
        ];
    }

    private function propertyLabel(?Property $property): string
    {
        if (! $property) {
            return $this->translation('host_calendar.values.no_property');
        }

        $translation = $this->content->resolve($property->translations, app()->getLocale(), 'en');

        return $translation?->title ?: $property->title ?: $this->translation('host_calendar.values.no_property');
    }

    private function roomLabel(?Room $room): string
    {
        if (! $room) {
            return $this->translation('host_calendar.values.no_room');
        }

        $translation = $this->content->resolve($room->translations, app()->getLocale(), 'en');

        return $translation?->title ?: $room->title ?: $room->room_number ?: $this->translation('host_calendar.values.no_room');
    }

    private function sleepingPlaceLabel(?SleepingPlace $place): string
    {
        if (! $place) {
            return $this->translation('host_calendar.values.no_sleeping_place');
        }

        $translation = $this->content->resolve($place->translations, app()->getLocale(), 'en');

        return $translation?->title
            ?: $place->title
            ?: $place->display_name
            ?: $place->place_number
            ?: $this->translation('host_calendar.values.no_sleeping_place');
    }

    private function dateLabel(?string $date): string
    {
        if (! $date) {
            return $this->translation('host_calendar.values.none');
        }

        return CarbonImmutable::parse($date)->translatedFormat('d M Y');
    }

    private function money(mixed $amount, ?string $currency): string
    {
        if ($amount === null || $amount === '') {
            return $this->translation('host_calendar.values.none');
        }

        return trim(($currency ?: 'EUR').' '.number_format((float) $amount, 2, '.', ''));
    }

    private function statusLabel(?string $status): string
    {
        if (! $status) {
            return $this->translation('host_calendar.values.none');
        }

        return $this->translation('host_calendar.statuses.'.$status, $status);
    }

    private function paymentStatusLabel(?string $status): string
    {
        if (! $status) {
            return $this->translation('host_calendar.values.none');
        }

        return $this->translation('host_calendar.payment_statuses.'.$status, $status);
    }

    private function checkInStatusLabel(?string $status): string
    {
        if (! $status) {
            return $this->translation('host_calendar.values.none');
        }

        return $this->translation('host_calendar.check_in_statuses.'.$status, $status);
    }

    private function booleanLabel(bool $value): string
    {
        return $this->translation('host_calendar.boolean.'.($value ? 'yes' : 'no'));
    }

    private function eventTypeColor(string $eventType): string
    {
        return match ($eventType) {
            'check_in', 'check_out', 'booking' => 'blue',
            'cleaning' => 'amber',
            'repair' => 'red',
            'payout' => 'emerald',
            'price' => 'purple',
            'note' => 'zinc',
            default => 'zinc',
        };
    }

    private function placeStatusColor(?string $status): string
    {
        return match ($status) {
            'available', 'checked_out' => 'green',
            'booked', 'occupied', 'checked_in' => 'blue',
            'pending_payment', 'pending_confirmation', 'cleaning', 'needs_cleaning', 'needs_inspection' => 'amber',
            'repair', 'broken', 'blocked_by_host', 'blocked_by_system', 'unavailable' => 'red',
            default => 'zinc',
        };
    }

    private function dateString(mixed $date): string
    {
        return $date instanceof \DateTimeInterface
            ? CarbonImmutable::instance($date)->toDateString()
            : CarbonImmutable::parse((string) $date)->toDateString();
    }

    private function translation(string $key, ?string $fallback = null): string
    {
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return $fallback ? Str::of($fallback)->replace('_', ' ')->headline()->toString() : $key;
    }
}
