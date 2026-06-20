<?php

namespace App\Services\Reviews;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\Booking;
use App\Models\Review;
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
