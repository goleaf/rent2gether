<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveMedia;
use App\Models\User;

class HostUnresponsivePrivacyService
{
    public function canGuestView(User $guest, BookingHostUnresponsiveCase $case): bool
    {
        return (int) $case->guest_user_id === (int) $guest->id
            || (int) $case->booking?->guest_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, BookingHostUnresponsiveCase $case): bool
    {
        return (int) $case->host_user_id === (int) $host->id
            || (int) $case->booking?->host_user_id === (int) $host->id;
    }

    public function canGuestReport(User $guest, Booking $booking): bool
    {
        return (int) $booking->guest_user_id === (int) $guest->id;
    }

    public function canHostRespond(User $host, BookingHostUnresponsiveCase $case): bool
    {
        return $this->canHostView($host, $case)
            && ! in_array($case->status, ['closed', 'cancelled'], true);
    }

    public function canViewMedia(User $user, HostUnresponsiveMedia $media): bool
    {
        $media->loadMissing('case');
        $case = $media->case;

        return match ($media->visibility) {
            'guest_and_host' => $this->canGuestView($user, $case) || $this->canHostView($user, $case),
            'guest_only' => $this->canGuestView($user, $case),
            'host_only' => $this->canHostView($user, $case),
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingHostUnresponsiveCase $case): array
    {
        abort_unless($this->canGuestView($guest, $case), 403);

        return $case->only([
            'case_number',
            'booking_id',
            'case_type',
            'reason_key',
            'status',
            'check_in_date',
            'planned_check_in_time',
            'guest_waiting_outside',
            'guest_at_address',
            'guest_feels_unsafe',
            'response_deadline_at',
            'decision_key',
            'refund_status',
            'refund_amount',
            'currency',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingHostUnresponsiveCase $case): array
    {
        abort_unless($this->canHostView($host, $case), 403);

        return collect($case->toArray())
            ->except(['future_support_comment'])
            ->all();
    }
}
