<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class NotificationNotificationCenterService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function getForUser(User $user, array $filters = []): CursorPaginator
    {
        $notifications = Notification::query()
            ->select([
                'id',
                'notification_number',
                'user_id',
                'recipient_user_id',
                'recipient_type',
                'notification_category',
                'notification_type',
                'priority',
                'title_key',
                'body_key',
                'title_translation_key',
                'body_translation_key',
                'translation_params_json',
                'data',
                'locale',
                'status',
                'action_type',
                'action_url',
                'created_at',
                'read_at',
                'is_read',
                'is_urgent',
                'is_critical',
            ])
            ->forUser($user)
            ->when(($filters['filter'] ?? null) === 'unread', fn ($query) => $query->unread())
            ->when(($filters['filter'] ?? null) === 'urgent', fn ($query) => $query->where(fn ($query) => $query->where('is_urgent', true)->orWhere('is_critical', true)))
            ->when(($filters['category'] ?? null), fn ($query, string $category) => $query->where('notification_category', $category))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(20);

        collect($notifications->items())->each->makeHidden('action_url');

        return $notifications;
    }

    public function getUnreadCount(User $user): int
    {
        return Notification::query()->forUser($user)->unread()->count();
    }

    public function getUrgentUnreadCount(User $user): int
    {
        return Notification::query()->forUser($user)->urgentUnread()->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<string, Collection<int, Notification>>
     */
    public function getGroupedByDate(User $user, array $filters = []): Collection
    {
        return collect($this->getForUser($user, $filters)->items())
            ->groupBy(fn (Notification $notification): string => $notification->created_at?->isToday() ? 'today' : 'earlier');
    }

    public function markAllRead(User $user): int
    {
        return Notification::query()
            ->forUser($user)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'status' => 'read',
            ]);
    }

    public function dismissAllRead(User $user): int
    {
        return Notification::query()
            ->forUser($user)
            ->where('is_read', true)
            ->update([
                'is_dismissed' => true,
                'dismissed_at' => now(),
                'status' => 'dismissed',
            ]);
    }
}
