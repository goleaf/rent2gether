<?php

namespace App\Services\SavedSearches;

use App\Models\SavedSearch;
use App\Models\User;
use App\Services\NotificationService;

class SavedSearchNotificationService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly SavedSearchFrequencyService $frequency,
    ) {}

    public function notifyNewMatches(SavedSearch $search): void
    {
        if (! $search->notify_new_matches) {
            return;
        }

        $count = $search->results()->newMatches()->count();

        if ($count < 1) {
            return;
        }

        $this->createInAppNotification($search->user, $search, 'saved_search_new_matches', [
            'count' => $count,
        ]);
    }

    public function notifyPriceDrops(SavedSearch $search): void
    {
        if (! $search->notify_price_drops) {
            return;
        }

        $count = $search->results()->priceDropped()->count();

        if ($count < 1) {
            return;
        }

        $this->createInAppNotification($search->user, $search, 'saved_search_price_drop', [
            'count' => $count,
        ]);
    }

    public function notifyAvailableAgain(SavedSearch $search): void
    {
        if (! $search->notify_available_again) {
            return;
        }

        $count = $search->results()->availableAgain()->count();

        if ($count < 1) {
            return;
        }

        $this->createInAppNotification($search->user, $search, 'saved_search_available_again', [
            'count' => $count,
        ]);
    }

    public function notifyBetterMatches(SavedSearch $search): void
    {
        if (! $search->notify_better_match) {
            return;
        }

        $count = $search->results()
            ->where('match_score', '>=', 95)
            ->where('is_new_match', true)
            ->count();

        if ($count < 1) {
            return;
        }

        $this->createInAppNotification($search->user, $search, 'saved_search_better_match', [
            'count' => $count,
        ]);
    }

    /**
     * @param  array<string, scalar|null>  $data
     */
    public function createInAppNotification(?User $user, SavedSearch $search, string $type, array $data = []): void
    {
        if (! $user instanceof User) {
            return;
        }

        $params = [
            'search' => $search->displayTitle(),
            'count' => $data['count'] ?? null,
        ];

        $this->notifications->create(
            user: $user,
            type: $type,
            params: $params,
            actionUrl: route('saved-searches.show', [
                'locale' => app()->getLocale(),
                'savedSearch' => $search,
            ]),
            data: [
                'saved_search_id' => $search->id,
                'quiet_hours' => $this->frequency->isQuietHours($search),
                'urgent' => ! $this->frequency->isQuietHours($search),
                ...$data,
            ],
        );

        $search->forceFill(['last_notified_at' => now()])->save();
    }
}
