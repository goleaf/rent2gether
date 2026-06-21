<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

class BookingRelocationNotificationService
{
    public function notifyGuestRelocationRequested(BookingRelocation $relocation): void
    {
        $this->notify($relocation->guest, $relocation, 'booking_relocation_requested');
    }

    public function notifyHostRelocationRequested(BookingRelocation $relocation): void
    {
        $this->notify($relocation->host, $relocation, 'booking_relocation_requested');
    }

    public function notifyGuestRelocationOffered(BookingRelocation $relocation): void
    {
        $this->notify($relocation->guest, $relocation, 'booking_relocation_offered');
    }

    public function notifyGuestConsentRequired(BookingRelocation $relocation): void
    {
        $this->notify($relocation->guest, $relocation, 'booking_relocation_guest_consent_required');
    }

    public function notifyHostConsentRequired(BookingRelocation $relocation): void
    {
        $this->notify($relocation->host, $relocation, 'booking_relocation_host_consent_required');
    }

    public function notifyPaymentRequired(BookingRelocation $relocation): void
    {
        $this->notify($relocation->guest, $relocation, 'booking_relocation_payment_required');
    }

    public function notifyRefundCreated(BookingRelocation $relocation): void
    {
        $this->notify($relocation->guest, $relocation, 'booking_relocation_refund_created');
    }

    public function notifyRelocationApplied(BookingRelocation $relocation): void
    {
        $this->notify($relocation->guest, $relocation, 'booking_relocation_applied');
        $this->notify($relocation->host, $relocation, 'booking_relocation_applied');
    }

    public function notifyRelocationRejected(BookingRelocation $relocation): void
    {
        $this->notify($relocation->guest, $relocation, 'booking_relocation_rejected');
    }

    public function notifyRelocationCancelled(BookingRelocation $relocation): void
    {
        $this->notify($relocation->guest, $relocation, 'booking_relocation_cancelled');
        $this->notify($relocation->host, $relocation, 'booking_relocation_cancelled');
    }

    private function notify(?User $user, BookingRelocation $relocation, string $type): void
    {
        if (! $user instanceof User) {
            return;
        }

        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'sleeping_place_id' => $relocation->new_sleeping_place_id ?: $relocation->current_sleeping_place_id,
            'data' => [
                'booking_id' => $relocation->original_booking_id,
                'booking_relocation_id' => $relocation->id,
                'relocation_number' => $relocation->relocation_number,
                'date' => $relocation->relocation_date?->toDateString(),
                'amount' => (float) $relocation->additional_payment_amount,
                'currency' => $relocation->currency,
                'status' => $relocation->status,
            ],
            'title_key' => 'booking_relocations.notifications.'.$type.'.title',
            'body_key' => 'booking_relocations.notifications.'.$type.'.body',
            'action_url' => null,
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }
}
