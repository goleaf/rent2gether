<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use Illuminate\Support\Collection;

class CheckInNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function scheduleCheckInReminders(Booking $booking): Collection
    {
        $booking->loadMissing('guest', 'host');

        return collect([$booking->guest, $booking->host])
            ->filter()
            ->map(fn ($user) => app(NotificationReminderService::class)->scheduleReminder($user, 'check_in_soon', now()->addDay(), ['booking' => $booking]));
    }

    public function notifyCheckInSoon(mixed $checkIn): void
    {
        $this->notifyGuest($this->bookingFrom($checkIn), 'check_in_soon');
    }

    public function notifyCheckInToday(mixed $checkIn): void
    {
        $this->notifyGuest($this->bookingFrom($checkIn), 'check_in_today');
        $this->notifyHost($this->bookingFrom($checkIn), 'check_in_today');
    }

    public function notifyInstructionAvailable(mixed $checkIn): void
    {
        $this->notifyGuest($this->bookingFrom($checkIn), 'check_in_instruction_available');
    }

    public function notifyGuestArrived(mixed $checkIn): void
    {
        $this->notifyHost($this->bookingFrom($checkIn), 'guest_arrived', ['priority' => 'urgent']);
    }

    public function notifyCheckInProblem(mixed $problem): void
    {
        $this->notifyHost($this->bookingFrom($problem), 'check_in_problem', ['priority' => 'critical']);
    }
}
