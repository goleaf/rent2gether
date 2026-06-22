<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Host\Reviews\HostReputationSummary;
use App\Livewire\Listings\Reviews\ListingRatingSummary;
use App\Livewire\Reviews\GuestPlaceReviewForm;
use App\Livewire\Reviews\HostGuestReviewForm;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\ComplaintCase;
use App\Models\DisputeCase;
use App\Models\GuestPreference;
use App\Models\GuestReputationSnapshot;
use App\Models\HostProfile;
use App\Models\HostReputationSnapshot;
use App\Models\Property;
use App\Models\RatingAggregate;
use App\Models\RatingEvent;
use App\Models\ReviewMedia;
use App\Models\ReviewPolicy;
use App\Models\ReviewRequest;
use App\Models\ReviewResponse;
use App\Models\ReviewScore;
use App\Models\Room;
use App\Models\RoommateExperienceReview;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceRatingSnapshot;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Reviews\RatingEventService;
use App\Services\Reviews\RatingImpactService;
use App\Services\Reviews\ReviewEligibilityService;
use App\Services\Reviews\ReviewMediaService;
use App\Services\Reviews\ReviewPrivacyService;
use App\Services\Reviews\ReviewPublishingService;
use App\Services\Reviews\ReviewRequestService;
use App\Services\Reviews\ReviewResponseService;
use App\Services\Reviews\ReviewService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewsRatingsReputationPointTwentySixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-22 09:00:00');
        CarbonImmutable::setTestNow('2026-06-22 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_review_policy_and_requests_are_created_after_completed_checkout(): void
    {
        [$booking, $guest, $host, $place, $checkOut] = $this->createCompletedStay();

        $policy = ReviewPolicy::factory()->create();
        $requests = app(ReviewRequestService::class)->createRequestsAfterCheckout($checkOut);

        $this->assertSame(14, $policy->review_window_days);
        $this->assertCount(3, $requests);
        $this->assertSame([
            'guest_reviews_place',
            'host_reviews_guest',
            'guest_reviews_roommates',
        ], $requests->pluck('request_type')->all());
        $this->assertTrue($requests->every(fn (ReviewRequest $request): bool => $request->booking_id === $booking->id));
        $this->assertTrue($requests->every(fn (ReviewRequest $request): bool => $request->property_id === $place->property_id));
        $this->assertTrue($requests->every(fn (ReviewRequest $request): bool => $request->room_id === $place->room_id));
        $this->assertTrue($requests->every(fn (ReviewRequest $request): bool => $request->sleeping_place_id === $place->id));
        $this->assertTrue($requests->contains(fn (ReviewRequest $request): bool => $request->reviewer_user_id === $guest->id));
        $this->assertTrue($requests->contains(fn (ReviewRequest $request): bool => $request->reviewer_user_id === $host->id));
        $this->assertTrue($requests->first()->due_at->isSameDay(CarbonImmutable::parse('2026-06-29')));
    }

    public function test_no_normal_review_request_for_no_show_or_cancelled_before_check_in(): void
    {
        [$noShowBooking, , , , $noShowCheckout] = $this->createCompletedStay(BookingStatus::NoShow);
        [$cancelledBooking, , , , $cancelledCheckout] = $this->createCompletedStay(BookingStatus::CancelledByGuestFlow);

        $this->assertFalse(app(ReviewEligibilityService::class)->bookingQualifiesForReview($noShowBooking));
        $this->assertFalse(app(ReviewEligibilityService::class)->bookingQualifiesForReview($cancelledBooking));
        $this->assertCount(0, app(ReviewRequestService::class)->createRequestsAfterCheckout($noShowCheckout));
        $this->assertCount(0, app(ReviewRequestService::class)->createRequestsAfterCheckout($cancelledCheckout));
    }

    public function test_guest_and_host_submit_double_blind_reviews_with_scores_and_snapshots(): void
    {
        [$booking, $guest, $host, $place, $checkOut] = $this->createCompletedStay();
        $requests = app(ReviewRequestService::class)->createRequestsAfterCheckout($checkOut);
        $guestRequest = $requests->firstWhere('request_type', 'guest_reviews_place');
        $hostRequest = $requests->firstWhere('request_type', 'host_reviews_guest');

        $guestReview = app(ReviewService::class)->submitGuestPlaceReview($guest, $guestRequest, [
            'scores' => [
                'overall' => 4,
                'cleanliness' => 5,
                'safety' => 4,
                'sleeping_place_quality' => 5,
                'internet' => 4,
                'value_for_money' => 4,
            ],
            'what_liked' => 'Calm room and fast replies.',
            'what_disliked' => 'The shelf was small.',
            'advice_to_future_guests' => 'Bring slippers.',
            'recommend' => true,
            'wants_to_return' => true,
        ]);

        $this->assertSame('waiting_other_party', $guestReview->status->value);
        $this->assertFalse($guestReview->is_public);
        $this->assertSame(6, ReviewScore::query()->where('review_id', $guestReview->id)->count());

        $hostReview = app(ReviewService::class)->submitHostGuestReview($host, $hostRequest, [
            'scores' => [
                'overall' => 5,
                'rules_respect' => 5,
                'cleanliness_after_stay' => 5,
                'communication' => 5,
                'punctuality' => 4,
                'care_for_property' => 5,
            ],
            'public_comment' => 'Respectful guest.',
            'recommend' => true,
        ]);

        $this->assertSame('published', $guestReview->refresh()->status->value);
        $this->assertSame('published', $hostReview->refresh()->status->value);
        $this->assertTrue($guestReview->is_public);
        $this->assertSame(2, RatingEvent::query()->where('event_key', 'review_submitted')->count());
        $this->assertSame(4.0, (float) RatingAggregate::query()
            ->where('target_type', 'sleeping_place')
            ->where('sleeping_place_id', $place->id)
            ->where('metric_key', 'overall')
            ->value('rating_average'));
        $this->assertSame(4.0, (float) SleepingPlaceRatingSnapshot::query()
            ->where('sleeping_place_id', $place->id)
            ->value('overall_rating'));
        $this->assertSame(4.0, (float) HostReputationSnapshot::query()
            ->where('host_user_id', $host->id)
            ->value('overall_rating'));
        $this->assertSame(5.0, (float) GuestReputationSnapshot::query()
            ->where('guest_user_id', $guest->id)
            ->value('overall_rating'));
    }

    public function test_roommate_experience_review_is_privacy_safe(): void
    {
        [$booking, $guest, , , $checkOut] = $this->createCompletedStay();
        $request = app(ReviewRequestService::class)
            ->createRequestsAfterCheckout($checkOut)
            ->firstWhere('request_type', 'guest_reviews_roommates');

        $review = app(ReviewService::class)->submitRoommateExperienceReview($guest, $request, [
            'scores' => ['overall' => 4, 'roommate_communication' => 4],
            'quiet_roommates' => true,
            'clean_roommates' => true,
            'friendly_roommates' => true,
            'roommates_disturbed_sleep' => false,
            'roommates_broke_rules' => false,
            'conflict_happened' => false,
            'comment' => 'The room felt calm.',
        ]);

        $experience = RoommateExperienceReview::query()->where('review_id', $review->id)->firstOrFail();
        app(ReviewPublishingService::class)->publishReview($review->refresh());
        $public = app(ReviewPrivacyService::class)->filterReviewForPublic($review->refresh());

        $this->assertSame($booking->room_id, $experience->room_id);
        $this->assertTrue($experience->quiet_roommates);
        $this->assertArrayHasKey('roommate_summary', $public);
        $this->assertArrayNotHasKey('target_user_id', $public);
        $this->assertArrayNotHasKey('private_comment', $public);
    }

    public function test_expired_request_cannot_be_submitted(): void
    {
        [$booking, $guest, , , $checkOut] = $this->createCompletedStay();
        $request = app(ReviewRequestService::class)
            ->createRequestsAfterCheckout($checkOut)
            ->firstWhere('request_type', 'guest_reviews_place');
        $request->forceFill([
            'status' => 'expired',
            'expired_at' => now(),
        ])->save();

        $this->expectException(ValidationException::class);

        app(ReviewService::class)->submitGuestPlaceReview($guest, $request->refresh(), [
            'scores' => ['overall' => 5],
            'what_liked' => 'Too late.',
        ]);

        $this->assertSame($booking->id, $request->booking_id);
    }

    public function test_publication_after_window_expiration_and_edit_rules(): void
    {
        [$booking, $guest, , , $checkOut] = $this->createCompletedStay();
        $request = app(ReviewRequestService::class)
            ->createRequestsAfterCheckout($checkOut)
            ->firstWhere('request_type', 'guest_reviews_place');

        $review = app(ReviewService::class)->submitGuestPlaceReview($guest, $request, [
            'scores' => ['overall' => 5],
            'what_liked' => 'Excellent.',
        ]);

        $edited = app(ReviewService::class)->editReview($guest, $review, [
            'what_liked' => 'Excellent and quiet.',
        ]);
        $this->assertSame('Excellent and quiet.', $edited->what_liked);

        $review->forceFill(['edit_deadline_at' => now()->subMinute()])->save();

        try {
            app(ReviewService::class)->editReview($guest, $review->refresh(), [
                'what_liked' => 'Late edit.',
            ]);

            $this->fail('Late review edits must be rejected.');
        } catch (ValidationException) {
            $this->assertSame('Excellent and quiet.', $review->refresh()->what_liked);
        }

        $booking->forceFill(['review_deadline_at' => now()->subMinute()])->save();
        app(ReviewPublishingService::class)->publishAfterWindowExpired($booking->refresh());

        $this->assertSame('published', $review->refresh()->status->value);
    }

    public function test_host_response_and_review_media_visibility_are_authorized(): void
    {
        [, $guest, $host, , $checkOut] = $this->createCompletedStay();
        $request = app(ReviewRequestService::class)
            ->createRequestsAfterCheckout($checkOut)
            ->firstWhere('request_type', 'guest_reviews_place');
        $review = app(ReviewService::class)->submitGuestPlaceReview($guest, $request, [
            'scores' => ['overall' => 5],
            'what_liked' => 'Helpful host.',
        ]);
        app(ReviewPublishingService::class)->publishReview($review);

        $response = app(ReviewResponseService::class)->respondToReview($host, $review->refresh(), 'Thank you for staying.');
        $publicMedia = app(ReviewMediaService::class)->uploadReviewPhoto($guest, $review, [
            'path' => 'review-photos/public.webp',
            'thumbnail_path' => 'review-photos/public-thumb.webp',
            'media_role' => 'positive_photo',
            'visibility' => 'public',
            'approved_for_public_display' => true,
        ]);
        app(ReviewMediaService::class)->uploadReviewPhoto($guest, $review, [
            'path' => 'review-photos/internal.webp',
            'media_role' => 'problem_photo',
            'visibility' => 'internal_future',
        ]);

        $visibleMedia = app(ReviewMediaService::class)->getVisibleMedia($guest, $review);

        $this->assertInstanceOf(ReviewResponse::class, $response);
        $this->assertSame('published', app(ReviewResponseService::class)->publishResponse($response)->status);
        $this->assertInstanceOf(ReviewMedia::class, $publicMedia);
        $this->assertCount(1, $visibleMedia);
        $this->assertSame('review-photos/public.webp', $visibleMedia->first()->path);
    }

    public function test_unconfirmed_complaints_do_not_impact_rating_and_disputes_freeze_events(): void
    {
        [$booking] = $this->createCompletedStay();
        $complaint = ComplaintCase::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'status' => 'submitted',
        ]);

        $this->assertFalse(app(RatingImpactService::class)->canImpactRating('complaint_case', $complaint->id));
        $this->assertNull(app(RatingEventService::class)->createConfirmedComplaintEvent($complaint));

        $complaint->forceFill(['status' => 'confirmed'])->save();
        $event = app(RatingEventService::class)->createConfirmedComplaintEvent($complaint->refresh());

        $this->assertInstanceOf(RatingEvent::class, $event);
        $this->assertTrue($event->confirmed);

        $dispute = DisputeCase::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'rating_impact_frozen' => true,
            'status' => 'opened',
        ]);

        $this->assertTrue(app(RatingImpactService::class)->shouldFreezeImpact('dispute_case', $dispute->id));
        $this->assertTrue(app(RatingEventService::class)->freezeEvent($event)->frozen);
        $this->assertTrue(app(RatingEventService::class)->ignoreEvent($event->refresh(), 'complaint_rejected')->ignored);
    }

    public function test_listing_rating_and_reputation_components_render_in_english_and_russian(): void
    {
        [$booking, $guest, $host, $place, $checkOut] = $this->createCompletedStay();
        $requests = app(ReviewRequestService::class)->createRequestsAfterCheckout($checkOut);
        app(ReviewService::class)->submitGuestPlaceReview($guest, $requests->firstWhere('request_type', 'guest_reviews_place'), [
            'scores' => ['overall' => 5],
            'what_liked' => 'Very clean.',
        ]);
        app(ReviewService::class)->submitHostGuestReview($host, $requests->firstWhere('request_type', 'host_reviews_guest'), [
            'scores' => ['overall' => 5],
            'public_comment' => 'Great guest.',
        ]);
        app(ReviewPublishingService::class)->publishPairIfReady($booking->refresh());

        app()->setLocale('en');
        Livewire::test(GuestPlaceReviewForm::class, ['reviewRequestId' => $requests->firstWhere('request_type', 'guest_reviews_place')->id])
            ->assertSee(__('reviews.actions.submit_review', [], 'en'));
        Livewire::test(HostGuestReviewForm::class, ['reviewRequestId' => $requests->firstWhere('request_type', 'host_reviews_guest')->id])
            ->assertSee(__('reviews.actions.submit_review', [], 'en'));
        Livewire::test(ListingRatingSummary::class, ['sleepingPlaceId' => $place->id])
            ->assertSee(__('ratings.title', [], 'en'));

        app()->setLocale('ru');
        Livewire::test(HostReputationSummary::class, ['hostUserId' => $host->id])
            ->assertSee(__('ratings.title', [], 'ru'));
    }

    /**
     * @return array{0: Booking, 1: User, 2: User, 3: SleepingPlace, 4: BookingCheckOut}
     */
    private function createCompletedStay(BookingStatus $status = BookingStatus::Completed): array
    {
        $guest = User::factory()->create(['name' => 'Point 26 Guest']);
        UserProfile::factory()->for($guest, 'user')->create(['display_name' => 'Point 26 Guest']);
        GuestPreference::factory()->for($guest, 'user')->create();

        $host = User::factory()->create(['name' => 'Point 26 Host', 'is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create(['display_name' => 'Point 26 Host']);

        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
            'status' => PropertyStatus::Active,
        ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'beds_count' => 4,
            'max_guests' => 4,
        ]);

        $place = SleepingPlace::factory()->for($property)->for($room)->create([
            'status' => SleepingPlaceStatus::Active,
            'display_name' => 'Point 26 lower bed',
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
            'checked_in_at' => '2026-06-10 15:00:00',
            'checked_out_at' => '2026-06-15 11:00:00',
            'guest_check_in_confirmed_at' => '2026-06-10 15:05:00',
            'guest_check_out_confirmed_at' => '2026-06-15 11:05:00',
            'host_check_out_confirmed_at' => '2026-06-15 12:00:00',
            'review_deadline_at' => '2026-06-29 12:00:00',
        ]);

        $checkOut = BookingCheckOut::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_out_date' => '2026-06-15',
            'guest_confirmed_checkout_at' => '2026-06-15 11:05:00',
            'host_confirmed_checkout_at' => '2026-06-15 12:00:00',
            'completed_at' => $status === BookingStatus::Completed ? '2026-06-15 12:00:00' : null,
            'status' => $status === BookingStatus::Completed ? 'completed' : 'cancelled',
        ]);

        return [$booking, $guest, $host, $place, $checkOut];
    }
}
