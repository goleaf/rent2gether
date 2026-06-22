<?php

namespace App\Services\Reviews;

use App\Models\Review;
use App\Models\ReviewMedia;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReviewMediaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function uploadReviewPhoto(User $author, Review $review, array $data): ReviewMedia
    {
        if (! $this->canManageReviewMedia($author, $review)) {
            throw ValidationException::withMessages([
                'review' => __('reviews.validation.not_author'),
            ]);
        }

        return ReviewMedia::query()->create([
            'review_id' => $review->id,
            'uploaded_by_user_id' => $author->id,
            'media_type' => $data['media_type'] ?? 'photo',
            'media_role' => $data['media_role'] ?? 'other',
            'path' => $data['path'],
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'caption' => $data['caption'] ?? null,
            'visibility' => $data['visibility'] ?? 'public',
            'approved_for_public_display' => (bool) ($data['approved_for_public_display'] ?? false),
            'public_display_at' => isset($data['approved_for_public_display']) && $data['approved_for_public_display'] ? now() : null,
        ]);
    }

    /**
     * @return Collection<int, ReviewMedia>
     */
    public function getVisibleMedia(User $user, Review $review): Collection
    {
        $isGuest = (int) $user->id === (int) $review->guest_user_id;
        $isHost = (int) $user->id === (int) $review->host_user_id;

        return $review->reviewMedia()
            ->where(function ($query) use ($isGuest, $isHost): void {
                $query
                    ->where(function ($public): void {
                        $public
                            ->where('visibility', 'public')
                            ->where('approved_for_public_display', true);
                    });

                if ($isGuest || $isHost) {
                    $query->orWhere('visibility', 'guest_and_host');
                }

                if ($isHost) {
                    $query->orWhere('visibility', 'host_only');
                }
            })
            ->where('visibility', '!=', 'internal_future')
            ->get();
    }

    public function markApprovedForPublicDisplay(ReviewMedia $media): ReviewMedia
    {
        $media->forceFill([
            'approved_for_public_display' => true,
            'public_display_at' => now(),
        ])->save();

        return $media->refresh();
    }

    public function hideMedia(ReviewMedia $media, string $reason): ReviewMedia
    {
        $media->forceFill([
            'visibility' => 'internal_future',
            'approved_for_public_display' => false,
            'public_display_at' => null,
        ])->save();

        return $media->refresh();
    }

    private function canManageReviewMedia(User $author, Review $review): bool
    {
        return (int) $review->author_user_id === (int) $author->id
            || (int) $review->reviewer_id === (int) $author->id;
    }
}
