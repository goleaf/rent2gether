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
use App\Queries\Occupants\RoomOccupantsForDateRangeQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class RoomOccupantSummaryService
{
    public function __construct(private readonly RoomOccupantsForDateRangeQuery $roomOccupantsForDateRange) {}

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
            cards: $this->cards($confirmed ? $visibleForCards : $visibleForSummary),
            privacyNote: __('occupants.privacy_note'),
            confirmed: $confirmed,
        );
    }

    private function baseQuery(Room $room, DateRange $range): Builder
    {
        return $this->roomOccupantsForDateRange->handle($room, $range);
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

            if ($snapshot->age_range_snapshot) {
                $badges[] = __('occupants.values.age_range', ['age' => $snapshot->age_range_snapshot]);
            }

            if ($snapshot->gender_for_room_policy_snapshot) {
                $badges[] = $this->translatedOption('occupants.options.gender', $snapshot->gender_for_room_policy_snapshot);
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

        foreach ($this->summaryLines($snapshots) as $summaryLine) {
            $messages[] = $summaryLine;
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
                displayName: $this->displayName($snapshot),
                alias: $snapshot->public_alias_snapshot,
                ageRange: $snapshot->age_range_snapshot,
                languages: $this->languages($snapshot->languages_json_snapshot),
                languagesLabel: $this->languagesLabel($snapshot),
                checkoutDateLabel: $snapshot->check_out_date
                    ? __('occupants.checkout_date', ['date' => $snapshot->check_out_date->format('M j, Y')])
                    : null,
                roommateRating: $snapshot->roommate_rating_average_snapshot === null ? null : (float) $snapshot->roommate_rating_average_snapshot,
                roommateRatingLabel: $this->roommateRatingLabel($snapshot),
                roommateReviewsCount: (int) $snapshot->roommate_reviews_count_snapshot,
                badges: $this->cardBadges($snapshot),
                lines: $this->cardLines($snapshot),
                fields: $this->cardFields($snapshot),
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
     * @param  Collection<int, RoomOccupantSnapshot>  $snapshots
     * @return list<string>
     */
    private function summaryLines(Collection $snapshots): array
    {
        return $snapshots
            ->take(3)
            ->map(function (RoomOccupantSnapshot $snapshot): string {
                $details = array_values(array_filter([
                    $snapshot->age_range_snapshot,
                    $snapshot->gender_for_room_policy_snapshot
                        ? $this->translatedOption('occupants.options.gender', $snapshot->gender_for_room_policy_snapshot)
                        : null,
                    $this->locationLabel($snapshot),
                    $this->stayPurposeLabel($snapshot),
                    $snapshot->sleep_schedule_snapshot ? __('occupants.'.$snapshot->sleep_schedule_snapshot) : null,
                    $snapshot->home_presence_level_snapshot ? __('occupants.'.$snapshot->home_presence_level_snapshot) : null,
                    $this->smokingLabel($snapshot),
                    $snapshot->social_level_snapshot ? $this->socialLevelLabel($snapshot) : null,
                    $snapshot->prefers_quiet_snapshot === true ? __('occupants.quiet') : null,
                    $this->roommateRatingLabel($snapshot),
                    $snapshot->check_out_date ? __('occupants.checkout_date', ['date' => $snapshot->check_out_date->format('M j, Y')]) : null,
                ]));

                if ($details === []) {
                    return $this->displayName($snapshot);
                }

                return __('occupants.values.occupant_summary', [
                    'name' => $this->displayName($snapshot),
                    'details' => implode(', ', array_slice($details, 0, 7)),
                ]);
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    private function cardFields(RoomOccupantSnapshot $snapshot): array
    {
        $fields = [];

        $this->addField($fields, 'occupants.fields.age_range', $snapshot->age_range_snapshot);
        $this->addField($fields, 'occupants.fields.gender_for_room_policy', $snapshot->gender_for_room_policy_snapshot ? $this->translatedOption('occupants.options.gender', $snapshot->gender_for_room_policy_snapshot) : null);
        $this->addField($fields, 'occupants.fields.location', $this->locationLabel($snapshot));
        $this->addField($fields, 'occupants.fields.languages', $this->languagesLabel($snapshot));
        $this->addField($fields, 'occupants.fields.stay_purpose', $this->stayPurposeLabel($snapshot));
        $this->addField($fields, 'occupants.fields.guest_type', $this->guestTypeLabel($snapshot));
        $this->addField($fields, 'occupants.fields.sleep_schedule', $snapshot->sleep_schedule_snapshot ? __('occupants.'.$snapshot->sleep_schedule_snapshot) : null);
        $this->addField($fields, 'occupants.fields.wake_schedule', $this->wakeScheduleLabel($snapshot));
        $this->addField($fields, 'occupants.fields.home_presence_level', $snapshot->home_presence_level_snapshot ? __('occupants.'.$snapshot->home_presence_level_snapshot) : null);
        $this->addField($fields, 'occupants.fields.smoking_status', $this->smokingLabel($snapshot));
        $this->addField($fields, 'occupants.fields.social_level', $snapshot->social_level_snapshot ? $this->socialLevelLabel($snapshot) : null);
        $this->addField($fields, 'occupants.fields.quiet_preference', $snapshot->prefers_quiet_snapshot === true ? __('occupants.quiet') : null);
        $this->addField($fields, 'occupants.fields.roommate_rating', $this->roommateRatingLabel($snapshot));
        $this->addField($fields, 'occupants.fields.checkout_date', $snapshot->check_out_date ? __('occupants.checkout_date', ['date' => $snapshot->check_out_date->format('M j, Y')]) : null);

        return $fields;
    }

    /**
     * @param  list<array{label:string,value:string}>  $fields
     */
    private function addField(array &$fields, string $labelKey, ?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $fields[] = [
            'label' => __($labelKey),
            'value' => $value,
        ];
    }

    private function displayName(RoomOccupantSnapshot $snapshot): string
    {
        return $snapshot->public_alias_snapshot ?: __('occupants.values.private_occupant');
    }

    private function languagesLabel(RoomOccupantSnapshot $snapshot): ?string
    {
        $languages = $this->languages($snapshot->languages_json_snapshot);

        if ($languages === []) {
            return null;
        }

        return implode(', ', $languages);
    }

    private function locationLabel(RoomOccupantSnapshot $snapshot): ?string
    {
        if ($snapshot->country_label_snapshot && $snapshot->city_label_snapshot) {
            return __('occupants.values.country_city', [
                'country' => $snapshot->country_label_snapshot,
                'city' => $snapshot->city_label_snapshot,
            ]);
        }

        return $snapshot->city_label_snapshot ?: $snapshot->country_label_snapshot;
    }

    private function stayPurposeLabel(RoomOccupantSnapshot $snapshot): ?string
    {
        return $this->translatedOption('occupants.options.stay_purpose', $snapshot->stay_purpose_snapshot)
            ?? $this->translatedOption('occupants.purposes', $snapshot->stay_purpose_snapshot);
    }

    private function guestTypeLabel(RoomOccupantSnapshot $snapshot): ?string
    {
        $labels = collect($this->guestTypeKeys($snapshot))
            ->map(fn (string $key): string => __('occupants.'.$key))
            ->values()
            ->all();

        if ($labels === []) {
            return null;
        }

        return implode(', ', $labels);
    }

    private function wakeScheduleLabel(RoomOccupantSnapshot $snapshot): ?string
    {
        return $this->translatedOption('occupants.wake_schedules', $snapshot->wake_schedule_snapshot)
            ?? $this->translatedOption('occupants.options.schedule', $snapshot->wake_schedule_snapshot);
    }

    private function smokingLabel(RoomOccupantSnapshot $snapshot): ?string
    {
        if ($snapshot->smokes_snapshot === true) {
            return __('occupants.smokes');
        }

        if ($snapshot->smokes_snapshot === false) {
            return __('occupants.does_not_smoke');
        }

        return null;
    }

    private function socialLevelLabel(RoomOccupantSnapshot $snapshot): ?string
    {
        return $this->translatedOption('occupants.options.social', $snapshot->social_level_snapshot)
            ?? $this->translatedOption('occupants', $snapshot->social_level_snapshot);
    }

    private function roommateRatingLabel(RoomOccupantSnapshot $snapshot): ?string
    {
        if ($snapshot->roommate_rating_average_snapshot === null) {
            return null;
        }

        $reviewsCount = (int) $snapshot->roommate_reviews_count_snapshot;
        $rating = number_format((float) $snapshot->roommate_rating_average_snapshot, 1);

        if ($reviewsCount > 0) {
            return trans_choice('occupants.values.rating_with_reviews', $reviewsCount, [
                'rating' => $rating,
                'count' => $reviewsCount,
            ]);
        }

        return __('occupants.values.rating', ['rating' => $rating]);
    }

    private function translatedOption(string $prefix, ?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $key = $prefix.'.'.$value;

        if (Lang::has($key)) {
            return __($key);
        }

        return str($value)->replace('_', ' ')->ucfirst()->toString();
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
