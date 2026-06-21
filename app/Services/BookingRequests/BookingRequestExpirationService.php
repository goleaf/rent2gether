<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use App\Models\User;

class BookingRequestExpirationService
{
    public function __construct(
        private readonly BookingRequestAvailabilityHoldService $holds,
        private readonly BookingRequestNotificationService $notifications,
    ) {}

    public function expireRequest(BookingRequest $request): BookingRequest
    {
        if (in_array($request->status, [
            BookingRequest::STATUS_APPROVED,
            BookingRequest::STATUS_APPROVED_WAITING_PAYMENT,
            BookingRequest::STATUS_REJECTED,
            BookingRequest::STATUS_EXPIRED,
            BookingRequest::STATUS_WITHDRAWN_BY_GUEST,
            BookingRequest::STATUS_CANCELLED,
            BookingRequest::STATUS_CONVERTED_TO_BOOKING,
        ], true)) {
            return $request;
        }

        $oldStatus = $request->status;

        $request->forceFill([
            'status' => BookingRequest::STATUS_EXPIRED,
        ])->save();

        $request->statusLogs()->create([
            'user_id' => null,
            'old_status' => $oldStatus,
            'new_status' => BookingRequest::STATUS_EXPIRED,
            'reason_key' => 'booking_requests.expired',
        ]);

        $this->holds->releaseHold($request, 'expired');
        $this->notifications->notifyRequestExpired($request->fresh(['guest', 'host']));

        return $request->fresh(['dateLocks']);
    }

    public function expireDueRequestsForUser(User $user): int
    {
        return $this->expireQuery(
            BookingRequest::query()
                ->forGuest($user)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
        );
    }

    public function expireDueRequestsForHost(User $host): int
    {
        return $this->expireQuery(
            BookingRequest::query()
                ->forHost($host)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
        );
    }

    public function releaseExpiredHolds(): int
    {
        $released = 0;

        BookingRequest::query()
            ->where('hold_dates', true)
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<=', now())
            ->whereNotIn('status', [
                BookingRequest::STATUS_REJECTED,
                BookingRequest::STATUS_EXPIRED,
                BookingRequest::STATUS_WITHDRAWN_BY_GUEST,
                BookingRequest::STATUS_CANCELLED,
                BookingRequest::STATUS_CONVERTED_TO_BOOKING,
            ])
            ->cursor()
            ->each(function (BookingRequest $request) use (&$released): void {
                $released += $this->holds->releaseHold($request, 'hold_expired');
            });

        return $released;
    }

    private function expireQuery(mixed $query): int
    {
        $expired = 0;

        $query->whereNotIn('status', [
            BookingRequest::STATUS_APPROVED,
            BookingRequest::STATUS_APPROVED_WAITING_PAYMENT,
            BookingRequest::STATUS_REJECTED,
            BookingRequest::STATUS_EXPIRED,
            BookingRequest::STATUS_WITHDRAWN_BY_GUEST,
            BookingRequest::STATUS_CANCELLED,
            BookingRequest::STATUS_CONVERTED_TO_BOOKING,
        ])
            ->cursor()
            ->each(function (BookingRequest $request) use (&$expired): void {
                $this->expireRequest($request);
                $expired++;
            });

        return $expired;
    }
}
