<?php

namespace Tests\Feature\Queries;

use App\Data\Occupants\DateRange;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomOccupantSnapshot;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Queries\Occupants\RoomOccupantsForDateRangeQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RoomOccupantsForDateRangeQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_visible_occupants_overlapping_the_room_date_range_in_checkout_order(): void
    {
        $room = $this->room();
        $otherRoom = $this->room();

        $later = $this->snapshot($room, [
            'status' => RoomOccupantSnapshot::STATUS_UPCOMING,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-15',
        ]);
        $earlier = $this->snapshot($room, [
            'status' => RoomOccupantSnapshot::STATUS_LEAVING_SOON,
            'check_in_date' => '2026-07-09',
            'check_out_date' => '2026-07-13',
        ]);

        $this->snapshot($room, [
            'status' => RoomOccupantSnapshot::STATUS_CHECKED_OUT,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-14',
        ]);
        $this->snapshot($room, [
            'status' => RoomOccupantSnapshot::STATUS_UPCOMING,
            'check_in_date' => '2026-07-15',
            'check_out_date' => '2026-07-18',
        ]);
        $this->snapshot($room, [
            'status' => RoomOccupantSnapshot::STATUS_UPCOMING,
            'check_in_date' => '2026-07-07',
            'check_out_date' => '2026-07-10',
        ]);
        $this->snapshot($otherRoom, [
            'status' => RoomOccupantSnapshot::STATUS_UPCOMING,
            'check_in_date' => '2026-07-11',
            'check_out_date' => '2026-07-14',
        ]);

        $snapshots = $this->runQuery($room, new DateRange('2026-07-10', '2026-07-15'));

        $this->assertSame([$earlier->id, $later->id], $snapshots->pluck('id')->all());
    }

    public function test_it_selects_only_the_privacy_safe_snapshot_summary_columns(): void
    {
        $room = $this->room();
        $this->snapshot($room, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-15',
            'gender_for_room_policy_snapshot' => 'female',
            'country_label_snapshot' => 'Lithuania',
            'city_label_snapshot' => 'Vilnius',
        ]);

        $snapshot = $this->runQuery($room, new DateRange('2026-07-10', '2026-07-15'))->firstOrFail();
        $attributes = $snapshot->getAttributes();

        $this->assertArrayHasKey('public_alias_snapshot', $attributes);
        $this->assertArrayHasKey('gender_for_room_policy_snapshot', $attributes);
        $this->assertArrayHasKey('country_label_snapshot', $attributes);
        $this->assertArrayHasKey('city_label_snapshot', $attributes);
        $this->assertArrayHasKey('languages_json_snapshot', $attributes);
        $this->assertArrayHasKey('can_show_before_booking', $attributes);
        $this->assertArrayNotHasKey('booking_id', $attributes);
        $this->assertArrayNotHasKey('user_id', $attributes);
        $this->assertArrayNotHasKey('sleeping_place_id', $attributes);
        $this->assertArrayNotHasKey('created_at', $attributes);
        $this->assertArrayNotHasKey('updated_at', $attributes);
    }

    /**
     * @return Collection<int, RoomOccupantSnapshot>
     */
    private function runQuery(Room $room, DateRange $range): Collection
    {
        return app(RoomOccupantsForDateRangeQuery::class)
            ->handle($room, $range)
            ->get();
    }

    private function room(): Room
    {
        $host = User::factory()->host()->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
            ]);

        return Room::factory()
            ->for($property)
            ->create([
                'user_id' => $host->id,
                'sleeping_places_count' => 4,
            ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function snapshot(Room $room, array $overrides = []): RoomOccupantSnapshot
    {
        $guest = User::factory()->create();
        $property = $room->property ?: Property::query()->findOrFail($room->property_id);
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create();
        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($property)
            ->for($room)
            ->for($place, 'sleepingPlace')
            ->create([
                'guest_user_id' => $guest->id,
                'property_id' => $property->id,
                'room_id' => $room->id,
                'sleeping_place_id' => $place->id,
                'check_in_date' => $overrides['check_in_date'] ?? '2026-07-10',
                'check_out_date' => $overrides['check_out_date'] ?? '2026-07-15',
            ]);

        return RoomOccupantSnapshot::factory()
            ->for($room)
            ->for($place, 'sleepingPlace')
            ->for($booking)
            ->for($guest, 'user')
            ->create(array_merge([
                'room_id' => $room->id,
                'sleeping_place_id' => $place->id,
                'booking_id' => $booking->id,
                'user_id' => $guest->id,
                'status' => RoomOccupantSnapshot::STATUS_UPCOMING,
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-15',
            ], $overrides));
    }
}
