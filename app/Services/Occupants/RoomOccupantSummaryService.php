<?php

namespace App\Services\Occupants;

use App\Data\Occupants\DateRange;
use App\Data\Occupants\RoomOccupantData;
use App\Data\Occupants\RoomOccupantSummaryData;
use App\Data\Occupants\RoomOccupantWarningData;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomOccupantSnapshot;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RoomOccupantSummaryService
{
    public function getSummaryForRoom(Room $room, DateRange $range, ?User $viewer = null): RoomOccupantSummaryData
    {
        return $this->buildSummary($room, $range, null, null, false);
    }

    public function getSummaryForSleepingPlace(SleepingPlace $place, DateRange $range, ?User $viewer = null): RoomOccupantSummaryData
    {
        $place->loadMissing('room:id,sleeping_places_count');

        return $this->buildSummary($place->room, $range, $place->id, null, false);
    }

    public function getPreBookingSummary(Room $room, DateRange $range): RoomOccupantSummaryData
    {
        return $this->buildSummary($room, $range, null, null, false);
    }

    public function getConfirmedBookingSummary(Room $room, DateRange $range, User $guest, Booking $booking): RoomOccupantSummaryData
    {
        return $this->buildSummary($room, $range, null, $booking, true);
    }

    public function countOccupantsForDates(Room $room, DateRange $range): int
    {
        if (! $range->valid()) {
            return 0;
        }

        return $this->baseQuery($room, $range)->count();
    }

    /**
     * @return Collection<int, RoomOccupantSnapshot>
     */
    public function getPrivacySafeOccupants(Room $room, DateRange $range, ?Booking $booking = null): Collection
    {
        return $this->baseQuery($room, $range)
            ->when($booking, fn (Builder $query): Builder => $query->where('booking_id', '!=', $booking->id))
            ->get();
    }

    /**
     * @return list<RoomOccupantWarningData>
     */
    public function getWarnings(Room $room, DateRange $range, User $guest): array
    {
        $occupants = $this->getPrivacySafeOccupants($room, $range);
        $warnings = [];

        if ($occupants->count() >= max(3, (int) $room->sleeping_places_count - 1)) {
            $warnings[] = new RoomOccupantWarningData('room_full', __('occupants.warnings.room_full'));
        }

        if ($guest->prefers_quiet === false && $occupants->contains(fn (RoomOccupantSnapshot $snapshot): bool => $snapshot->prefers_quiet_snapshot === true)) {
            $warnings[] = new RoomOccupantWarningData('quiet_conflict', __('occupants.warnings.quiet_conflict'));
        }

        if ($guest->is_smoker === false && $occupants->contains(fn (RoomOccupantSnapshot $snapshot): bool => $snapshot->smokes_snapshot === true)) {
            $warnings[] = new RoomOccupantWarningData('smoking_conflict', __('occupants.warnings.smoking_conflict'));
        }

        return $warnings;
    }

    private function buildSummary(Room $room, DateRange $range, ?int $excludeSleepingPlaceId, ?Booking $booking, bool $confirmed): RoomOccupantSummaryData
    {
        if (! $range->valid()) {
            return new RoomOccupantSummaryData(0, [], [__('occupants.no_occupants')], [], [], __('occupants.privacy_note'), $confirmed);
        }

        $query = $this->baseQuery($room, $range)
            ->when($excludeSleepingPlaceId, fn (Builder $builder): Builder => $builder->where('sleeping_place_id', '!=', $excludeSleepingPlaceId))
            ->when($booking, fn (Builder $builder): Builder => $builder->where('booking_id', '!=', $booking->id));

        $snapshots = $query->get();
        $visibleForSummary = $snapshots->filter(fn (RoomOccupantSnapshot $snapshot): bool => $snapshot->can_show_before_booking);
        $visibleForCards = $snapshots->filter(fn (RoomOccupantSnapshot $snapshot): bool => $snapshot->can_show_after_booking);
        $count = $snapshots->count();

        return new RoomOccupantSummaryData(
            occupantsCount: $count,
            badges: $this->badges($visibleForSummary),
            messages: $this->messages($count, $visibleForSummary),
            warnings: $this->aggregateWarnings($room, $snapshots),
            cards: $confirmed ? $this->cards($visibleForCards) : [],
            privacyNote: __('occupants.privacy_note'),
            confirmed: $confirmed,
        );
    }

    private function baseQuery(Room $room, DateRange $range): Builder
    {
        return RoomOccupantSnapshot::query()
            ->select([
                'id',
                'room_id',
                'sleeping_place_id',
                'booking_id',
                'user_id',
                'status',
                'check_in_date',
                'check_out_date',
                'public_alias_snapshot',
                'age_range_snapshot',
                'languages_json_snapshot',
                'stay_purpose_snapshot',
                'guest_type_snapshot',
                'tourist_snapshot',
                'student_snapshot',
                'working_snapshot',
                'remote_worker_snapshot',
                'long_term_guest_snapshot',
                'short_term_guest_snapshot',
                'sleep_schedule_snapshot',
                'wake_schedule_snapshot',
                'home_presence_level_snapshot',
                'smokes_snapshot',
                'social_level_snapshot',
                'prefers_quiet_snapshot',
                'roommate_rating_average_snapshot',
                'roommate_reviews_count_snapshot',
                'can_show_before_booking',
                'can_show_after_booking',
            ])
            ->where('room_id', $room->id)
            ->visibleOccupants()
            ->overlapping($range->checkIn->toDateString(), $range->checkOut->toDateString())
            ->orderBy('check_out_date')
            ->orderBy('id');
    }

    /**
     * @param  Collection<int, RoomOccupantSnapshot>  $snapshots
     * @return list<string>
     */
    private function badges(Collection $snapshots): array
    {
        $badges = [];

        foreach ($snapshots as $snapshot) {
            foreach ($this->guestTypeKeys($snapshot) as $key) {
                $badges[] = __('occupants.'.$key);
            }

            if ($snapshot->prefers_quiet_snapshot === true) {
                $badges[] = __('occupants.quiet');
            }

            if ($snapshot->smokes_snapshot === true) {
                $badges[] = __('occupants.smokes');
            } elseif ($snapshot->smokes_snapshot === false) {
                $badges[] = __('occupants.does_not_smoke');
            }

            if ($snapshot->home_presence_level_snapshot) {
                $badges[] = __('occupants.'.$snapshot->home_presence_level_snapshot);
            }
        }

        return array_values(array_slice(array_unique(array_filter($badges)), 0, 8));
    }

    /**
     * @param  Collection<int, RoomOccupantSnapshot>  $snapshots
     * @return list<string>
     */
    private function messages(int $count, Collection $snapshots): array
    {
        if ($count === 0) {
            return [__('occupants.no_occupants')];
        }

        $messages = [
            trans_choice('occupants.occupants_count', $count, ['count' => $count]),
        ];

        $languages = $snapshots
            ->flatMap(fn (RoomOccupantSnapshot $snapshot): array => $this->languages($snapshot->languages_json_snapshot))
            ->unique()
            ->values()
            ->all();

        if ($languages !== []) {
            $messages[] = __('occupants.languages', ['languages' => implode(', ', $languages)]);
        }

        return $messages;
    }

    /**
     * @param  Collection<int, RoomOccupantSnapshot>  $snapshots
     * @return list<RoomOccupantWarningData>
     */
    private function aggregateWarnings(Room $room, Collection $snapshots): array
    {
        $warnings = [];

        if ($snapshots->count() >= max(3, (int) $room->sleeping_places_count - 1)) {
            $warnings[] = new RoomOccupantWarningData('room_full', __('occupants.warnings.room_full'));
        }

        return $warnings;
    }

    /**
     * @param  Collection<int, RoomOccupantSnapshot>  $snapshots
     * @return list<RoomOccupantData>
     */
    private function cards(Collection $snapshots): array
    {
        return $snapshots
            ->map(fn (RoomOccupantSnapshot $snapshot): RoomOccupantData => new RoomOccupantData(
                snapshotId: $snapshot->id,
                userId: $snapshot->user_id,
                bookingId: $snapshot->booking_id,
                alias: $snapshot->public_alias_snapshot,
                ageRange: $snapshot->age_range_snapshot,
                languages: $this->languages($snapshot->languages_json_snapshot),
                checkoutDateLabel: $snapshot->check_out_date
                    ? __('occupants.checkout_date', ['date' => $snapshot->check_out_date->format('M j, Y')])
                    : null,
                roommateRating: $snapshot->roommate_rating_average_snapshot === null ? null : (float) $snapshot->roommate_rating_average_snapshot,
                roommateReviewsCount: (int) $snapshot->roommate_reviews_count_snapshot,
                badges: $this->cardBadges($snapshot),
                lines: $this->cardLines($snapshot),
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function cardBadges(RoomOccupantSnapshot $snapshot): array
    {
        $badges = [];

        foreach ($this->guestTypeKeys($snapshot) as $key) {
            $badges[] = __('occupants.'.$key);
        }

        if ($snapshot->prefers_quiet_snapshot === true) {
            $badges[] = __('occupants.quiet');
        }

        if ($snapshot->smokes_snapshot === true) {
            $badges[] = __('occupants.smokes');
        } elseif ($snapshot->smokes_snapshot === false) {
            $badges[] = __('occupants.does_not_smoke');
        }

        return array_values(array_unique(array_filter($badges)));
    }

    /**
     * @return list<string>
     */
    private function cardLines(RoomOccupantSnapshot $snapshot): array
    {
        return array_values(array_filter([
            $snapshot->sleep_schedule_snapshot ? __('occupants.'.$snapshot->sleep_schedule_snapshot) : null,
            $snapshot->home_presence_level_snapshot ? __('occupants.'.$snapshot->home_presence_level_snapshot) : null,
            $snapshot->check_out_date ? __('occupants.checkout_date', ['date' => $snapshot->check_out_date->format('M j, Y')]) : null,
        ]));
    }

    /**
     * @return list<string>
     */
    private function guestTypeKeys(RoomOccupantSnapshot $snapshot): array
    {
        return array_values(array_filter([
            $snapshot->long_term_guest_snapshot ? 'long_term_guest' : null,
            $snapshot->short_term_guest_snapshot ? 'short_term_guest' : null,
            $snapshot->tourist_snapshot ? 'tourist' : null,
            $snapshot->student_snapshot ? 'student' : null,
            $snapshot->working_snapshot ? 'working' : null,
            $snapshot->remote_worker_snapshot ? 'remote_worker' : null,
            $snapshot->guest_type_snapshot && ! in_array($snapshot->guest_type_snapshot, ['long_term_guest', 'short_term_guest', 'tourist', 'student', 'working', 'remote_worker'], true)
                ? $snapshot->guest_type_snapshot
                : null,
        ]));
    }

    /**
     * @return list<string>
     */
    private function languages(mixed $languages): array
    {
        if (! is_array($languages)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $language): string => strtoupper((string) $language),
            $languages,
        )));
    }
}
