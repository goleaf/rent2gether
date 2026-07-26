<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationDigest;
use App\Models\NotificationEvent;
use App\Models\NotificationReminder;
use Illuminate\Database\Eloquent\Model;

class NotificationNumberService
{
    public function generateEventNumber(): string
    {
        return $this->nextNumber('NEVT', NotificationEvent::class, 'event_number');
    }

    public function generateNotificationNumber(): string
    {
        return $this->nextNumber('NTF', Notification::class, 'notification_number');
    }

    public function generateReminderNumber(): string
    {
        return $this->nextNumber('REM', NotificationReminder::class, 'reminder_number');
    }

    public function generateDigestNumber(): string
    {
        return $this->nextNumber('NDG', NotificationDigest::class, 'digest_number');
    }

    public function ensureUnique(string $number): string
    {
        $exists = Notification::query()->where('notification_number', $number)->exists()
            || NotificationEvent::query()->where('event_number', $number)->exists()
            || NotificationReminder::query()->where('reminder_number', $number)->exists()
            || NotificationDigest::query()->where('digest_number', $number)->exists();

        if (! $exists) {
            return $number;
        }

        $prefix = str($number)->before('-')->toString();

        return match ($prefix) {
            'NEVT' => $this->generateEventNumber(),
            'REM' => $this->generateReminderNumber(),
            'NDG' => $this->generateDigestNumber(),
            default => $this->generateNotificationNumber(),
        };
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function nextNumber(string $prefix, string $model, string $column): string
    {
        $year = now()->format('Y');
        $latest = $model::query()
            ->where($column, 'like', $prefix.'-'.$year.'-%')
            ->orderByDesc($column)
            ->value($column);

        $next = $latest ? ((int) str($latest)->afterLast('-')->toString()) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $next);
    }
}
