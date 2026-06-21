<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Models\BookingNoShowMedia;
use App\Models\User;

class BookingNoShowPrivacyService
{
    public function canGuestView(User $guest, BookingNoShow $noShow): bool
    {
        return (int) $noShow->guest_user_id === (int) $guest->id
            || (int) $noShow->booking?->guest_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, BookingNoShow $noShow): bool
    {
        return (int) $noShow->host_user_id === (int) $host->id
            || (int) $noShow->booking?->host_user_id === (int) $host->id;
    }

    public function canHostReport(User $host, Booking $booking): bool
    {
        return (int) $booking->host_user_id === (int) $host->id;
    }

    public function canGuestRespond(User $guest, BookingNoShow $noShow): bool
    {
        return $this->canGuestView($guest, $noShow)
            && ! in_array($noShow->status, ['closed', 'cancelled'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingNoShow $noShow): array
    {
        abort_unless($this->canGuestView($guest, $noShow), 403);

        return $noShow->only([
            'no_show_number',
            'booking_id',
            'status',
            'reason_key',
            'check_in_date',
            'planned_check_in_time',
            'waiting_until',
            'guest_response_type',
            'decision_key',
            'refund_or_penalty_status',
            'refund_amount',
            'penalty_amount',
            'deposit_refund_amount',
            'cleaning_fee_refund_amount',
            'service_fee_refund_amount',
            'currency',
            'calendar_release_status',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingNoShow $noShow): array
    {
        abort_unless($this->canHostView($host, $noShow), 403);

        return collect($noShow->toArray())
            ->except(['future_support_comment'])
            ->all();
    }

    public function canViewMedia(User $user, BookingNoShowMedia $media): bool
    {
        $media->loadMissing('noShow');
        $noShow = $media->noShow;

        return match ($media->visibility) {
            'guest_and_host' => $this->canGuestView($user, $noShow) || $this->canHostView($user, $noShow),
            'guest_only' => $this->canGuestView($user, $noShow),
            'host_only' => $this->canHostView($user, $noShow),
            default => false,
        };
    }
}
