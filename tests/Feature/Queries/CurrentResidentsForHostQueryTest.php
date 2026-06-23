<?php

namespace Tests\Feature\Queries;

use App\Models\Booking;
use App\Models\BookingStay;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Queries\Stays\CurrentResidentsForHostQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CurrentResidentsForHostQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_active_current_residents_for_the_selected_host_in_checkout_order(): void
    {
        $host = User::factory()->host()->create();
        $otherHost = User::factory()->host()->create();

        $laterStay = $this->createStayForHost($host, [
            'status' => 'active',
            'planned_check_out_date' => '2026-07-18',
        ]);
        $earlierStay = $this->createStayForHost($host, [
            'status' => 'active_with_warning',
            'planned_check_out_date' => '2026-07-15',
        ]);

        $this->createStayForHost($host, [
            'status' => 'completed',
            'planned_check_out_date' => '2026-07-14',
        ]);
        $this->createStayForHost($otherHost, [
            'status' => 'active',
            'planned_check_out_date' => '2026-07-13',
        ]);

        $stays = $this->runQuery($host);

        $this->assertSame([$earlierStay->id, $laterStay->id], $stays->pluck('id')->all());
        $this->assertTrue($stays->first()->relationLoaded('guest'));
        $this->assertTrue($stays->first()->relationLoaded('property'));
        $this->assertTrue($stays->first()->relationLoaded('room'));
        $this->assertTrue($stays->first()->relationLoaded('sleepingPlace'));
    }

    public function test_it_can_include_a_non_active_status_when_status_filter_is_explicit(): void
    {
        $host = User::factory()->host()->create();

        $completedStay = $this->createStayForHost($host, ['status' => 'completed']);
        $this->createStayForHost($host, ['status' => 'active']);

        $stays = $this->runQuery($host, ['status' => 'completed']);

        $this->assertSame([$completedStay->id], $stays->pluck('id')->all());
    }

    public function test_it_filters_current_residents_by_property_room_and_attention_scope(): void
    {
        $host = User::factory()->host()->create();
        $firstListing = $this->listingForHost($host);
        $secondListing = $this->listingForHost($host);

        $matchingStay = $this->createStay($firstListing, [
            'has_payment_problem' => true,
            'planned_check_out_date' => '2026-07-15',
        ]);
        $this->createStay($firstListing, [
            'has_payment_problem' => false,
            'planned_check_out_date' => '2026-07-16',
        ]);
        $this->createStay($secondListing, [
            'has_payment_problem' => true,
            'planned_check_out_date' => '2026-07-14',
        ]);

        $stays = $this->runQuery($host, [
            'scope' => 'payment_issue',
            'property_id' => $matchingStay->property_id,
            'room_id' => $matchingStay->room_id,
        ]);

        $this->assertSame([$matchingStay->id], $stays->pluck('id')->all());
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, BookingStay>
     */
    private function runQuery(User $host, array $filters = []): Collection
    {
        return app(CurrentResidentsForHostQuery::class)
            ->handle($host, $filters)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createStayForHost(User $host, array $overrides = []): BookingStay
    {
        return $this->createStay($this->listingForHost($host), $overrides);
    }

    /**
     * @return array{guest: User, host: User, property: Property, room: Room, place: SleepingPlace}
     */
    private function listingForHost(User $host): array
    {
        $guest = User::factory()->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'user_id' => $host->id,
                'host_user_id' => $host->id,
            ]);
        $room = Room::factory()
            ->for($property)
            ->create([
                'user_id' => $host->id,
            ]);
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'user_id' => $host->id,
            ]);

        return [
            'guest' => $guest,
            'host' => $host,
            'property' => $property,
            'room' => $room,
            'place' => $place,
        ];
    }

    /**
     * @param  array{guest: User, host: User, property: Property, room: Room, place: SleepingPlace}  $listing
     * @param  array<string, mixed>  $overrides
     */
    private function createStay(array $listing, array $overrides = []): BookingStay
    {
        $booking = Booking::factory()
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create([
                'guest_user_id' => $listing['guest']->id,
                'host_user_id' => $listing['host']->id,
                'property_id' => $listing['property']->id,
                'room_id' => $listing['room']->id,
                'sleeping_place_id' => $listing['place']->id,
            ]);

        return BookingStay::factory()
            ->for($booking)
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create(array_merge([
                'guest_user_id' => $listing['guest']->id,
                'host_user_id' => $listing['host']->id,
                'property_id' => $listing['property']->id,
                'room_id' => $listing['room']->id,
                'sleeping_place_id' => $listing['place']->id,
                'status' => 'active',
                'planned_check_out_date' => '2026-07-20',
            ], $overrides));
    }
}
