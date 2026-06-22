<?php

namespace App\Services\Reviews;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\Booking;
use App\Models\Review;
use App\Models\ReviewRequest;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    private const REVIEW_WINDOW_DAYS = 14;

    private const FLAGGED_TERMS = [
        'fuck',
        'shit',
        'idiot',
        'дурак',
    ];

    public function __construct(
        private readonly ReviewNumberService $numbers,
        private readonly ReviewPolicyService $policies,
        private readonly ReviewScoreService $scores,
        private readonly RoommateExperienceReviewService $roommates,
        private readonly ReviewPublishingService $publishing,
        private readonly ReviewEventService $events,
    ) {}

    public function startReview(User $author, ReviewRequest $request): Review
    {
        $this->ensureCanUseRequest($author, $request);

        return Review::query()->firstOrCreate(
            [
                'review_request_id' => $request->id,
            ],
            $this->pointTwentySixReviewAttributes($author, $request, [
                'scores' => ['overall' => 5],
            ], ReviewStatus::Draft),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function submitGuestPlaceReview(User $guest, ReviewRequest $request, array $data): Review
    {
        $this->ensureCanUseRequest($guest, $request);
        $this->ensureRequestType($request, 'guest_reviews_place');
        $this->ensureReviewDoesNotExistForType($request, ReviewType::GuestToPlace);
        $this->ensureOverallScore($data);

        return DB::transaction(function () use ($guest, $request, $data): Review {
            $review = Review::query()->create($this->pointTwentySixReviewAttributes($guest, $request, $data, ReviewStatus::Submitted));
            $this->scores->createScores($review, $data['scores']);
            $this->markRequestSubmitted($request, $review);
            $this->events->record($review, 'review_submitted');

            $review = $this->publishing->holdUntilOtherPartyOrDeadline($review->refresh());

            if ($this->publishing->shouldPublishNow($review)) {
                $this->publishing->publishPairIfReady($review->booking()->firstOrFail());
            }

            return $review->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function submitHostGuestReview(User $host, ReviewRequest $request, array $data): Review
    {
        $this->ensureCanUseRequest($host, $request);
        $this->ensureRequestType($request, 'host_reviews_guest');
        $this->ensureReviewDoesNotExistForType($request, ReviewType::HostToGuest);
        $this->ensureOverallScore($data);

        return DB::transaction(function () use ($host, $request, $data): Review {
            $review = Review::query()->create($this->pointTwentySixReviewAttributes($host, $request, $data, ReviewStatus::Submitted));
            $this->scores->createScores($review, $data['scores']);
            $this->markRequestSubmitted($request, $review);
            $this->events->record($review, 'review_submitted');

            $review = $this->publishing->holdUntilOtherPartyOrDeadline($review->refresh());

            if ($this->publishing->shouldPublishNow($review)) {
                $this->publishing->publishPairIfReady($review->booking()->firstOrFail());
            }

            return $review->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function submitRoommateExperienceReview(User $guest, ReviewRequest $request, array $data): Review
    {
        $this->ensureCanUseRequest($guest, $request);
        $this->ensureRequestType($request, 'guest_reviews_roommates');
        $this->ensureReviewDoesNotExistForType($request, ReviewType::RoommateExperience);
        $this->ensureOverallScore($data);

        return DB::transaction(function () use ($guest, $request, $data): Review {
            $review = Review::query()->create($this->pointTwentySixReviewAttributes($guest, $request, $data, ReviewStatus::Submitted));
            $this->scores->createScores($review, $data['scores']);
            $this->roommates->createFromGuestReview($review, $data);
            $this->markRequestSubmitted($request, $review);
            $this->events->record($review, 'review_submitted');

            return $this->publishing->holdUntilOtherPartyOrDeadline($review->refresh());
        });
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function editReview(User $author, Review $review, array $data): Review
    {
        if ((int) $review->author_user_id !== (int) $author->id && (int) $review->reviewer_id !== (int) $author->id) {
            throw ValidationException::withMessages([
                'review' => __('reviews.validation.not_author'),
            ]);
        }

        if ($review->status === ReviewStatus::Published || $review->is_public) {
            throw ValidationException::withMessages([
                'review' => __('reviews.validation.published_not_editable'),
            ]);
        }

        if ($review->edit_deadline_at !== null && $review->edit_deadline_at->lessThan(now())) {
            throw ValidationException::withMessages([
                'review' => __('reviews.validation.edit_window_closed'),
            ]);
        }

        $review->forceFill([
            'title' => $data['title'] ?? $review->title,
            'public_comment' => $data['public_comment'] ?? $data['what_liked'] ?? $review->public_comment,
            'what_liked' => $data['what_liked'] ?? $review->what_liked,
            'what_disliked' => $data['what_disliked'] ?? $review->what_disliked,
            'advice_to_future_guests' => $data['advice_to_future_guests'] ?? $review->advice_to_future_guests,
            'edited_at' => now(),
            'edit_count' => $review->edit_count + 1,
        ])->save();

        if (isset($data['scores'])) {
            $this->scores->updateScores($review->refresh(), $data['scores']);
        }

        return $review->refresh();
    }

    public function hideReview(Review $review, string $reason): Review
    {
        $review->forceFill([
            'status' => ReviewStatus::Hidden,
            'is_public' => false,
            'hidden_at' => now(),
        ])->save();

        $this->events->record($review->refresh(), 'review_hidden', ['reason' => $reason]);

        return $review->refresh();
    }

    public function closeReview(Review $review): Review
    {
        $review->forceFill(['status' => ReviewStatus::Closed])->save();

        return $review->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function pointTwentySixReviewAttributes(User $author, ReviewRequest $request, array $data, ReviewStatus $status): array
    {
        $booking = $request->booking()->firstOrFail();
        $policy = $this->policies->resolveForBooking($booking);
        $reviewType = $this->reviewTypeForRequest($request);
        $targetType = $this->targetTypeForRequest($request);
        $targetUserId = $this->targetUserIdForRequest($request);
        $overallRating = (int) ($data['scores']['overall'] ?? 5);
        $publicComment = $this->blankToNull($data['public_comment'] ?? $data['what_liked'] ?? $data['comment'] ?? null);

        return [
            'review_number' => $this->numbers->generateReviewNumber(),
            'review_request_id' => $request->id,
            'booking_id' => $request->booking_id,
            'booking_stay_id' => $request->booking_stay_id,
            'booking_check_out_id' => $request->booking_check_out_id,
            'reviewer_id' => $author->id,
            'reviewee_id' => $targetUserId ?: $request->host_user_id,
            'author_user_id' => $author->id,
            'author_type' => $request->reviewer_type,
            'target_user_id' => $targetUserId,
            'target_type' => $targetType,
            'guest_user_id' => $request->guest_user_id,
            'host_user_id' => $request->host_user_id,
            'type' => $reviewType,
            'review_subject_type' => $request->review_subject_type,
            'bed_id' => $booking->bed_id,
            'sleeping_place_id' => $request->sleeping_place_id,
            'room_id' => $request->room_id,
            'property_id' => $request->property_id,
            'overall_rating' => $overallRating,
            'title' => $data['title'] ?? null,
            'public_comment' => $publicComment,
            'private_comment' => $this->blankToNull($data['private_comment'] ?? null),
            'what_liked' => $this->blankToNull($data['what_liked'] ?? $data['public_comment'] ?? null),
            'what_disliked' => $this->blankToNull($data['what_disliked'] ?? null),
            'advice_to_future_guests' => $this->blankToNull($data['advice_to_future_guests'] ?? null),
            'positive_comment' => $publicComment,
            'negative_comment' => $this->blankToNull($data['what_disliked'] ?? null),
            'advice' => $this->blankToNull($data['advice_to_future_guests'] ?? null),
            'liked_text' => $this->blankToNull($data['what_liked'] ?? $data['public_comment'] ?? null),
            'improvement_text' => $this->blankToNull($data['what_disliked'] ?? null),
            'advice_text' => $this->blankToNull($data['advice_to_future_guests'] ?? null),
            'comment' => $publicComment,
            'would_recommend' => (bool) ($data['recommend'] ?? true),
            'recommend' => (bool) ($data['recommend'] ?? true),
            'recommend_guest' => $request->request_type === 'host_reviews_guest' ? (bool) ($data['recommend'] ?? true) : null,
            'would_return' => $data['wants_to_return'] ?? null,
            'status' => $status,
            'is_public' => false,
            'is_anonymous_future' => false,
            'is_double_blind' => $policy->double_blind_enabled,
            'is_published_after_window' => false,
            'submitted_at' => $status === ReviewStatus::Draft ? null : now(),
            'edit_deadline_at' => now()->addHours($policy->edit_window_hours),
            'edit_count' => 0,
            'language_locale' => app()->getLocale(),
            'flagged_words_json' => [],
        ];
    }

    /**
     * @throws ValidationException
     */
    private function ensureCanUseRequest(User $author, ReviewRequest $request): void
    {
        if ((int) $request->reviewer_user_id !== (int) $author->id) {
            throw ValidationException::withMessages([
                'review' => __('reviews.validation.not_author'),
            ]);
        }

        if (in_array($request->status, ['submitted', 'expired', 'cancelled', 'closed'], true)) {
            throw ValidationException::withMessages([
                'review' => __('reviews.validation.request_not_open'),
            ]);
        }

        if ($request->due_at !== null && $request->due_at->lessThanOrEqualTo(now())) {
            $request->forceFill([
                'status' => 'expired',
                'expired_at' => now(),
            ])->save();

            throw ValidationException::withMessages([
                'review' => __('reviews.validation.request_expired'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureRequestType(ReviewRequest $request, string $requestType): void
    {
        if ($request->request_type !== $requestType) {
            throw ValidationException::withMessages([
                'review' => __('reviews.validation.wrong_request_type'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function ensureOverallScore(array $data): void
    {
        if (! isset($data['scores']['overall']) || ! $this->scores->validateScoreValue('overall', (float) $data['scores']['overall'])) {
            throw ValidationException::withMessages([
                'scores.overall' => __('reviews.validation.overall_required'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureReviewDoesNotExistForType(ReviewRequest $request, ReviewType $type): void
    {
        if (Review::query()->where('booking_id', $request->booking_id)->where('type', $type->value)->exists()) {
            throw ValidationException::withMessages([
                'review' => __('reviews.validation.already_submitted'),
            ]);
        }
    }

    private function markRequestSubmitted(ReviewRequest $request, Review $review): void
    {
        $request->forceFill([
            'status' => 'submitted',
            'submitted_at' => now(),
        ])->save();

        if ($request->request_type === 'guest_reviews_place') {
            Booking::query()->whereKey($request->booking_id)->update(['guest_review_left_at' => now(), 'guest_review_left' => true]);
        }

        if ($request->request_type === 'host_reviews_guest') {
            Booking::query()->whereKey($request->booking_id)->update(['host_review_left_at' => now(), 'host_review_left' => true]);
        }

        $this->events->record($review, 'review_request_submitted');
    }

    private function reviewTypeForRequest(ReviewRequest $request): ReviewType
    {
        return match ($request->request_type) {
            'host_reviews_guest' => ReviewType::HostToGuest,
            'guest_reviews_roommates' => ReviewType::RoommateExperience,
            default => ReviewType::GuestToPlace,
        };
    }

    private function targetTypeForRequest(ReviewRequest $request): string
    {
        return match ($request->request_type) {
            'host_reviews_guest' => 'guest',
            'guest_reviews_roommates' => 'roommates',
            default => 'host',
        };
    }

    private function targetUserIdForRequest(ReviewRequest $request): ?int
    {
        return match ($request->request_type) {
            'host_reviews_guest' => (int) $request->guest_user_id,
            'guest_reviews_roommates' => null,
            default => (int) $request->host_user_id,
        };
    }

    /**
     * @param  array<string, mixed>  $ratings
     * @param  list<string>  $photos
     *
     * @throws ValidationException
     */
    public function createGuestReview(
        Booking $booking,
        User $guest,
        array $ratings,
        ?string $likedText = null,
        ?string $improvementText = null,
        ?string $adviceText = null,
        bool $recommend = true,
        array $photos = [],
    ): Review {
        $this->validateGuestRatings($ratings);
        $this->ensureFriendlyText([$likedText, $improvementText, $adviceText]);

        return DB::transaction(function () use ($booking, $guest, $ratings, $likedText, $improvementText, $adviceText, $recommend, $photos): Review {
            $booking = $this->lockReviewBooking($booking);

            $this->ensureCanReview($booking, $guest, ReviewType::GuestToPlace);
            $this->ensureReviewDoesNotExist($booking, ReviewType::GuestToPlace);

            $deadline = $this->ensureReviewDeadline($booking);
            $status = $this->initialStatus($deadline);

            $review = Review::query()->create([
                'booking_id' => $booking->id,
                'reviewer_id' => $guest->id,
                'reviewee_id' => $this->hostId($booking),
                'guest_user_id' => $this->guestId($booking),
                'host_user_id' => $this->hostId($booking),
                'type' => ReviewType::GuestToPlace,
                'bed_id' => $booking->bed_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'room_id' => $booking->room_id,
                'property_id' => $booking->property_id,
                'overall_rating' => $this->rating($ratings, 'overall'),
                'cleanliness_rating' => $this->optionalRating($ratings, 'cleanliness'),
                'safety_rating' => $this->optionalRating($ratings, 'safety'),
                'location_rating' => $this->optionalRating($ratings, 'location'),
                'accuracy_rating' => $this->optionalRating($ratings, 'accuracy'),
                'bed_comfort_rating' => $this->optionalRating($ratings, 'sleeping_place_comfort', 'bed_comfort'),
                'sleeping_place_comfort_rating' => $this->optionalRating($ratings, 'sleeping_place_comfort', 'bed_comfort'),
                'amenities_rating' => $this->optionalRating($ratings, 'amenities'),
                'communication_rating' => $this->optionalRating($ratings, 'host_communication', 'communication'),
                'host_communication_rating' => $this->optionalRating($ratings, 'host_communication', 'communication'),
                'neighbors_rating' => $this->optionalRating($ratings, 'neighbors'),
                'value_rating' => $this->optionalRating($ratings, 'value'),
                'positive_comment' => $this->blankToNull($likedText),
                'negative_comment' => $this->blankToNull($improvementText),
                'advice' => $this->blankToNull($adviceText),
                'liked_text' => $this->blankToNull($likedText),
                'improvement_text' => $this->blankToNull($improvementText),
                'advice_text' => $this->blankToNull($adviceText),
                'photos_json' => $photos,
                'would_recommend' => $recommend,
                'recommend' => $recommend,
                'would_return' => null,
                'status' => $status,
                'visible_at' => $status === ReviewStatus::Published ? now() : null,
                'flagged_words_json' => [],
            ]);

            Booking::query()->whereKey($booking->id)->update(['guest_review_left' => true]);
            $booking->guest_review_left = true;
            $this->syncReviewVisibility($booking);

            app(NotificationService::class)->notifyReviewReceived($review->refresh());

            return $review->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $ratings
     *
     * @throws ValidationException
     */
    public function createHostReview(
        Booking $booking,
        User $host,
        array $ratings,
        ?string $comment = null,
        bool $recommendGuest = true,
    ): Review {
        $this->validateHostRatings($ratings);
        $this->ensureFriendlyText([$comment]);

        return DB::transaction(function () use ($booking, $host, $ratings, $comment, $recommendGuest): Review {
            $booking = $this->lockReviewBooking($booking);

            $this->ensureCanReview($booking, $host, ReviewType::HostToGuest);
            $this->ensureReviewDoesNotExist($booking, ReviewType::HostToGuest);

            $deadline = $this->ensureReviewDeadline($booking);
            $status = $this->initialStatus($deadline);

            $review = Review::query()->create([
                'booking_id' => $booking->id,
                'reviewer_id' => $host->id,
                'reviewee_id' => $this->guestId($booking),
                'guest_user_id' => $this->guestId($booking),
                'host_user_id' => $this->hostId($booking),
                'type' => ReviewType::HostToGuest,
                'bed_id' => $booking->bed_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'room_id' => $booking->room_id,
                'property_id' => $booking->property_id,
                'overall_rating' => $this->rating($ratings, 'overall'),
                'cleanliness_rating' => $this->optionalRating($ratings, 'cleanliness', 'tidiness'),
                'communication_rating' => $this->optionalRating($ratings, 'communication'),
                'rule_compliance_rating' => $this->optionalRating($ratings, 'rule_following', 'rule_compliance'),
                'rule_following_rating' => $this->optionalRating($ratings, 'rule_following', 'rule_compliance'),
                'tidiness_rating' => $this->optionalRating($ratings, 'cleanliness', 'tidiness'),
                'punctuality_rating' => $this->optionalRating($ratings, 'punctuality'),
                'respect_rating' => $this->optionalRating($ratings, 'respect'),
                'positive_comment' => $this->blankToNull($comment),
                'liked_text' => $this->blankToNull($comment),
                'comment' => $this->blankToNull($comment),
                'would_recommend' => $recommendGuest,
                'recommend_guest' => $recommendGuest,
                'status' => $status,
                'visible_at' => $status === ReviewStatus::Published ? now() : null,
                'flagged_words_json' => [],
            ]);

            Booking::query()->whereKey($booking->id)->update(['host_review_left' => true]);
            $booking->host_review_left = true;
            $this->syncReviewVisibility($booking);

            app(NotificationService::class)->notifyReviewReceived($review->refresh());

            return $review->refresh();
        });
    }

    public function recalculateUserRating(User $user): void
    {
        $guestRating = $user->reviewsReceived()
            ->hostToGuest()
            ->published()
            ->avg('overall_rating');

        $hostRating = $user->reviewsReceived()
            ->guestToPlace()
            ->published()
            ->avg('overall_rating');

        $user->forceFill([
            'rating_as_guest' => $guestRating ? round((float) $guestRating, 2) : null,
            'rating_as_host' => $hostRating ? round((float) $hostRating, 2) : null,
        ])->save();
    }

    private function lockReviewBooking(Booking $booking): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'guest_id',
                'guest_user_id',
                'host_id',
                'host_user_id',
                'bed_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'checked_out_at',
                'guest_review_left',
                'host_review_left',
                'review_deadline_at',
                'updated_at',
            ])
            ->with([
                'guest:id,name,rating_as_guest,rating_as_host',
                'guest.profile:id,user_id,rating_average,reviews_count',
                'host:id,name,rating_as_guest,rating_as_host',
                'host.hostProfile:id,user_id,rating_average,reviews_count',
            ])
            ->lockForUpdate()
            ->findOrFail($booking->id);
    }

    /**
     * @throws ValidationException
     */
    private function ensureCanReview(Booking $booking, User $user, ReviewType $type): void
    {
        if ($this->statusValue($booking) !== BookingStatus::Completed->value) {
            throw ValidationException::withMessages([
                'booking' => __('booking.review.errors.not_completed'),
            ]);
        }

        $expectedUserIds = $type === ReviewType::GuestToPlace
            ? array_filter([(int) $booking->guest_user_id, (int) $booking->guest_id])
            : array_filter([(int) $booking->host_user_id, (int) $booking->host_id]);

        if (! in_array((int) $user->id, $expectedUserIds, true)) {
            throw ValidationException::withMessages([
                'booking' => __('booking.review.errors.not_your_booking'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureReviewDoesNotExist(Booking $booking, ReviewType $type): void
    {
        if ($booking->reviews()->where('type', $type->value)->exists()) {
            throw ValidationException::withMessages([
                'booking' => __('booking.review.errors.already_reviewed'),
            ]);
        }
    }

    private function ensureReviewDeadline(Booking $booking): CarbonInterface
    {
        if ($booking->review_deadline_at instanceof CarbonInterface) {
            return $booking->review_deadline_at;
        }

        $base = $booking->checked_out_at ?: $booking->updated_at ?: now();
        $deadline = $base->copy()->addDays(self::REVIEW_WINDOW_DAYS);

        Booking::query()->whereKey($booking->id)->update(['review_deadline_at' => $deadline]);
        $booking->review_deadline_at = $deadline;

        return $deadline;
    }

    private function syncReviewVisibility(Booking $booking): void
    {
        $reviews = $booking->reviews()
            ->whereIn('type', [ReviewType::GuestToPlace->value, ReviewType::HostToGuest->value])
            ->get(['id', 'booking_id', 'reviewee_id', 'type', 'status']);

        $hasGuestReview = $reviews->contains(fn (Review $review): bool => $review->type === ReviewType::GuestToPlace);
        $hasHostReview = $reviews->contains(fn (Review $review): bool => $review->type === ReviewType::HostToGuest);

        if (! ($hasGuestReview && $hasHostReview) && ! $this->reviewWindowExpired($booking)) {
            return;
        }

        $booking->reviews()
            ->where('status', ReviewStatus::Pending->value)
            ->update([
                'status' => ReviewStatus::Published->value,
                'visible_at' => now(),
            ]);

        $booking->loadMissing(['guest:id', 'host:id']);

        if ($booking->guest instanceof User) {
            $this->recalculateUserRating($booking->guest);
            $this->updateGuestProfileAggregate($booking->guest);
        }

        if ($booking->host instanceof User) {
            $this->recalculateUserRating($booking->host);
            $this->updateHostProfileAggregate($booking->host);
        }
    }

    private function updateGuestProfileAggregate(User $guest): void
    {
        $profile = $guest->profile;

        if (! $profile) {
            return;
        }

        $query = Review::query()
            ->hostToGuest()
            ->published()
            ->where('reviewee_id', $guest->id);

        $profile->forceFill([
            'rating_average' => round((float) ($query->avg('overall_rating') ?: 0), 2),
            'reviews_count' => (int) $query->count(),
        ])->save();
    }

    private function updateHostProfileAggregate(User $host): void
    {
        $profile = $host->hostProfile;

        if (! $profile) {
            return;
        }

        $query = Review::query()
            ->guestToPlace()
            ->published()
            ->where('reviewee_id', $host->id);

        $profile->forceFill([
            'rating_average' => round((float) ($query->avg('overall_rating') ?: 0), 2),
            'reviews_count' => (int) $query->count(),
        ])->save();
    }

    private function initialStatus(CarbonInterface $deadline): ReviewStatus
    {
        return $deadline->lessThanOrEqualTo(now())
            ? ReviewStatus::Published
            : ReviewStatus::Pending;
    }

    private function reviewWindowExpired(Booking $booking): bool
    {
        return $booking->review_deadline_at instanceof CarbonInterface
            && $booking->review_deadline_at->lessThanOrEqualTo(now());
    }

    /**
     * @param  array<int, string|null>  $texts
     *
     * @throws ValidationException
     */
    private function ensureFriendlyText(array $texts): void
    {
        $text = Str::lower(implode(' ', array_filter($texts)));

        foreach (self::FLAGGED_TERMS as $term) {
            if (str_contains($text, $term)) {
                throw ValidationException::withMessages([
                    'review' => __('booking.review.errors.friendly_language'),
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $ratings
     *
     * @throws ValidationException
     */
    private function validateGuestRatings(array $ratings): void
    {
        Validator::make($ratings, [
            'overall' => ['required', 'integer', 'between:1,5'],
            'cleanliness' => ['nullable', 'integer', 'between:1,5'],
            'safety' => ['nullable', 'integer', 'between:1,5'],
            'location' => ['nullable', 'integer', 'between:1,5'],
            'accuracy' => ['nullable', 'integer', 'between:1,5'],
            'sleeping_place_comfort' => ['nullable', 'integer', 'between:1,5'],
            'bed_comfort' => ['nullable', 'integer', 'between:1,5'],
            'amenities' => ['nullable', 'integer', 'between:1,5'],
            'host_communication' => ['nullable', 'integer', 'between:1,5'],
            'communication' => ['nullable', 'integer', 'between:1,5'],
            'neighbors' => ['nullable', 'integer', 'between:1,5'],
            'value' => ['nullable', 'integer', 'between:1,5'],
        ], [], $this->validationAttributes())->validate();
    }

    /**
     * @param  array<string, mixed>  $ratings
     *
     * @throws ValidationException
     */
    private function validateHostRatings(array $ratings): void
    {
        Validator::make($ratings, [
            'overall' => ['required', 'integer', 'between:1,5'],
            'rule_following' => ['nullable', 'integer', 'between:1,5'],
            'rule_compliance' => ['nullable', 'integer', 'between:1,5'],
            'cleanliness' => ['nullable', 'integer', 'between:1,5'],
            'tidiness' => ['nullable', 'integer', 'between:1,5'],
            'communication' => ['nullable', 'integer', 'between:1,5'],
            'punctuality' => ['nullable', 'integer', 'between:1,5'],
            'respect' => ['nullable', 'integer', 'between:1,5'],
        ], [], $this->validationAttributes())->validate();
    }

    /**
     * @param  array<string, mixed>  $ratings
     */
    private function rating(array $ratings, string ...$keys): int
    {
        return (int) ($this->optionalRating($ratings, ...$keys) ?: 0);
    }

    /**
     * @param  array<string, mixed>  $ratings
     */
    private function optionalRating(array $ratings, string ...$keys): ?int
    {
        foreach ($keys as $key) {
            if (isset($ratings[$key]) && $ratings[$key] !== '') {
                return (int) $ratings[$key];
            }
        }

        return null;
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return app('translator')->get('booking.review.validation_attributes');
    }

    private function guestId(Booking $booking): int
    {
        return (int) ($booking->guest_id ?: $booking->guest_user_id);
    }

    private function hostId(Booking $booking): int
    {
        return (int) ($booking->host_id ?: $booking->host_user_id);
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }
}
