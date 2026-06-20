<?php

namespace App\Services\CheckOut;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\User;
use Carbon\CarbonImmutable;

class BookingCheckOutReminderService
{
    public function createRemindersForBooking(Booking $booking): void
    {
        app(BookingCheckOutService::class)->createForBooking($booking);
    }

    public function sendGuestReminder(BookingCheckOut $checkOut): void
    {
        $this->markReminderSent($checkOut);
    }

    public function sendHostReminder(BookingCheckOut $checkOut): void
    {
        $this->markReminderSent($checkOut);
    }

    public function sendDueReminders(User $user): int
    {
        $tomorrow = CarbonImmutable::today()->addDay()->toDateString();
        $sent = 0;

        BookingCheckOut::query()
            ->where(function ($query) use ($user): void {
                $query->where('guest_user_id', $user->id)
                    ->orWhere('host_user_id', $user->id);
            })
            ->whereDate('check_out_date', $tomorrow)
            ->whereNull('last_reminder_sent_at')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('id')
            ->get()
            ->each(function (BookingCheckOut $checkOut) use (&$sent): void {
                $this->markReminderSent($checkOut);
                $sent++;
            });

        return $sent;
    }

    public function markReminderSent(BookingCheckOut $checkOut): BookingCheckOut
    {
        $checkOut->forceFill([
            'status' => in_array($checkOut->status, ['not_started', 'waiting_for_checkout'], true)
                ? 'reminder_sent'
                : $checkOut->status,
            'last_reminder_sent_at' => now(),
        ])->save();

        return $checkOut->refresh();
    }
}
