<?php

namespace App\Services\Waitlist;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;
use App\Services\Notifications\NotificationService;

class WaitlistNotificationService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function notifyJoined(WaitlistItem $item): void
    {
        $item->loadMissing('user');

        if (! $item->user instanceof User) {
            return;
        }

        $this->notifications->create(
            user: $item->user,
            type: 'waitlist_joined',
            params: ['position' => $item->position],
            actionUrl: route('waitlist.index', ['locale' => $this->localeFor($item->user)]),
            data: ['waitlist_item_id' => $item->id],
        );
    }

    public function notifyOfferCreated(WaitlistOffer $offer): void
    {
        $offer->loadMissing('user');

        if (! $offer->user instanceof User) {
            return;
        }

        $offer->update(['notification_sent_at' => now()]);

        $this->notifications->create(
            user: $offer->user,
            type: 'waitlist_offer_created',
            params: ['deadline' => $offer->offer_expires_at?->format('H:i')],
            actionUrl: route('waitlist.offers.show', [
                'locale' => $this->localeFor($offer->user),
                'waitlistOffer' => $offer,
            ]),
            data: ['waitlist_offer_id' => $offer->id],
        );
    }

    public function notifyOfferExpiring(WaitlistOffer $offer): void
    {
        $this->notifySimpleOffer($offer, 'waitlist_offer_expiring');
    }

    public function notifyOfferExpired(WaitlistOffer $offer): void
    {
        $this->notifySimpleOffer($offer, 'waitlist_offer_expired');
    }

    public function notifySkippedToNext(WaitlistItem $item): void
    {
        $item->loadMissing('user');

        if ($item->user instanceof User) {
            $this->notifications->create(
                user: $item->user,
                type: 'waitlist_skipped_to_next',
                actionUrl: route('waitlist.index', ['locale' => $this->localeFor($item->user)]),
                data: ['waitlist_item_id' => $item->id],
            );
        }
    }

    public function notifyPlaceUnavailableAgain(WaitlistItem $item): void
    {
        $item->loadMissing('user');

        if ($item->user instanceof User) {
            $this->notifications->create(
                user: $item->user,
                type: 'waitlist_place_unavailable_again',
                actionUrl: route('waitlist.index', ['locale' => $this->localeFor($item->user)]),
                data: ['waitlist_item_id' => $item->id],
            );
        }
    }

    public function notifyHostHasWaitingGuests(SleepingPlace $place): void
    {
        $place->loadMissing('property.host');
        $host = $place->property?->host;

        if (! $host instanceof User) {
            return;
        }

        $this->notifications->create(
            user: $host,
            type: 'host_waiting_guests_available',
            actionUrl: route('host.sleeping-places.edit', [
                'locale' => $this->localeFor($host),
                'room' => $place->room_id,
                'sleepingPlace' => $place,
            ]),
            data: ['sleeping_place_id' => $place->id],
        );
    }

    private function notifySimpleOffer(WaitlistOffer $offer, string $type): void
    {
        $offer->loadMissing('user');

        if ($offer->user instanceof User) {
            $this->notifications->create(
                user: $offer->user,
                type: $type,
                actionUrl: route('waitlist.offers.show', [
                    'locale' => $this->localeFor($offer->user),
                    'waitlistOffer' => $offer,
                ]),
                data: ['waitlist_offer_id' => $offer->id],
            );
        }
    }

    private function localeFor(User $user): string
    {
        return $user->setting?->locale ?: app()->getLocale();
    }
}
