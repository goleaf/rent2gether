<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\User;

class NotificationDueProcessorService
{
    public function __construct(private readonly NotificationReminderService $reminders) {}

    public function processDueForUser(User $user): int
    {
        return $this->reminders->getDueReminders($user)
            ->reduce(function (int $count, $reminder): int {
                return $this->reminders->processReminder($reminder) ? $count + 1 : $count;
            }, 0);
    }

    public function processDueForBooking(Booking $booking): int
    {
        return $this->reminders->getDueReminders()
            ->where('booking_id', $booking->id)
            ->reduce(function (int $count, $reminder): int {
                return $this->reminders->processReminder($reminder) ? $count + 1 : $count;
            }, 0);
    }

    public function processDueForHost(User $host): int
    {
        return $this->processDueForUser($host);
    }

    public function processDueGlobalLimit(int $limit = 100): int
    {
        return $this->reminders->getDueReminders()
            ->take($limit)
            ->reduce(function (int $count, $reminder): int {
                return $this->reminders->processReminder($reminder) ? $count + 1 : $count;
            }, 0);
    }
}
