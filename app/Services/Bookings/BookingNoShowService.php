<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingNoShowService
{
    public function __construct(
        private readonly BookingNoShowNumberService $numbers,
        private readonly BookingNoShowPolicySnapshotService $snapshots,
        private readonly BookingNoShowDetectionService $detection,
        private readonly BookingNoShowContactService $contacts,
        private readonly BookingNoShowEventService $events,
        private readonly BookingNoShowNotificationService $notifications,
    ) {}

    public function startWatchForBooking(Booking $booking): BookingNoShow
    {
        if (! $this->detection->shouldStartNoShowWatch($booking)) {
            throw ValidationException::withMessages([
                'booking' => __('no_show.validation.cannot_start'),
            ]);
        }

        return $this->createOrLoadCase($booking, 'watching', []);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromHostReport(User $host, Booking $booking, array $data): BookingNoShow
    {
        if ((int) $booking->host_user_id !== (int) $host->id) {
            throw ValidationException::withMessages([
                'booking' => __('no_show.validation.not_host_booking'),
            ]);
        }

        return DB::transaction(function () use ($host, $booking, $data): BookingNoShow {
            $noShow = $this->createOrLoadCase($booking, 'host_reported', [
                'reason_key' => $data['reason_key'] ?? 'host_reported_guest_absent',
                'host_comment' => $data['host_comment'] ?? $data['comment'] ?? null,
                'host_reported_at' => now(),
                'host_marked_no_show' => true,
            ]);

            $this->events->record($noShow, 'host_reported_no_show', ['user_id' => $host->id]);
            $this->contacts->sendGuestResponseRequest($noShow);
            $this->notifications->notifyGuestNoShowReported($noShow->fresh());

            return $noShow->fresh(['contactAttempts']);
        });
    }

    public function getForBooking(Booking $booking): ?BookingNoShow
    {
        return BookingNoShow::query()
            ->where('booking_id', $booking->id)
            ->latest('id')
            ->first();
    }

    public function cancelNoShow(BookingNoShow $noShow, string $reason): BookingNoShow
    {
        $noShow->forceFill([
            'status' => 'cancelled',
            'decision_key' => null,
            'future_support_comment' => $reason,
            'closed_at' => now(),
        ])->save();

        $this->events->record($noShow->fresh(), 'no_show_closed', ['reason' => $reason]);

        return $noShow->fresh();
    }

    public function closeNoShow(BookingNoShow $noShow): BookingNoShow
    {
        $noShow->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
        ])->save();

        $this->events->record($noShow->fresh(), 'no_show_closed');

        return $noShow->fresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createOrLoadCase(Booking $booking, string $status, array $attributes): BookingNoShow
    {
        $existing = $this->getForBooking($booking);

        if ($existing instanceof BookingNoShow && ! in_array($existing->status, ['closed', 'cancelled'], true)) {
            $existing->forceFill([
                ...$attributes,
                'status' => $status,
            ])->save();

            return $existing->fresh();
        }

        $snapshot = $this->snapshots->getForBooking($booking);
        $booking->loadMissing('checkIn');
        $waitingUntil = now()->addMinutes($snapshot->waiting_period_minutes);

        $noShow = BookingNoShow::query()->create([
            'no_show_number' => $this->numbers->generate(),
            'booking_id' => $booking->id,
            'booking_check_in_id' => $booking->checkIn?->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'status' => $status,
            'reason_key' => $attributes['reason_key'] ?? 'guest_did_not_arrive',
            'check_in_date' => $booking->check_in_date ?: $booking->check_in,
            'planned_check_in_time' => $this->timeString($booking->arrival_time ?: $booking->check_in_time),
            'check_in_window' => $booking->checkIn?->check_in_window,
            'no_show_started_at' => now(),
            'waiting_period_minutes' => $snapshot->waiting_period_minutes,
            'waiting_until' => $waitingUntil,
            'currency' => $booking->currency ?: 'EUR',
            ...$attributes,
        ]);

        $this->events->record($noShow, 'no_show_watch_started');
        $this->events->record($noShow, 'waiting_period_started', ['waiting_until' => $waitingUntil->toISOString()]);

        return $noShow->fresh();
    }

    private function timeString(mixed $time): ?string
    {
        return is_object($time) && method_exists($time, 'format') ? $time->format('H:i') : $time;
    }
}
