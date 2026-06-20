<?php

namespace App\Services\CheckIn;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\User;
use Carbon\CarbonImmutable;

class BookingCheckInReminderService
{
    public function createRemindersForBooking(Booking $booking): void
    {
        app(BookingCheckInService::class)->createForBooking($booking);
    }

    public function sendGuestReminder(BookingCheckIn $checkIn): void
    {
        $this->markReminderSent($checkIn);
    }

    public function sendHostReminder(BookingCheckIn $checkIn): void
    {
        $this->markReminderSent($checkIn);
    }

    public function sendDueReminders(User $user): int
    {
        $tomorrow = CarbonImmutable::today()->addDay()->toDateString();
        $sent = 0;

        BookingCheckIn::query()
            ->where(function ($query) use ($user): void {
                $query->where('guest_user_id', $user->id)
                    ->orWhere('host_user_id', $user->id);
            })
            ->whereDate('check_in_date', $tomorrow)
            ->whereNull('last_reminder_sent_at')
            ->whereNotIn('status', ['checked_in', 'failed', 'no_show', 'cancelled'])
            ->orderBy('id')
            ->get()
            ->each(function (BookingCheckIn $checkIn) use (&$sent): void {
                $this->markReminderSent($checkIn);
                $sent++;
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
}
