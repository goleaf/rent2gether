<?php

namespace App\Services\Favorites;

use App\Models\Favorite;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Number;

class FavoriteChangeNotificationService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly FavoriteReminderService $reminders,
    ) {}

    public function notifyDueReminders(?User $user = null): int
    {
        $created = 0;

        foreach ($this->reminders->dueReminders($user) as $favorite) {
            if (! $favorite->user instanceof User) {
                continue;
            }

            $this->notifications->create(
                user: $favorite->user,
                type: 'favorites.reminder_due',
                params: [
                    'place' => $this->placeTitle($favorite),
                ],
                actionUrl: $this->favoriteUrl($favorite->user, $favorite),
                data: [
                    'favorite_id' => $favorite->id,
                    'sleeping_place_id' => $favorite->sleeping_place_id,
                    'reminder_text' => $favorite->reminder_text,
                ],
            );

            $this->reminders->markSent($favorite);
            $created++;
        }

        return $created;
    }

    public function notifyPriceChange(Favorite $favorite): ?object
    {
        $favorite->loadMissing(['user:id', 'sleepingPlace:id,display_name,place_number']);

        if (! $favorite->user instanceof User || ! $favorite->price_changed) {
            return null;
        }

        $amount = (float) ($favorite->price_change_amount ?? 0);
        $isDrop = $amount < -0.01;
        $isIncrease = $amount > 0.01;

        if (($isDrop && ! $favorite->notify_price_drop) || ($isIncrease && ! $favorite->notify_price_increase)) {
            return null;
        }

        $type = $isDrop ? 'favorites.price_dropped' : 'favorites.price_increased';
        $currency = $favorite->currency ?: 'EUR';
        $locale = $this->localeFor($favorite->user);

        return $this->notifications->create(
            user: $favorite->user,
            type: $type,
            params: [
                'place' => $this->placeTitle($favorite),
                'amount' => Number::currency(abs($amount), $currency, $locale),
            ],
            actionUrl: $this->favoriteUrl($favorite->user, $favorite),
            data: [
                'favorite_id' => $favorite->id,
                'sleeping_place_id' => $favorite->sleeping_place_id,
                'price_change_amount' => $amount,
            ],
        );
    }

    public function notifyAvailabilityChange(Favorite $favorite): ?object
    {
        $favorite->loadMissing(['user:id', 'sleepingPlace:id,display_name,place_number']);

        if (! $favorite->user instanceof User) {
            return null;
        }

        if ($favorite->became_available_again && $favorite->notify_available_again) {
            return $this->notifications->create(
                user: $favorite->user,
                type: 'favorites.available_again',
                params: ['place' => $this->placeTitle($favorite)],
                actionUrl: $this->favoriteUrl($favorite->user, $favorite),
                data: [
                    'favorite_id' => $favorite->id,
                    'sleeping_place_id' => $favorite->sleeping_place_id,
                ],
            );
        }

        if ($favorite->became_unavailable && $favorite->notify_unavailable) {
            return $this->notifications->create(
                user: $favorite->user,
                type: 'favorites.unavailable',
                params: ['place' => $this->placeTitle($favorite)],
                actionUrl: $this->favoriteUrl($favorite->user, $favorite),
                data: [
                    'favorite_id' => $favorite->id,
                    'sleeping_place_id' => $favorite->sleeping_place_id,
                ],
            );
        }

        return null;
    }

    public function refreshAndNotify(Favorite $favorite): int
    {
        $created = 0;

        if ($this->notifyPriceChange($favorite)) {
            $created++;
        }

        if ($this->notifyAvailabilityChange($favorite)) {
            $created++;
        }

        return $created;
    }

    private function favoriteUrl(User $user, Favorite $favorite): string
    {
        if ($favorite->sleeping_place_id) {
            return route('places.show', [
                'locale' => $this->localeFor($user),
                'sleepingPlace' => $favorite->sleeping_place_id,
            ]);
        }

        return route('favorites.index', ['locale' => $this->localeFor($user)]);
    }

    private function placeTitle(Favorite $favorite): string
    {
        $place = $favorite->sleepingPlace;

        if ($place instanceof SleepingPlace) {
            return $place->display_name ?: __('search.card.untitled', ['number' => $place->place_number ?: $place->id]);
        }

        return __('favorites.place_fallback');
    }

    private function localeFor(User $user): string
    {
        $user->loadMissing('setting:id,user_id,locale');

        return $user->setting?->locale ?: app()->getLocale();
    }
}
