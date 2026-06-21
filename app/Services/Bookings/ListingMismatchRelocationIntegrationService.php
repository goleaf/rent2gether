<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;
use App\Models\BookingRelocation;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ListingMismatchRelocationIntegrationService
{
    public function __construct(
        private readonly BookingRelocationNumberService $numbers,
        private readonly ListingMismatchEventService $events,
        private readonly ListingMismatchNotificationService $notifications,
    ) {}

    /**
     * @return Collection<int, SleepingPlace>
     */
    public function suggestRelocationOptions(BookingListingMismatchReport $report): Collection
    {
        return SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'display_name', 'status', 'base_price_per_night', 'currency'])
            ->where('property_id', $report->property_id)
            ->where('id', '!=', $report->sleeping_place_id)
            ->where('status', 'active')
            ->limit(5)
            ->get();
    }

    public function createRelocationFromMismatch(BookingListingMismatchReport $report, ?SleepingPlace $place = null): BookingRelocation
    {
        $report->loadMissing('booking', 'stay');
        $booking = $report->booking;
        $place ??= $this->suggestRelocationOptions($report)->first();
        $relocationDate = CarbonImmutable::parse(now()->toDateString())->startOfDay();
        $checkIn = CarbonImmutable::parse((string) ($booking->check_in_date ?: $booking->check_in))->startOfDay();
        $checkOut = CarbonImmutable::parse((string) ($booking->check_out_date ?: $booking->check_out))->startOfDay();

        $relocation = BookingRelocation::query()->create([
            'relocation_number' => $this->numbers->generate(),
            'original_booking_id' => $booking->id,
            'new_booking_id' => null,
            'booking_stay_id' => $report->booking_stay_id,
            'guest_user_id' => $report->guest_user_id,
            'host_user_id' => $report->host_user_id,
            'current_property_id' => $report->property_id,
            'current_room_id' => $report->room_id,
            'current_sleeping_place_id' => $report->sleeping_place_id,
            'new_property_id' => $place?->property_id,
            'new_room_id' => $place?->room_id,
            'new_sleeping_place_id' => $place?->id,
            'source_type' => 'listing_mismatch_report',
            'source_id' => $report->id,
            'requested_by_user_id' => $report->host_user_id,
            'requested_by_type' => 'host',
            'reason' => 'listing_mismatch',
            'status' => $place ? 'waiting_guest_consent' : 'options_searching',
            'relocation_date' => $relocationDate->toDateString(),
            'check_in_date' => $relocationDate->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'original_check_in_date' => $checkIn->toDateString(),
            'original_check_out_date' => $checkOut->toDateString(),
            'old_period_check_in_date' => $checkIn->toDateString(),
            'old_period_check_out_date' => $relocationDate->toDateString(),
            'new_period_check_in_date' => $relocationDate->toDateString(),
            'new_period_check_out_date' => $checkOut->toDateString(),
            'currency' => $booking->currency ?: $place?->currency ?: 'EUR',
            'price_difference_payer' => 'host',
            'requires_guest_consent' => $place !== null,
            'requires_host_consent' => false,
            'requires_payment' => false,
            'payment_status' => 'not_required',
            'requires_refund' => false,
            'guest_comment' => $report->guest_description,
            'host_comment' => $report->host_response,
            'hold_dates' => true,
            'hold_expires_at' => $place ? now()->addMinutes(30) : null,
            'expires_at' => now()->addDay(),
        ]);

        $report->forceFill([
            'booking_relocation_id' => $relocation->id,
            'status' => 'relocation_started',
            'resolution_type' => 'relocation',
            'resolution_status' => 'in_progress',
        ])->save();

        $this->events->record($report->fresh(), 'relocation_created', ['booking_relocation_id' => $relocation->id]);
        $this->notifications->notifyRelocationCreated($report->fresh());

        return $relocation->fresh();
    }
}
