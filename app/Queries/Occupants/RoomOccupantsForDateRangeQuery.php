<?php

namespace App\Queries\Occupants;

use App\Data\Occupants\DateRange;
use App\Models\Room;
use App\Models\RoomOccupantSnapshot;
use Illuminate\Database\Eloquent\Builder;

final readonly class RoomOccupantsForDateRangeQuery
{
    /**
     * Build the privacy-safe room occupant snapshot query for a half-open stay date range.
     *
     * @return Builder<RoomOccupantSnapshot>
     */
    public function handle(Room $room, DateRange $range): Builder
    {
        return RoomOccupantSnapshot::query()
            ->select($this->summaryColumns())
            ->where('room_id', $room->id)
            ->visibleOccupants()
            ->overlapping($range->checkIn->toDateString(), $range->checkOut->toDateString())
            ->orderBy('check_out_date')
            ->orderBy('id');
    }

    /**
     * @return list<string>
     */
    private function summaryColumns(): array
    {
        return [
            'id',
            'room_id',
            'status',
            'check_in_date',
            'check_out_date',
            'public_alias_snapshot',
            'age_range_snapshot',
            'gender_for_room_policy_snapshot',
            'country_label_snapshot',
            'city_label_snapshot',
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
        ];
    }
}
