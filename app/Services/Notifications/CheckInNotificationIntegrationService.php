<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\NotificationReminder;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CheckInNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    /**
     * @return Collection<int, NotificationReminder>
     */
    public function scheduleCheckInReminders(Booking $booking): Collection
    {
        $booking->loadMissing(['guest.setting', 'host.setting']);
        $scheduledFor = $this->reminderScheduledFor($booking);

        return collect([
            ['user' => $booking->guest, 'recipient_type' => 'guest', 'action_url' => $this->guestCheckInUrl($booking->guest, $booking)],
            ['user' => $booking->host, 'recipient_type' => 'host', 'action_url' => $this->hostBookingUrl($booking->host, $booking)],
        ])
            ->filter(fn (array $recipient): bool => $recipient['user'] instanceof User)
            ->map(fn (array $recipient): NotificationReminder => $this->scheduleUniqueReminder(
                user: $recipient['user'],
                booking: $booking,
                recipientType: $recipient['recipient_type'],
                scheduledFor: $scheduledFor,
                actionUrl: $recipient['action_url'],
            ))
            ->values();
    }

    public function notifyCheckInSoon(mixed $checkIn): void
    {
        $this->notifyGuestCheckInSoon($checkIn);
    }

    public function notifyGuestCheckInSoon(mixed $checkIn): void
    {
        $booking = $this->bookingFrom($checkIn);

        $this->notifyGuest($booking, 'check_in_soon', [
            'priority' => 'high',
            'action_url' => $this->guestCheckInUrl($booking?->guest, $booking),
        ]);
    }

    public function notifyHostCheckInSoon(mixed $checkIn): void
    {
        $booking = $this->bookingFrom($checkIn);

        $this->notifyHost($booking, 'check_in_soon', [
            'priority' => 'high',
            'action_url' => $this->hostBookingUrl($booking?->host, $booking),
        ]);
    }

    public function notifyCheckInToday(mixed $checkIn): void
    {
        $booking = $this->bookingFrom($checkIn);

        $this->notifyGuest($booking, 'check_in_today', [
            'priority' => 'urgent',
            'action_url' => $this->guestCheckInUrl($booking?->guest, $booking),
        ]);
        $this->notifyHost($booking, 'check_in_today', [
            'priority' => 'urgent',
            'action_url' => $this->hostBookingUrl($booking?->host, $booking),
        ]);
    }

    public function notifyInstructionAvailable(mixed $checkIn): void
    {
        $booking = $this->bookingFrom($checkIn);

        $this->notifyGuest($booking, 'check_in_instruction_available', [
            'priority' => 'high',
            'action_url' => $this->guestCheckInUrl($booking?->guest, $booking),
        ]);
    }

    public function notifyGuestArrived(mixed $checkIn): void
    {
        $booking = $this->bookingFrom($checkIn);

        $this->notifyHost($booking, 'guest_arrived', [
            'priority' => 'urgent',
            'action_url' => $this->hostBookingUrl($booking?->host, $booking),
        ]);
    }

    public function notifyHostGuestConfirmed(mixed $checkIn): void
    {
        $booking = $this->bookingFrom($checkIn);

        $this->notifyHost($booking, 'guest_confirmed_check_in', [
            'priority' => 'high',
            'action_url' => $this->hostBookingUrl($booking?->host, $booking),
        ]);
    }

    public function notifyGuestHostConfirmed(mixed $checkIn): void
    {
        $booking = $this->bookingFrom($checkIn);

        $this->notifyGuest($booking, 'host_confirmed_check_in', [
            'priority' => 'high',
            'action_url' => $this->guestCheckInUrl($booking?->guest, $booking),
        ]);
    }

    public function notifyCheckInProblem(mixed $problem): void
    {
        $booking = $this->bookingFrom($problem);

        $this->notifyHost($booking, 'check_in_problem', [
            'priority' => 'critical',
            'action_url' => $this->hostBookingUrl($booking?->host, $booking),
        ]);
    }

    private function scheduleUniqueReminder(
        User $user,
        Booking $booking,
        string $recipientType,
        CarbonInterface $scheduledFor,
        ?string $actionUrl,
    ): NotificationReminder {
        $existing = NotificationReminder::query()
            ->where('booking_id', $booking->id)
            ->where('user_id', $user->id)
            ->where('recipient_type', $recipientType)
            ->where('reminder_type', 'check_in_soon')
            ->whereIn('status', ['scheduled', 'due', 'processed'])
            ->first();

        if ($existing instanceof NotificationReminder) {
            return $existing;
        }

        return app(NotificationReminderService::class)->scheduleReminder($user, 'check_in_soon', $scheduledFor, [
            'booking' => $booking,
            'recipient_type' => $recipientType,
            'priority' => 'high',
            'action_url' => $actionUrl,
        ]);
    }

    private function reminderScheduledFor(Booking $booking): CarbonImmutable
    {
        $date = $booking->check_in_date ?: $booking->check_in ?: now()->addDay()->toDateString();
        $time = $this->timeString($booking->arrival_time ?: $booking->check_in_time) ?: '09:00';

        return CarbonImmutable::parse($this->dateString($date).' '.$time)->subDay();
    }

    private function guestCheckInUrl(?User $user, ?Booking $booking): ?string
    {
        if (! $user instanceof User || ! $booking instanceof Booking) {
            return null;
        }

        return route('guest.bookings.check-in', [
            'locale' => $this->localeFor($user),
            'booking' => $booking,
        ]);
    }

    private function hostBookingUrl(?User $user, ?Booking $booking): ?string
    {
        if (! $user instanceof User || ! $booking instanceof Booking) {
            return null;
        }

        return route('host.bookings.manage', [
            'locale' => $this->localeFor($user),
            'booking' => $booking,
        ]);
    }

    private function localeFor(User $user): string
    {
        $locale = $user->setting?->locale ?: $user->preferred_locale ?: app()->getLocale();

        return in_array($locale, config('localization.supported_locales'), true)
            ? $locale
            : (string) config('app.fallback_locale', 'en');
    }

    private function dateString(mixed $date): string
    {
        return is_object($date) && method_exists($date, 'format') ? $date->format('Y-m-d') : (string) $date;
    }

    private function timeString(mixed $time): ?string
    {
        if (is_object($time) && method_exists($time, 'format')) {
            return $time->format('H:i');
        }

        return filled($time) ? (string) $time : null;
    }
}
