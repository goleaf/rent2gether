<?php

namespace App\Services\HostOccupants;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\HostCurrentStaySnapshot;
use App\Models\HostGuestStayFlag;
use App\Models\HostGuestStayNote;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostOccupants\Data\HostOccupantData;
use App\Services\HostOccupants\Data\HostOccupantFilters;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class HostCurrentOccupantsService
{
    public function __construct(
        private readonly HostCurrentStaySnapshotService $snapshots,
        private readonly HostOccupantFilterService $filters,
        private readonly HostOccupantPrivacyService $privacy,
        private readonly HostGuestStayFlagService $flags,
        private readonly HostGuestStayNoteService $notes,
    ) {}

    public function getCurrentOccupants(User $host, HostOccupantFilters $filters): Collection
    {
        return $this->currentOccupantsQuery($host, $filters)->get();
    }

    public function paginateCurrentOccupants(User $host, HostOccupantFilters $filters, int $perPage = 10, string $pageName = 'currentOccupantsPage'): Paginator
    {
        $paginator = $this->currentOccupantsQuery($host, $filters)
            ->simplePaginate($perPage, pageName: $pageName);

        $paginator->setCollection(
            $this->hydrateOccupantData($host, $paginator->getCollection())
                ->map(fn (HostOccupantData $occupant): array => $occupant->toArray()),
        );

        return $paginator;
    }

    /**
     * @return Builder<HostCurrentStaySnapshot>
     */
    public function currentOccupantsQuery(User $host, HostOccupantFilters $filters): Builder
    {
        $this->refreshCurrentBookings($host);

        $query = HostCurrentStaySnapshot::query()
            ->select([
                'id',
                'user_id',
                'guest_user_id',
                'booking_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'guest_display_name',
                'guest_avatar_url',
                'room_label',
                'sleeping_place_label',
                'check_in_date',
                'check_out_date',
                'nights_count',
                'nights_left',
                'payment_status',
                'stay_status',
                'check_in_status',
                'has_special_requests',
                'special_requests_summary',
                'guest_rating_average',
                'has_complaints',
                'open_complaints_count',
                'needs_extension',
                'needs_checkout',
                'checkout_due_today',
                'checkout_overdue',
                'needs_cleaning_after_checkout',
                'needs_inspection',
                'last_host_note',
                'last_activity_at',
            ])
            ->with([
                'booking:id,host_user_id,guest_user_id,status',
            ])
            ->forHost($host)
            ->whereNotIn('stay_status', ['checked_out', 'cancelled', 'no_show'])
            ->where(function (Builder $current): void {
                $today = CarbonImmutable::today()->toDateString();

                $current
                    ->where(function (Builder $range) use ($today): void {
                        $range
                            ->where('check_in_date', '<=', $today)
                            ->where('check_out_date', '>=', $today);
                    })
                    ->orWhereIn('stay_status', ['checked_in', 'living_now', 'check_out_today', 'checkout_overdue']);
            })
            ->orderBy('check_out_date')
            ->orderBy('room_label')
            ->orderBy('sleeping_place_label')
            ->orderBy('id');

        return $this->filters->apply($query, $filters);
    }

    public function getOccupantDetails(User $host, Booking $booking): HostOccupantData
    {
        if (! $this->privacy->canViewOccupant($host, $booking)) {
            throw new AuthorizationException;
        }

        $snapshot = $this->snapshots->refreshForBooking($booking);
        $flags = $this->flags->getOpenFlags($host, $booking);
        $notes = $this->notes->getNotesForBooking($host, $booking);

        return HostOccupantData::fromSnapshot(
            $snapshot,
            $flags,
            $notes,
            $this->privacy->filterGuestContactForHost($host, $booking),
        );
    }

    public function getByProperty(User $host, Property $property): Collection
    {
        if ((int) $property->host_user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }

        return $this->getCurrentOccupants($host, new HostOccupantFilters(propertyId: $property->id));
    }

    public function getByRoom(User $host, Room $room): Collection
    {
        $room->loadMissing('property:id,host_user_id');

        if ((int) $room->property?->host_user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }

        return $this->getCurrentOccupants($host, new HostOccupantFilters(roomId: $room->id));
    }

    public function getBySleepingPlace(User $host, SleepingPlace $place): Collection
    {
        $place->loadMissing('property:id,host_user_id');

        if ((int) $place->property?->host_user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }

        return $this->getCurrentOccupants($host, new HostOccupantFilters(sleepingPlaceId: $place->id));
    }

    public function getTodayCheckIns(User $host): Collection
    {
        return $this->getCurrentOccupants($host, new HostOccupantFilters(scope: 'check_ins_today'));
    }

    public function getTodayCheckOuts(User $host): Collection
    {
        return $this->getCurrentOccupants($host, new HostOccupantFilters(scope: 'check_outs_today'));
    }

    public function getNeedsAttention(User $host): Collection
    {
        return $this->getCurrentOccupants($host, new HostOccupantFilters(onlyNeedsAttention: true));
    }

    private function refreshCurrentBookings(User $host): void
    {
        $today = CarbonImmutable::today()->toDateString();

        Booking::query()
            ->where('host_user_id', $host->id)
            ->whereNotIn('status', $this->closedStatusValues())
            ->where(function (Builder $query) use ($today): void {
                $query
                    ->where(function (Builder $range) use ($today): void {
                        $range
                            ->where('check_in_date', '<=', $today)
                            ->where('check_out_date', '>=', $today);
                    })
                    ->orWhereIn('status', [
                        BookingStatus::CheckedIn->value,
                        BookingStatus::InProgress->value,
                        BookingStatus::ActiveStay->value,
                        BookingStatus::LeavingSoon->value,
                    ]);
            })
            ->get()
            ->each(function (Booking $booking): void {
                $this->snapshots->refreshForBooking($booking);
            });
    }

    /**
     * @param  Collection<int, HostCurrentStaySnapshot>  $snapshots
     * @return Collection<int, HostOccupantData>
     */
    private function hydrateOccupantData(User $host, Collection $snapshots): Collection
    {
        $bookingIds = $snapshots
            ->pluck('booking_id')
            ->filter()
            ->unique()
            ->values();

        $flagsByBooking = $bookingIds->isEmpty()
            ? collect()
            : HostGuestStayFlag::query()
                ->where('user_id', $host->id)
                ->whereIn('booking_id', $bookingIds)
                ->where('status', 'open')
                ->orderByDesc('severity')
                ->orderBy('id')
                ->get()
                ->groupBy('booking_id');

        $notesByBooking = $bookingIds->isEmpty()
            ? collect()
            : HostGuestStayNote::query()
                ->where('user_id', $host->id)
                ->whereIn('booking_id', $bookingIds)
                ->orderByDesc('is_pinned')
                ->orderByDesc('id')
                ->get()
                ->groupBy('booking_id');

        return $snapshots->map(function (HostCurrentStaySnapshot $snapshot) use ($flagsByBooking, $host, $notesByBooking): HostOccupantData {
            $contact = $snapshot->booking
                ? $this->privacy->filterGuestContactForHost($host, $snapshot->booking)
                : ['chat' => false, 'phone' => null, 'email' => null];

            return HostOccupantData::fromSnapshot(
                $snapshot,
                $flagsByBooking->get($snapshot->booking_id, collect()),
                $notesByBooking->get($snapshot->booking_id, collect()),
                $contact,
            );
        });
    }

    /**
     * @return list<string>
     */
    private function closedStatusValues(): array
    {
        return [
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::Closed->value,
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::CancelledByGuest->value,
            BookingStatus::CancelledByHost->value,
            BookingStatus::CancelledBySystem->value,
            BookingStatus::CancelledByService->value,
            BookingStatus::DeclinedByHost->value,
            BookingStatus::Expired->value,
            BookingStatus::NoShow->value,
        ];
    }
}
