<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingListingMismatchMedia;
use App\Models\BookingListingMismatchReport;
use App\Models\User;

class ListingMismatchPrivacyService
{
    public function canGuestCreate(User $guest, Booking $booking): bool
    {
        return (int) $booking->guest_user_id === (int) $guest->id;
    }

    public function canGuestView(User $guest, BookingListingMismatchReport $report): bool
    {
        return (int) $report->guest_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, BookingListingMismatchReport $report): bool
    {
        return (int) $report->host_user_id === (int) $host->id;
    }

    public function canHostRespond(User $host, BookingListingMismatchReport $report): bool
    {
        return $this->canHostView($host, $report) && ! in_array((string) $report->status, ['closed', 'cancelled'], true);
    }

    public function canViewMedia(User $user, BookingListingMismatchMedia $media): bool
    {
        $media->loadMissing('report');
        $report = $media->report;

        if (! $report instanceof BookingListingMismatchReport) {
            return false;
        }

        return match ($media->visibility) {
            'guest_and_host' => $this->canGuestView($user, $report) || $this->canHostView($user, $report),
            'guest_only' => $this->canGuestView($user, $report),
            'host_only' => $this->canHostView($user, $report),
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingListingMismatchReport $report): array
    {
        abort_unless($this->canGuestView($guest, $report), 403);

        return $this->withoutInternalFields($report);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingListingMismatchReport $report): array
    {
        abort_unless($this->canHostView($host, $report), 403);

        return $this->withoutInternalFields($report);
    }

    /**
     * @return array<string, mixed>
     */
    private function withoutInternalFields(BookingListingMismatchReport $report): array
    {
        return collect($report->toArray())
            ->except(['future_review_required', 'future_review_comment'])
            ->all();
    }
}
