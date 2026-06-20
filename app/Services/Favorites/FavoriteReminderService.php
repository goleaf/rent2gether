<?php

namespace App\Services\Favorites;

use App\Models\Favorite;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class FavoriteReminderService
{
    public function schedule(User $user, Favorite $favorite, CarbonInterface $remindAt, ?string $text = null): Favorite
    {
        $this->authorize($user, $favorite);

        $favorite->update([
            'remind_at' => $remindAt,
            'reminder_text' => $text === null ? null : mb_substr(trim($text), 0, 1000),
            'reminder_sent_at' => null,
        ]);

        return $favorite->refresh();
    }

    public function cancel(User $user, Favorite $favorite): Favorite
    {
        $this->authorize($user, $favorite);

        $favorite->update([
            'remind_at' => null,
            'reminder_text' => null,
            'reminder_sent_at' => null,
        ]);

        return $favorite->refresh();
    }

    /**
     * @return Collection<int, Favorite>
     */
    public function dueReminders(?User $user = null): Collection
    {
        return Favorite::query()
            ->select(['id', 'user_id', 'sleeping_place_id', 'remind_at', 'reminder_text', 'reminder_sent_at'])
            ->whereNotNull('remind_at')
            ->whereNull('reminder_sent_at')
            ->where('remind_at', '<=', now())
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->with([
                'user:id',
                'sleepingPlace:id,room_id,property_id,display_name,place_number',
            ])
            ->orderBy('remind_at')
            ->limit(100)
            ->get();
    }

    public function markSent(Favorite $favorite): void
    {
        $favorite->update(['reminder_sent_at' => now()]);
    }

    private function authorize(User $user, Favorite $favorite): void
    {
        if ((int) $favorite->user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }
}
