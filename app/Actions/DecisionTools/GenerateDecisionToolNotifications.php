<?php

namespace App\Actions\DecisionTools;

use App\Models\Favorite;
use App\Models\Notification;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Illuminate\Support\Str;

class GenerateDecisionToolNotifications
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PricingService $pricing,
    ) {}

    public function handle(): int
    {
        return $this->favoritePriceDrops()
            + $this->waitlistedAvailability()
            + $this->waitlistedPriceDrops();
    }

    private function favoritePriceDrops(): int
    {
        $created = 0;

        Favorite::query()
            ->select(['id', 'user_id', 'sleeping_place_id', 'price_at_save', 'check_in', 'check_out', 'guests_count', 'notify_price_drop'])
            ->where('notify_price_drop', true)
            ->whereNotNull('sleeping_place_id')
            ->whereNotNull('price_at_save')
            ->with([
                'user:id',
                'user.setting:id,user_id,locale',
                'sleepingPlace:id,room_id,property_id,base_price_per_night,weekly_price,monthly_price,weekend_price,cleaning_fee,deposit_amount,currency,max_guests,min_nights,max_nights',
            ])
            ->chunkById(100, function ($favorites) use (&$created): void {
                foreach ($favorites as $favorite) {
                    if (! $favorite->user instanceof User || ! $favorite->sleepingPlace instanceof SleepingPlace) {
                        continue;
                    }

                    $currentPrice = $this->currentFavoritePrice($favorite);

                    if ($currentPrice >= (float) $favorite->price_at_save - 0.01) {
                        continue;
                    }

                    $actionUrl = $this->placeUrl($favorite->user, $favorite->sleepingPlace, $favorite->check_in?->toDateString(), $favorite->check_out?->toDateString());

                    if ($this->notificationExists($favorite->user, 'decision.favorite_price_drop', $actionUrl)) {
                        continue;
                    }

                    $this->createNotification(
                        user: $favorite->user,
                        type: 'decision.favorite_price_drop',
                        titleKey: 'notifications.decision.favorite_price_drop.title',
                        bodyKey: 'notifications.decision.favorite_price_drop.body',
                        actionUrl: $actionUrl,
                        data: [
                            'favorite_id' => $favorite->id,
                            'sleeping_place_id' => $favorite->sleeping_place_id,
                            'old_price' => (float) $favorite->price_at_save,
                            'current_price' => $currentPrice,
                        ],
                    );

                    $created++;
                }
            });

        return $created;
    }

    private function waitlistedAvailability(): int
    {
        $created = 0;

        WaitlistItem::query()
            ->select(['id', 'user_id', 'sleeping_place_id', 'desired_check_in', 'desired_check_out', 'notify_available', 'notified', 'status'])
            ->where('status', 'waiting')
            ->where('notify_available', true)
            ->where('notified', false)
            ->whereNotNull('desired_check_in')
            ->whereNotNull('desired_check_out')
            ->with([
                'user:id',
                'user.setting:id,user_id,locale',
                'sleepingPlace:id,room_id,property_id,status,base_price_per_night,currency',
                'sleepingPlace.room:id,property_id,status',
                'sleepingPlace.property:id,status',
            ])
            ->chunkById(100, function ($items) use (&$created): void {
                foreach ($items as $item) {
                    if (! $item->user instanceof User || ! $item->sleepingPlace instanceof SleepingPlace) {
                        continue;
                    }

                    if (! $this->availability->isAvailable($item->sleepingPlace, $item->desired_check_in, $item->desired_check_out)) {
                        continue;
                    }

                    $actionUrl = $this->placeUrl($item->user, $item->sleepingPlace, $item->desired_check_in?->toDateString(), $item->desired_check_out?->toDateString());

                    if ($this->notificationExists($item->user, 'decision.waitlist_available', $actionUrl)) {
                        continue;
                    }

                    $this->createNotification(
                        user: $item->user,
                        type: 'decision.waitlist_available',
                        titleKey: 'notifications.decision.waitlist_available.title',
                        bodyKey: 'notifications.decision.waitlist_available.body',
                        actionUrl: $actionUrl,
                        data: [
                            'waitlist_item_id' => $item->id,
                            'sleeping_place_id' => $item->sleeping_place_id,
                            'check_in' => $item->desired_check_in?->toDateString(),
                            'check_out' => $item->desired_check_out?->toDateString(),
                        ],
                    );

                    $item->forceFill([
                        'notified' => true,
                        'notified_at' => now(),
                    ])->save();

                    $created++;
                }
            });

        return $created;
    }

    private function waitlistedPriceDrops(): int
    {
        $created = 0;

        WaitlistItem::query()
            ->select(['id', 'user_id', 'sleeping_place_id', 'desired_check_in', 'desired_check_out', 'price_at_join', 'notify_price_drop', 'status'])
            ->where('status', 'waiting')
            ->where('notify_price_drop', true)
            ->whereNotNull('price_at_join')
            ->with([
                'user:id',
                'user.setting:id,user_id,locale',
                'sleepingPlace:id,room_id,property_id,base_price_per_night,weekly_price,monthly_price,weekend_price,cleaning_fee,deposit_amount,currency,max_guests,min_nights,max_nights',
            ])
            ->chunkById(100, function ($items) use (&$created): void {
                foreach ($items as $item) {
                    if (! $item->user instanceof User || ! $item->sleepingPlace instanceof SleepingPlace) {
                        continue;
                    }

                    $currentPrice = $this->currentWaitlistPrice($item);

                    if ($currentPrice >= (float) $item->price_at_join - 0.01) {
                        continue;
                    }

                    $actionUrl = $this->placeUrl($item->user, $item->sleepingPlace, $item->desired_check_in?->toDateString(), $item->desired_check_out?->toDateString());

                    if ($this->notificationExists($item->user, 'decision.waitlist_price_drop', $actionUrl)) {
                        continue;
                    }

                    $this->createNotification(
                        user: $item->user,
                        type: 'decision.waitlist_price_drop',
                        titleKey: 'notifications.decision.waitlist_price_drop.title',
                        bodyKey: 'notifications.decision.waitlist_price_drop.body',
                        actionUrl: $actionUrl,
                        data: [
                            'waitlist_item_id' => $item->id,
                            'sleeping_place_id' => $item->sleeping_place_id,
                            'old_price' => (float) $item->price_at_join,
                            'current_price' => $currentPrice,
                        ],
                    );

                    $created++;
                }
            });

        return $created;
    }

    private function currentFavoritePrice(Favorite $favorite): float
    {
        if (! $favorite->check_in || ! $favorite->check_out) {
            return (float) $favorite->sleepingPlace->base_price_per_night;
        }

        return $this->pricing
            ->calculate($favorite->user, $favorite->sleepingPlace, $favorite->check_in, $favorite->check_out, max(1, (int) $favorite->guests_count))
            ->totalAmount;
    }

    private function currentWaitlistPrice(WaitlistItem $item): float
    {
        if (! $item->desired_check_in || ! $item->desired_check_out) {
            return (float) $item->sleepingPlace->base_price_per_night;
        }

        return $this->pricing
            ->calculate($item->user, $item->sleepingPlace, $item->desired_check_in, $item->desired_check_out)
            ->totalAmount;
    }

    private function createNotification(User $user, string $type, string $titleKey, string $bodyKey, string $actionUrl, array $data): void
    {
        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'data' => $data,
            'title_key' => $titleKey,
            'body_key' => $bodyKey,
            'action_url' => $actionUrl,
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }

    private function notificationExists(User $user, string $type, string $actionUrl): bool
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->where('action_url', $actionUrl)
            ->where('status', 'unread')
            ->exists();
    }

    private function placeUrl(User $user, SleepingPlace $place, ?string $checkIn, ?string $checkOut): string
    {
        return route('places.show', array_filter([
            'locale' => $user->setting?->locale ?: config('app.fallback_locale', 'en'),
            'sleepingPlace' => $place,
            'in' => $checkIn,
            'out' => $checkOut,
        ]));
    }
}
