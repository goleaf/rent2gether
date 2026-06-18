<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case PendingHostConfirmation = 'pending_host';
    case PendingPayment = 'pending_payment';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case ActiveStay = 'active_stay';
    case CheckedOut = 'checked_out';
    case Completed = 'completed';
    case CancelledByGuest = 'cancelled_guest';
    case CancelledByHost = 'cancelled_host';
    case CancelledBySystem = 'cancelled_system';
    case NoShow = 'no_show';
    case Disputed = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingHostConfirmation => 'Awaiting host',
            self::PendingPayment => 'Awaiting payment',
            self::Confirmed => 'Confirmed',
            self::CheckedIn => 'Checked in',
            self::ActiveStay => 'Active stay',
            self::CheckedOut => 'Checked out',
            self::Completed => 'Completed',
            self::CancelledByGuest => 'Cancelled by guest',
            self::CancelledByHost => 'Cancelled by host',
            self::CancelledBySystem => 'Cancelled by system',
            self::NoShow => 'No show',
            self::Disputed => 'Disputed',
        };
    }

    public function isCancelled(): bool
    {
        return in_array($this, [
            self::CancelledByGuest,
            self::CancelledByHost,
            self::CancelledBySystem,
        ]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::CheckedIn, self::ActiveStay]);
    }
}
