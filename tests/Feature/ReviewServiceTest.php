<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use App\Services\Reviews\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createCompletedBooking(): Booking
    {
        $host = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $host->id]);
        $room = Room::factory()->for($property)->create();
        $bed = Bed::factory()->for($room)->create();

        return Booking::factory()->create([
            'bed_id' => $bed->id,
            'guest_id' => User::factory()->create()->id,
            'host_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'status' => BookingStatus::Completed->value,
        ]);
    }

    public function test_create_guest_review(): void
    {
        $booking = $this->createCompletedBooking();

        $guest = User::find($booking->guest_id);
        $review = app(ReviewService::class)->createGuestReview($booking, $guest, [
            'overall' => 4,
            'cleanliness' => 5,
        ], 'Great place!');

        $this->assertInstanceOf(Review::class, $review);
        $this->assertSame(4, $review->overall_rating);
        $this->assertSame('guest_to_place', $review->type->value);
    }

    public function test_create_host_review(): void
    {
        $booking = $this->createCompletedBooking();

        $host = User::find($booking->host_id);
        $review = app(ReviewService::class)->createHostReview($booking, $host, [
            'overall' => 5,
        ], 'Perfect guest.');

        $this->assertInstanceOf(Review::class, $review);
        $this->assertSame('host_to_guest', $review->type->value);
        $this->assertSame($booking->guest_id, $review->reviewee_id);
    }
}
