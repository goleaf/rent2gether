<?php

namespace App\Services\Bookings;

use App\Models\BookingExtension;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

class BookingExtensionNotificationService
{
    public function notifyHostExtensionRequested(BookingExtension $extension): void
    {
        $this->notify($extension->host, $extension, 'booking_extension_requested');
    }

    public function notifyGuestExtensionApproved(BookingExtension $extension): void
    {
        $this->notify($extension->guest, $extension, 'booking_extension_approved');
    }

    public function notifyGuestExtensionRejected(BookingExtension $extension): void
    {
        $this->notify($extension->guest, $extension, 'booking_extension_rejected');
    }

    public function notifyGuestPaymentRequired(BookingExtension $extension): void
    {
        $this->notify($extension->guest, $extension, 'booking_extension_payment_required');
    }

    public function notifyGuestExtensionApplied(BookingExtension $extension): void
    {
        $this->notify($extension->guest, $extension, 'booking_extension_applied');
    }

    public function notifyHostExtensionApplied(BookingExtension $extension): void
    {
        $this->notify($extension->host, $extension, 'booking_extension_applied');
    }

    public function notifyExtensionExpired(BookingExtension $extension): void
    {
        $this->notify($extension->guest, $extension, 'booking_extension_expired');
        $this->notify($extension->host, $extension, 'booking_extension_expired');
    }

    public function notifyExtensionCancelled(BookingExtension $extension): void
    {
        $this->notify($extension->guest, $extension, 'booking_extension_cancelled');
        $this->notify($extension->host, $extension, 'booking_extension_cancelled');
    }

    private function notify(?User $user, BookingExtension $extension, string $type): void
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
            'sleeping_place_id' => $extension->sleeping_place_id,
            'data' => [
                'booking_id' => $extension->booking_id,
                'booking_extension_id' => $extension->id,
                'extension_number' => $extension->extension_number,
                'date' => $extension->new_check_out_date?->toDateString(),
                'amount' => (float) $extension->total_payable,
                'currency' => $extension->currency,
                'status' => $extension->status instanceof \BackedEnum ? $extension->status->value : (string) $extension->status,
            ],
            'title_key' => 'booking_extensions.notifications.'.$type.'.title',
            'body_key' => 'booking_extensions.notifications.'.$type.'.body',
            'action_url' => null,
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }
}
