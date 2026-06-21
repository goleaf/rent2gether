<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\BookingTimelineDate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingTimelineDateService
{
    /**
     * @return Collection<int, BookingTimelineDate>
     */
    public function buildForQuote(BookingQuote $quote): Collection
    {
        $quote->timelineDates()->delete();

        return collect($this->timelinePayload($quote))
            ->map(fn (array $payload): BookingTimelineDate => $quote->timelineDates()->create($payload))
            ->values();
    }

    /**
     * @return Collection<int, BookingTimelineDate>
     */
    public function copyToBooking(BookingQuote $quote, Booking $booking): Collection
    {
        return $quote->timelineDates()
            ->orderBy('scheduled_at')
            ->get()
            ->map(function (BookingTimelineDate $timelineDate) use ($booking): BookingTimelineDate {
                return BookingTimelineDate::query()->create([
                    'booking_id' => $booking->id,
                    'event_key' => $timelineDate->event_key,
                    'scheduled_at' => $timelineDate->scheduled_at,
                    'status' => 'pending',
                ]);
            })
            ->values();
    }

    /**
     * @return Collection<int, BookingTimelineDate>
     */
    public function createNotificationEventsForBooking(Booking $booking): Collection
    {
        return $this->rescheduleForBooking($booking);
    }

    /**
     * @return Collection<int, BookingTimelineDate>
     */
    public function rescheduleForBooking(Booking $booking): Collection
    {
        $booking->timelineDates()->delete();
        $checkIn = CarbonImmutable::parse($booking->check_in_date ?? $booking->check_in);
        $checkOut = CarbonImmutable::parse($booking->check_out_date ?? $booking->check_out);

        $events = [
            ['event_key' => 'guest_check_in_reminder', 'scheduled_at' => $checkIn->subDay()->setTime(18, 0)],
            ['event_key' => 'host_check_in_reminder', 'scheduled_at' => $checkIn->subDay()->setTime(18, 0)],
            ['event_key' => 'guest_check_out_reminder', 'scheduled_at' => $checkOut->subDay()->setTime(18, 0)],
            ['event_key' => 'host_check_out_reminder', 'scheduled_at' => $checkOut->subDay()->setTime(18, 0)],
        ];

        return collect($events)
            ->map(fn (array $event): BookingTimelineDate => $booking->timelineDates()->create([
                ...$event,
                'status' => 'pending',
            ]))
            ->values();
    }

    /**
     * @return list<array{event_key:string,scheduled_at:CarbonImmutable,status:string}>
     */
    private function timelinePayload(BookingQuote $quote): array
    {
        $checkIn = CarbonImmutable::instance($quote->check_in_date);
        $checkOut = CarbonImmutable::instance($quote->check_out_date);
        $paymentDeadline = $quote->payment_deadline_at ? CarbonImmutable::instance($quote->payment_deadline_at) : CarbonImmutable::now()->addMinutes(20);
        $freeCancellation = $quote->free_cancellation_until ? CarbonImmutable::instance($quote->free_cancellation_until) : $checkIn->subDays(5)->setTime(18, 0);
        $penaltyStarts = $quote->cancellation_penalty_starts_at ? CarbonImmutable::instance($quote->cancellation_penalty_starts_at) : $freeCancellation;

        return [
            ['event_key' => 'payment_deadline', 'scheduled_at' => $paymentDeadline, 'status' => 'pending'],
            ['event_key' => 'free_cancellation_until', 'scheduled_at' => $freeCancellation, 'status' => 'pending'],
            ['event_key' => 'cancellation_penalty_starts', 'scheduled_at' => $penaltyStarts, 'status' => 'pending'],
            ['event_key' => 'guest_check_in_reminder', 'scheduled_at' => $checkIn->subDay()->setTime(18, 0), 'status' => 'pending'],
            ['event_key' => 'host_check_in_reminder', 'scheduled_at' => $checkIn->subDay()->setTime(18, 0), 'status' => 'pending'],
            ['event_key' => 'guest_check_out_reminder', 'scheduled_at' => $checkOut->subDay()->setTime(18, 0), 'status' => 'pending'],
            ['event_key' => 'host_check_out_reminder', 'scheduled_at' => $checkOut->subDay()->setTime(18, 0), 'status' => 'pending'],
            ['event_key' => 'deposit_review_start', 'scheduled_at' => $checkOut->setTime(12, 0), 'status' => 'pending'],
            ['event_key' => 'host_payout_due', 'scheduled_at' => $checkOut->addDay()->setTime(12, 0), 'status' => 'pending'],
            ['event_key' => 'review_request', 'scheduled_at' => $checkOut->addDay()->setTime(18, 0), 'status' => 'pending'],
        ];
    }
}
