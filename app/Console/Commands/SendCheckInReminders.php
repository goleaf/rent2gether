<?php

namespace App\Console\Commands;

use App\Services\CheckIn\BookingCheckInReminderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('check-in:send-reminders')]
#[Description('Send guest and host check-in reminders due tomorrow')]
class SendCheckInReminders extends Command
{
    public function handle(BookingCheckInReminderService $reminders): int
    {
        $sent = $reminders->sendDueRemindersForAll();

        $this->info(__('check_in.console.reminders_sent', ['count' => $sent]));

        return self::SUCCESS;
    }
}
