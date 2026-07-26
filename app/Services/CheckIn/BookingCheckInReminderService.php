<?php

namespace App\Services\CheckIn;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\CheckInNotificationIntegrationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingCheckInReminderService
{
    public function createRemindersForBooking(Booking $booking): void
    {
        app(BookingCheckInService::class)->createForBooking($booking);
        app(CheckInNotificationIntegrationService::class)->scheduleCheckInReminders($booking);
    }

    public function sendGuestReminder(BookingCheckIn $checkIn): void
    {
        $checkIn->loadMissing('guest:id,name');

        if ($checkIn->guest instanceof User) {
            $this->sendReminderForRecipient($checkIn->guest, $checkIn, 'guest');
        }
    }

    public function sendHostReminder(BookingCheckIn $checkIn): void
    {
        $checkIn->loadMissing('host:id,name,is_host');

        if ($checkIn->host instanceof User) {
            $this->sendReminderForRecipient($checkIn->host, $checkIn, 'host');
        }
    }

    public function sendDueReminders(User $user): int
    {
        $tomorrow = CarbonImmutable::today()->addDay()->toDateString();
        $sent = 0;

        $this->dueCheckInsForRecipient('guest_user_id', $user, $tomorrow)
            ->each(function (BookingCheckIn $checkIn) use ($user, &$sent): void {
                if ($this->sendReminderForRecipient($user, $checkIn, 'guest')) {
                    $sent++;
                }
            });

        $this->dueCheckInsForRecipient('host_user_id', $user, $tomorrow)
            ->each(function (BookingCheckIn $checkIn) use ($user, &$sent): void {
                if ($this->sendReminderForRecipient($user, $checkIn, 'host')) {
                    $sent++;
                }
            });

        return $sent;
    }

    public function sendDueRemindersForAll(): int
    {
        $tomorrow = CarbonImmutable::today()->addDay()->toDateString();
        $sent = 0;

        BookingCheckIn::query()
            ->whereDate('check_in_date', $tomorrow)
            ->whereNotIn('status', ['checked_in', 'failed', 'no_show', 'cancelled'])
            ->with(['guest:id,name', 'host:id,name,is_host'])
            ->orderBy('id')
            ->get()
            ->each(function (BookingCheckIn $checkIn) use (&$sent): void {
                if ($checkIn->guest instanceof User && $this->sendReminderForRecipient($checkIn->guest, $checkIn, 'guest')) {
                    $sent++;
                }

                if ($checkIn->host instanceof User && $this->sendReminderForRecipient($checkIn->host, $checkIn, 'host')) {
                    $sent++;
                }
            });

        return $sent;
    }

    public function markReminderSent(BookingCheckIn $checkIn): BookingCheckIn
    {
        $checkIn->forceFill([
            'status' => $checkIn->status === 'not_started' || $checkIn->status === 'instructions_available'
                ? 'reminder_sent'
                : $checkIn->status,
            'last_reminder_sent_at' => now(),
        ])->save();

        return $checkIn->refresh();
    }

    private function sendReminderForRecipient(User $user, BookingCheckIn $checkIn, string $recipientType): bool
    {
        if ($this->notificationAlreadySent($user, $checkIn, $recipientType)) {
            return false;
        }

        if ($recipientType === 'host') {
            app(BookingCheckInNotificationService::class)->notifyHostCheckInSoon($checkIn);
        } else {
            app(BookingCheckInNotificationService::class)->notifyGuestCheckInSoon($checkIn);
        }

        $this->markReminderSent($checkIn);

        return true;
    }

    private function notificationAlreadySent(User $user, BookingCheckIn $checkIn, string $recipientType): bool
    {
        return Notification::query()
            ->where('recipient_user_id', $user->id)
            ->where('booking_id', $checkIn->booking_id)
            ->where('recipient_type', $recipientType)
            ->where('type', 'check_in_soon')
            ->exists();
    }

    /**
     * @return Collection<int, BookingCheckIn>
     */
    private function dueCheckInsForRecipient(string $column, User $user, string $date): Collection
    {
        return BookingCheckIn::query()
            ->where($column, $user->id)
            ->whereDate('check_in_date', $date)
            ->whereNotIn('status', ['checked_in', 'failed', 'no_show', 'cancelled'])
            ->with(['guest:id,name', 'host:id,name,is_host'])
            ->orderBy('id')
            ->get();
    }
}
