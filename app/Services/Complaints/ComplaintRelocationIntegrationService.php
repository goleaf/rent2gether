<?php

namespace App\Services\Complaints;

use App\Models\BookingRelocation;
use App\Models\ComplaintCase;
use App\Models\SleepingPlace;
use App\Services\Bookings\BookingRelocationNumberService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ComplaintRelocationIntegrationService
{
    public function __construct(
        private readonly BookingRelocationNumberService $numbers,
        private readonly ComplaintActionService $actions,
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
    ) {}

    /**
     * @return Collection<int, SleepingPlace>
     */
    public function suggestRelocationOptions(ComplaintCase $case): Collection
    {
        return SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'display_name', 'status', 'base_price_per_night', 'currency'])
            ->where('property_id', $case->property_id)
            ->where('id', '!=', $case->sleeping_place_id)
            ->where('status', 'active')
            ->limit(5)
            ->get();
    }

    public function createRelocationFromComplaint(ComplaintCase $case, ?SleepingPlace $place = null): BookingRelocation
    {
        $case->loadMissing('booking');
        $booking = $case->booking;
        $place ??= $this->suggestRelocationOptions($case)->first();
        $relocationDate = CarbonImmutable::parse(now()->toDateString())->startOfDay();
        $checkIn = CarbonImmutable::parse((string) ($booking->check_in_date ?: $booking->check_in))->startOfDay();
        $checkOut = CarbonImmutable::parse((string) ($booking->check_out_date ?: $booking->check_out))->startOfDay();

        $relocation = BookingRelocation::query()->create([
            'relocation_number' => $this->numbers->generate(),
            'original_booking_id' => $booking->id,
            'booking_stay_id' => $case->booking_stay_id,
            'guest_user_id' => $case->guest_user_id,
            'host_user_id' => $case->host_user_id,
            'current_property_id' => $case->property_id,
            'current_room_id' => $case->room_id,
            'current_sleeping_place_id' => $case->sleeping_place_id,
            'new_property_id' => $place?->property_id,
            'new_room_id' => $place?->room_id,
            'new_sleeping_place_id' => $place?->id,
            'source_type' => 'complaint_case',
            'source_id' => $case->id,
            'requested_by_user_id' => $case->reporter_user_id,
            'requested_by_type' => $case->submitted_by_type,
            'reason' => 'complaint',
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
            'guest_comment' => $case->description,
            'hold_dates' => true,
            'hold_expires_at' => $place ? now()->addMinutes(30) : null,
            'expires_at' => now()->addDay(),
        ]);

        $case->forceFill([
            'booking_relocation_id' => $relocation->id,
            'resolution_type' => 'relocation',
            'resolution_status' => 'in_progress',
        ])->save();

        $this->actions->createAction($case->fresh(), 'create_relocation', ['status' => 'completed', 'source_type' => 'booking_relocation', 'source_id' => $relocation->id, 'completed_at' => now()]);
        $this->statuses->transition($case->fresh(), 'relocation_created');
        $this->events->record($case->fresh(), 'relocation_created', ['booking_relocation_id' => $relocation->id]);

        return $relocation->fresh();
    }
}
