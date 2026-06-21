<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchMedia;
use App\Models\BookingListingMismatchReport;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class ListingMismatchMediaService
{
    public function __construct(
        private readonly ListingMismatchPrivacyService $privacy,
        private readonly ListingMismatchEventService $events,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function uploadGuestEvidence(User $guest, BookingListingMismatchReport $report, array $data): BookingListingMismatchMedia
    {
        if (! $this->privacy->canGuestView($guest, $report)) {
            throw new AuthorizationException(__('listing_mismatch.validation.not_allowed'));
        }

        return $this->createMedia($report, $guest, $data + ['media_role' => 'guest_real_photo']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function uploadHostEvidence(User $host, BookingListingMismatchReport $report, array $data): BookingListingMismatchMedia
    {
        if (! $this->privacy->canHostView($host, $report)) {
            throw new AuthorizationException(__('listing_mismatch.validation.not_allowed'));
        }

        return $this->createMedia($report, $host, $data + ['media_role' => 'host_fix_evidence']);
    }

    /**
     * @return Collection<int, BookingListingMismatchMedia>
     */
    public function getVisibleMedia(User $user, BookingListingMismatchReport $report): Collection
    {
        return $report->media()
            ->orderByDesc('id')
            ->get()
            ->filter(fn (BookingListingMismatchMedia $media): bool => $this->privacy->canViewMedia($user, $media))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createMedia(BookingListingMismatchReport $report, User $user, array $data): BookingListingMismatchMedia
    {
        $media = $report->media()->create([
            'booking_id' => $report->booking_id,
            'uploaded_by_user_id' => $user->id,
            'media_type' => $data['media_type'] ?? 'photo',
            'media_role' => $data['media_role'],
            'path' => (string) $data['path'],
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'caption' => $data['caption'] ?? null,
            'visibility' => $data['visibility'] ?? 'guest_and_host',
            'related_mismatch_item_id' => $data['related_mismatch_item_id'] ?? null,
        ]);

        $this->events->record($report->fresh(), 'evidence_submitted', ['user_id' => $user->id, 'media_id' => $media->id]);

        return $media->fresh();
    }
}
