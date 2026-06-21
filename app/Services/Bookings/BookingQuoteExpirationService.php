<?php

namespace App\Services\Bookings;

use App\Models\BookingQuote;
use App\Models\User;
use App\Services\Availability\SleepingPlaceDateLockService;

class BookingQuoteExpirationService
{
    public function __construct(
        private readonly SleepingPlaceDateLockService $locks,
    ) {}

    public function expireQuote(BookingQuote $quote): BookingQuote
    {
        if ($quote->status !== BookingQuote::STATUS_EXPIRED) {
            $quote->forceFill(['status' => BookingQuote::STATUS_EXPIRED])->save();
        }

        $this->locks->releaseLocksForQuote($quote, 'expired');

        return $quote;
    }

    public function expireDueQuotesForUser(User $user): int
    {
        return BookingQuote::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [BookingQuote::STATUS_DRAFT, BookingQuote::STATUS_VALID, BookingQuote::STATUS_INVALID])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get()
            ->sum(fn (BookingQuote $quote): int => $this->expireQuote($quote)->wasChanged('status') ? 1 : 0);
    }

    public function releaseExpiredQuoteLocks(): int
    {
        return $this->locks->expireOldLocks();
    }

    public function isExpired(BookingQuote $quote): bool
    {
        return $quote->status === BookingQuote::STATUS_EXPIRED
            || ($quote->expires_at !== null && $quote->expires_at->lessThanOrEqualTo(now()));
    }
}
