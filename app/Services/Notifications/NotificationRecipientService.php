<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\HostRepresentative;
use App\Models\NotificationEvent;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationRecipientService
{
    /**
     * @return Collection<int, User>
     */
    public function getRecipientsForEvent(NotificationEvent $event): Collection
    {
        $event->loadMissing('booking');
        $booking = $event->booking;

        if (! $booking instanceof Booking) {
            return collect();
        }

        $recipients = collect();

        if ($this->isHostFacing($event->event_key)) {
            $host = $this->getHostRecipient($booking);
            if ($host instanceof User) {
                $recipients->push($host);
            }

            return $recipients
                ->merge($this->getHostRepresentativeRecipients($booking)->filter(fn ($representative): bool => $this->shouldNotifyRepresentative($event, $representative)))
                ->unique('id')
                ->values();
        }

        $guest = $this->getGuestRecipient($booking);

        return $guest instanceof User ? collect([$guest]) : collect();
    }

    public function getGuestRecipient(Booking $booking): ?User
    {
        $booking->loadMissing('guest:id,name');

        return $booking->guest;
    }

    public function getHostRecipient(Booking $booking): ?User
    {
        $booking->loadMissing('host:id,name');

        return $booking->host;
    }

    /**
     * @return Collection<int, User>
     */
    public function getHostRepresentativeRecipients(Booking $booking): Collection
    {
        return HostRepresentative::query()
            ->with('representativeUser:id,name')
            ->where('host_user_id', $booking->host_user_id)
            ->where('active', true)
            ->whereNotNull('representative_user_id')
            ->get()
            ->pluck('representativeUser')
            ->filter()
            ->values();
    }

    public function shouldNotifyRepresentative(NotificationEvent $event, mixed $representative): bool
    {
        if ($representative instanceof User) {
            return HostRepresentative::query()
                ->where('representative_user_id', $representative->id)
                ->where('active', true)
                ->where('can_help_with_check_in', true)
                ->exists();
        }

        return (bool) ($representative->active ?? false) && (bool) ($representative->can_help_with_check_in ?? false);
    }

    private function isHostFacing(string $eventKey): bool
    {
        return in_array($eventKey, [
            'guest_sent_message',
            'guest_arrived',
            'check_in_problem',
            'host_unresponsive_reported',
            'guest_checked_out',
            'extension_requested',
            'relocation_requested',
            'complaint_opened',
            'dispute_opened',
            'maintenance_reported',
            'cleaning_due',
            'inspection_due',
        ], true);
    }
}
