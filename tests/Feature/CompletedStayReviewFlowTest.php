<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Places\SleepingPlaceReviews;
use App\Livewire\Reviews\CreateReview;
use App\Models\Booking;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Reviews\ReviewService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CompletedStayReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-20 10:00:00');
        CarbonImmutable::setTestNow('2026-06-20 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_cannot_review_before_completed(): void
    {
        [$booking, $guest] = $this->createStayBooking(BookingStatus::Confirmed);

        $this->expectException(ValidationException::class);

        try {
            app(ReviewService::class)->createGuestReview($booking, $guest, [
                'overall' => 5,
            ], 'The place was calm.');
        } finally {
            $this->assertSame(0, Review::query()->count());
        }
    }

    public function test_guest_review_creates_ratings_and_waits_for_second_review(): void
    {
        [$booking, $guest, , $place] = $this->createStayBooking();

        $review = app(ReviewService::class)->createGuestReview(
            booking: $booking,
            guest: $guest,
            ratings: [
                'overall' => 4,
                'cleanliness' => 5,
                'safety' => 4,
                'location' => 5,
                'accuracy' => 4,
                'sleeping_place_comfort' => 5,
                'amenities' => 4,
                'host_communication' => 5,
                'neighbors' => 4,
                'value' => 4,
            ],
            likedText: 'Quiet and clean sleeping place.',
            improvementText: 'The shelf could be larger.',
            adviceText: 'Bring earplugs if you sleep very lightly.',
            recommend: true,
            photos: ['review-photos/example.jpg'],
        );

        $this->assertTrue($review->type === ReviewType::GuestToPlace);
        $this->assertTrue($review->status === ReviewStatus::Pending);
        $this->assertSame($guest->id, $review->guest_user_id);
        $this->assertSame($place->id, $review->sleeping_place_id);
        $this->assertSame(5, $review->sleeping_place_comfort_rating);
        $this->assertSame(5, $review->host_communication_rating);
        $this->assertSame('Quiet and clean sleeping place.', $review->liked_text);
        $this->assertSame(['review-photos/example.jpg'], $review->photos_json);
        $this->assertTrue($booking->refresh()->guest_review_left);
    }

    public function test_host_review_creates_ratings(): void
    {
        [$booking, , $host] = $this->createStayBooking();

        $review = app(ReviewService::class)->createHostReview(
            booking: $booking,
            host: $host,
            ratings: [
                'overall' => 5,
                'rule_following' => 5,
                'cleanliness' => 4,
                'communication' => 5,
                'punctuality' => 5,
                'respect' => 5,
            ],
            comment: 'Respectful and easy to host.',
            recommendGuest: true,
        );

        $this->assertTrue($review->type === ReviewType::HostToGuest);
        $this->assertTrue($review->status === ReviewStatus::Pending);
        $this->assertSame(5, $review->rule_following_rating);
        $this->assertSame(5, $review->respect_rating);
        $this->assertTrue($review->recommend_guest);
        $this->assertTrue($booking->refresh()->host_review_left);
    }

    public function test_aggregate_rating_updates_after_both_reviews_are_submitted(): void
    {
        [$booking, $guest, $host] = $this->createStayBooking();

        app(ReviewService::class)->createGuestReview($booking, $guest, [
            'overall' => 4,
        ], 'Quiet and clean sleeping place.');

        app(ReviewService::class)->createHostReview($booking->refresh(), $host, [
            'overall' => 5,
        ], 'Respectful guest.');

        $this->assertSame(2, Review::query()->published()->count());
        $this->assertSame(4.0, (float) $host->refresh()->hostProfile->rating_average);
        $this->assertSame(1, $host->hostProfile->reviews_count);
        $this->assertSame(4.0, (float) $host->rating_as_host);
        $this->assertSame(5.0, (float) $guest->refresh()->profile->rating_average);
        $this->assertSame(1, $guest->profile->reviews_count);
        $this->assertSame(5.0, (float) $guest->rating_as_guest);
    }

    public function test_review_forms_and_public_labels_are_localized(): void
    {
        [$booking, $guest, $host] = $this->createStayBooking();

        $this->actingAs($guest)
            ->get(route('reviews.create', ['locale' => 'en', 'booking' => $booking]))
            ->assertOk()
            ->assertSeeLivewire(CreateReview::class)
            ->assertSee(__('booking.review.guest_title', [], 'en'))
            ->assertSee(__('booking.review.fields.sleeping_place_comfort_rating', [], 'en'));

        $this->actingAs($host)
            ->get(route('host.reviews.create', ['locale' => 'ru', 'booking' => $booking]))
            ->assertOk()
            ->assertSeeLivewire(CreateReview::class)
            ->assertSee(__('booking.review.host_title', [], 'ru'))
            ->assertSee(__('booking.review.fields.rule_following_rating', [], 'ru'));
    }

    public function test_guest_review_uploads_photos_as_webp(): void
    {
        Storage::fake('public');

        [$booking, $guest] = $this->createStayBooking();

        Livewire::actingAs($guest)
            ->test(CreateReview::class, ['booking' => $booking])
            ->set('photos', [
                UploadedFile::fake()->image('sleeping-place.png', 1200, 900)->size(500),
            ])
            ->call('submit')
            ->assertHasNoErrors();

        $review = Review::query()->firstOrFail();

        $this->assertCount(1, $review->photos_json);
        $this->assertStringEndsWith('.webp', $review->photos_json[0]);
        Storage::disk('public')->assertExists($review->photos_json[0]);
    }

    public function test_public_listing_reviews_and_profile_summary_show_visible_reviews(): void
    {
        [$booking, $guest, $host, $place] = $this->createStayBooking();

        app(ReviewService::class)->createGuestReview($booking, $guest, [
            'overall' => 5,
        ], 'Quiet and clean sleeping place.');
        app(ReviewService::class)->createHostReview($booking->refresh(), $host, [
            'overall' => 5,
        ], 'Respectful guest.');

        Livewire::test(SleepingPlaceReviews::class, ['sleepingPlaceId' => $place->id])
            ->assertSee('Quiet and clean sleeping place.')
            ->assertSee(__('listing.detail.reviews.rating', ['rating' => '5.0']));

        $this->actingAs($guest)
            ->get(route('profile.show', ['locale' => 'en', 'user' => $host]))
            ->assertOk()
            ->assertSee(__('app.profile.review_summary', [], 'en'))
            ->assertSee('Quiet and clean sleeping place.');
    }

    /**
     * @return array{0: Booking, 1: User, 2: User, 3: SleepingPlace}
     */
    private function createStayBooking(BookingStatus $status = BookingStatus::Completed): array
    {
        $guest = User::factory()->create(['name' => 'Calm Guest']);
        UserProfile::factory()->for($guest, 'user')->create([
            'display_name' => 'Calm Guest',
        ]);
        GuestPreference::factory()->for($guest, 'user')->create();

        $host = User::factory()->create(['name' => 'Kind Host', 'is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'display_name' => 'Kind Host',
        ]);

        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'status' => PropertyStatus::Active,
            ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'beds_count' => 2,
            'max_guests' => 2,
        ]);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'display_name' => 'Quiet lower bed',
            ]);
        $place->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet lower bed',
            'summary' => 'A calm place.',
            'description' => 'A calm place.',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихое нижнее место',
            'summary' => 'Спокойное место.',
            'description' => 'Спокойное место.',
        ]);

        $booking = Booking::factory()->create([
            'bed_id' => null,
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => $status,
            'payment_status' => PaymentStatus::Paid,
            'check_in' => '2026-06-10',
            'check_out' => '2026-06-15',
            'check_in_date' => '2026-06-10',
            'check_out_date' => '2026-06-15',
            'checked_out_at' => now()->subDay(),
            'review_deadline_at' => now()->addDays(13),
        ]);

        return [$booking, $guest, $host, $place];
    }
}
