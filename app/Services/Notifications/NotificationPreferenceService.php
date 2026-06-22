<?php

namespace App\Services\Notifications;

use App\Models\NotificationCategoryPreference;
use App\Models\NotificationPreference;
use App\Models\User;

class NotificationPreferenceService
{
    public function getForUser(User $user): NotificationPreference
    {
        return $this->getOrCreateForUser($user);
    }

    public function getOrCreateForUser(User $user): NotificationPreference
    {
        return NotificationPreference::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'language_locale' => $user->preferred_locale ?: app()->getLocale(),
                'timezone' => config('app.timezone', 'UTC'),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePreferences(User $user, array $data): NotificationPreference
    {
        $preference = $this->getOrCreateForUser($user);
        $preference->update(collect($data)->only($preference->getFillable())->all());

        return $preference->refresh();
    }

    public function getCategoryPreference(User $user, string $category): NotificationCategoryPreference
    {
        return NotificationCategoryPreference::query()->firstOrCreate([
            'user_id' => $user->id,
            'notification_category' => $category,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCategoryPreference(User $user, string $category, array $data): NotificationCategoryPreference
    {
        $preference = $this->getCategoryPreference($user, $category);
        $preference->update(collect($data)->only($preference->getFillable())->all());

        return $preference->refresh();
    }

    public function isChannelEnabled(User $user, string $category, string $channel, string $priority): bool
    {
        if ($channel === 'in_app' && $priority === 'critical') {
            return true;
        }

        $preference = $this->getOrCreateForUser($user);
        $categoryPreference = $this->getCategoryPreference($user, $category);
        $field = match ($channel) {
            'email' => 'email_enabled',
            'sms_future' => 'sms_future_enabled',
            'push_future' => 'push_future_enabled',
            default => 'in_app_enabled',
        };

        return (bool) $preference->{$field} && (bool) $categoryPreference->{$field};
    }
}
