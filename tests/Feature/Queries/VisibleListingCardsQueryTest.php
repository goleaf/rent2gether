<?php

namespace Tests\Feature\Queries;

use App\Data\Listings\ListingCardContext;
use App\Enums\PropertyStatus;
use App\Enums\ReviewStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Queries\Listings\VisibleListingCardsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisibleListingCardsQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_active_listing_cards_with_review_aggregates_and_card_relations(): void
    {
        $visible = $this->createPlace('Visible place');
        $this->createPlace('Hidden sleeping place', [
            'status' => SleepingPlaceStatus::Hidden->value,
        ]);
        $this->createPlace('Hidden room place', roomOverrides: [
            'status' => RoomStatus::Hidden->value,
        ]);
        $this->createPlace('Hidden property place', propertyOverrides: [
            'status' => PropertyStatus::Hidden->value,
        ]);

        $booking = Booking::factory()
            ->for($visible->property)
            ->for($visible->room)
            ->for($visible, 'sleepingPlace')
            ->create([
                'property_id' => $visible->property_id,
                'room_id' => $visible->room_id,
                'sleeping_place_id' => $visible->id,
            ]);

        Review::factory()->for($booking)->for($visible, 'sleepingPlace')->create([
            'bed_id' => null,
            'property_id' => $visible->property_id,
            'room_id' => $visible->room_id,
            'status' => ReviewStatus::Published->value,
            'overall_rating' => 4.0,
            'cleanliness_rating' => 5.0,
            'safety_rating' => 3.0,
        ]);

        $places = app(VisibleListingCardsQuery::class)
            ->handle($this->context())
            ->get();

        $this->assertSame([$visible->id], $places->pluck('id')->all());

        $loaded = $places->firstOrFail();

        $this->assertTrue($loaded->relationLoaded('translations'));
        $this->assertTrue($loaded->relationLoaded('room'));
        $this->assertTrue($loaded->room->relationLoaded('translations'));
        $this->assertTrue($loaded->room->relationLoaded('currentOccupancySnapshot'));
        $this->assertTrue($loaded->relationLoaded('property'));
        $this->assertTrue($loaded->property->relationLoaded('host'));
        $this->assertTrue($loaded->property->relationLoaded('accessDetails'));
        $this->assertSame(1, $loaded->published_reviews_count);
        $this->assertSame(4.0, (float) $loaded->published_reviews_rating);
        $this->assertSame(5.0, (float) $loaded->published_cleanliness_rating);
        $this->assertSame(3.0, (float) $loaded->published_safety_rating);
    }

    public function test_it_loads_only_price_override_days_inside_the_selected_date_range(): void
    {
        $place = $this->createPlace('Date aware place');

        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-10',
            'price_override' => 21,
        ]);
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-11',
            'price_override' => null,
        ]);
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-13',
            'price_override' => 25,
        ]);

        $loadedWithDates = app(VisibleListingCardsQuery::class)
            ->handle($this->context(checkIn: '2026-07-10', checkOut: '2026-07-13'))
            ->whereKey($place->id)
            ->firstOrFail();

        $this->assertTrue($loadedWithDates->relationLoaded('availabilityDays'));
        $this->assertSame(
            ['2026-07-10'],
            $loadedWithDates->availabilityDays
                ->map(fn (AvailabilityDay $day): string => $day->date->toDateString())
                ->all(),
        );

        $loadedWithoutDates = app(VisibleListingCardsQuery::class)
            ->handle($this->context(checkIn: null, checkOut: null))
            ->whereKey($place->id)
            ->firstOrFail();

        $this->assertFalse($loadedWithoutDates->relationLoaded('availabilityDays'));
    }

    private function context(?string $checkIn = null, ?string $checkOut = null): ListingCardContext
    {
        return new ListingCardContext(
            locale: 'en',
            currency: 'EUR',
            checkInDate: $checkIn,
            checkOutDate: $checkOut,
            source: 'query-test',
        );
    }

    /**
     * @param  array<string, mixed>  $placeOverrides
     * @param  array<string, mixed>  $roomOverrides
     * @param  array<string, mixed>  $propertyOverrides
     */
    private function createPlace(
        string $title,
        array $placeOverrides = [],
        array $roomOverrides = [],
        array $propertyOverrides = [],
    ): SleepingPlace {
        $host = User::factory()->host()->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->create(array_merge([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'status' => PropertyStatus::Active->value,
            ], $propertyOverrides));
        $room = Room::factory()
            ->for($property)
            ->create(array_merge([
                'user_id' => $host->id,
                'status' => RoomStatus::Active->value,
            ], $roomOverrides));
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create(array_merge([
                'user_id' => $host->id,
                'status' => SleepingPlaceStatus::Active->value,
                'display_name' => $title,
            ], $placeOverrides));

        $place->translations()->create([
            'locale' => 'en',
            'title' => $title,
            'summary' => 'Summary '.$title,
        ]);

        return $place;
    }
}
