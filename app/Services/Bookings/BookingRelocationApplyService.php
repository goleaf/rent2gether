<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingRelocation;
use App\Models\BookingStay;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\RoomCurrentOccupancySnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingRelocationApplyService
{
    public function __construct(
        private readonly BookingRelocationAvailabilityService $availability,
        private readonly BookingRelocationConsentService $consents,
        private readonly BookingRelocationHoldService $holds,
        private readonly BookingRelocationCalendarService $calendar,
        private readonly BookingRelocationInventoryService $inventory,
        private readonly BookingRelocationEventService $events,
        private readonly BookingRelocationNotificationService $notifications,
    ) {}

    public function apply(BookingRelocation $relocation): BookingRelocation
    {
        return DB::transaction(function () use ($relocation): BookingRelocation {
            $relocation->refresh()->loadMissing(['originalBooking', 'newSleepingPlace', 'bookingStay']);

            if (! $this->canApply($relocation)) {
                throw ValidationException::withMessages([
                    'relocation' => __('booking_relocations.validation.guest_consent_required'),
                ]);
            }

            $availability = $this->availability->checkNewPlace($relocation);
            if (! $availability['available']) {
                $relocation->forceFill(['status' => 'failed'])->save();

                throw ValidationException::withMessages([
                    'relocation' => __('booking_relocations.validation.new_sleeping_place_unavailable'),
                ]);
            }

            $relocation->forceFill(['status' => 'applying'])->save();
            $newBooking = $this->createNewBookingSegment($relocation);
            $relocation->forceFill(['new_booking_id' => $newBooking->id])->save();

            $this->shortenOriginalBooking($relocation->refresh());
            $this->updateCurrentStay($relocation->refresh());
            $this->updateCalendarLocks($relocation->refresh());
            $this->rescheduleCheckout($relocation->refresh());
            $this->rescheduleDepositReview($relocation->refresh());
            $this->rescheduleReviewRequest($relocation->refresh());
            $this->linkMessagesAndNotifications($relocation->refresh());
            $this->updateOccupancySnapshots($relocation->refresh());
            $this->inventory->prepareInventoryTransfer($relocation->refresh());

            $relocation->forceFill([
                'status' => 'applied',
                'applied_at' => now(),
            ])->save();

            $this->events->record($relocation->refresh(), 'relocation_applied');
            $this->notifications->notifyRelocationApplied($relocation->refresh());

            return $relocation->refresh();
        });
    }

    public function createNewBookingSegment(BookingRelocation $relocation): Booking
    {
        $original = $relocation->originalBooking()->firstOrFail();
        $nights = $this->nights($relocation->new_period_check_in_date, $relocation->new_period_check_out_date);

        $booking = Booking::query()->create([
            'reference' => null,
            'guest_user_id' => $original->guest_user_id,
            'guest_id' => $original->guest_user_id,
            'host_user_id' => $original->host_user_id,
            'host_id' => $original->host_user_id,
            'property_id' => $relocation->new_property_id,
            'room_id' => $relocation->new_room_id,
            'sleeping_place_id' => $relocation->new_sleeping_place_id,
            'relocation_from_booking_id' => $original->id,
            'booking_type' => 'relocation',
            'check_in' => $relocation->new_period_check_in_date,
            'check_in_date' => $relocation->new_period_check_in_date,
            'check_out' => $relocation->new_period_check_out_date,
            'check_out_date' => $relocation->new_period_check_out_date,
            'check_in_time' => $original->check_in_time,
            'check_out_time' => $original->check_out_time,
            'guests_count' => $original->guests_count,
            'nights' => $nights,
            'nights_count' => $nights,
            'chargeable_days_count' => $nights,
            'calendar_days_count' => $nights + 1,
            'calendar_presence_days_count' => $nights + 1,
            'price_per_night' => $relocation->newSleepingPlace?->base_price_per_night ?: $original->price_per_night,
            'subtotal' => $relocation->new_remaining_value_amount,
            'subtotal_amount' => $relocation->new_remaining_value_amount,
            'accommodation_amount' => $relocation->new_remaining_value_amount,
            'discount_amount' => 0,
            'cleaning_fee' => 0,
            'cleaning_fee_amount' => 0,
            'deposit' => $relocation->additional_deposit_amount,
            'deposit_amount' => $relocation->additional_deposit_amount,
            'service_fee' => $relocation->service_fee_difference_amount,
            'service_fee_amount' => $relocation->service_fee_difference_amount,
            'total' => (float) $relocation->new_remaining_value_amount + (float) $relocation->service_fee_difference_amount + (float) $relocation->additional_deposit_amount,
            'total_amount' => (float) $relocation->new_remaining_value_amount + (float) $relocation->service_fee_difference_amount + (float) $relocation->additional_deposit_amount,
            'total_payable' => $relocation->additional_payment_amount,
            'host_payout_amount' => $relocation->new_remaining_value_amount,
            'refundable_amount' => $relocation->additional_deposit_amount,
            'non_refundable_amount' => $relocation->new_remaining_value_amount,
            'currency' => $relocation->currency,
            'status' => 'in_progress',
            'payment_status' => $relocation->requires_payment ? $relocation->payment_status : $original->payment_status,
            'cancellation_policy' => $original->cancellation_policy,
            'guest_message' => $original->guest_message,
            'host_response' => $original->host_response,
            'has_dispute' => $original->has_dispute,
            'has_complaint' => $original->has_complaint,
        ]);

        $this->events->record($relocation, 'new_booking_segment_created', ['new_booking_id' => $booking->id]);

        return $booking->refresh();
    }

    public function shortenOriginalBooking(BookingRelocation $relocation): Booking
    {
        $booking = $relocation->originalBooking()->firstOrFail();
        $nights = $this->nights($booking->check_in_date, $relocation->relocation_date);

        $booking->forceFill([
            'check_out' => $relocation->relocation_date,
            'check_out_date' => $relocation->relocation_date,
            'nights' => $nights,
            'nights_count' => $nights,
            'chargeable_days_count' => $nights,
            'calendar_days_count' => $nights + 1,
            'calendar_presence_days_count' => $nights + 1,
        ])->save();

        $this->events->record($relocation, 'old_booking_shortened');

        return $booking->refresh();
    }

    public function updateCurrentStay(BookingRelocation $relocation): void
    {
        $stay = $relocation->bookingStay()->first();

        if (! $stay instanceof BookingStay) {
            return;
        }

        $stay->forceFill([
            'booking_id' => $relocation->new_booking_id,
            'property_id' => $relocation->new_property_id,
            'room_id' => $relocation->new_room_id,
            'sleeping_place_id' => $relocation->new_sleeping_place_id,
            'planned_check_out_date' => $relocation->new_period_check_out_date,
            'relocation_requested' => false,
            'status' => 'active',
        ])->save();
    }

    public function updateCalendarLocks(BookingRelocation $relocation): void
    {
        $this->holds->convertHoldToBookingLocks($relocation);
        $this->calendar->releaseOldPlaceLocksAfterMove($relocation);
        $this->calendar->keepOldPlaceBlockedForCleaningOrInspection($relocation);
        $this->events->record($relocation, 'new_place_locks_created');
        $this->events->record($relocation, 'old_place_released_for_inspection');
    }

    public function rescheduleCheckout(BookingRelocation $relocation): void
    {
        BookingCheckOut::query()
            ->where('booking_id', $relocation->original_booking_id)
            ->update([
                'booking_id' => $relocation->new_booking_id,
                'booking_stay_id' => $relocation->booking_stay_id,
                'property_id' => $relocation->new_property_id,
                'room_id' => $relocation->new_room_id,
                'sleeping_place_id' => $relocation->new_sleeping_place_id,
                'updated_at' => now(),
            ]);
    }

    public function rescheduleDepositReview(BookingRelocation $relocation): void
    {
        $this->events->record($relocation, 'old_place_cleaning_created');
    }

    public function rescheduleReviewRequest(BookingRelocation $relocation): void
    {
        $this->events->record($relocation, 'relocation_scheduled');
    }

    public function linkMessagesAndNotifications(BookingRelocation $relocation): void
    {
        $this->events->record($relocation, 'inventory_transfer_started');
    }

    public function updateOccupancySnapshots(BookingRelocation $relocation): void
    {
        foreach (array_unique([$relocation->current_room_id, $relocation->new_room_id]) as $roomId) {
            if (! $roomId) {
                continue;
            }

            RoomCurrentOccupancySnapshot::query()->updateOrCreate([
                'room_id' => $roomId,
            ], [
                'property_id' => $roomId === $relocation->current_room_id ? $relocation->current_property_id : $relocation->new_property_id,
                'host_user_id' => $relocation->host_user_id,
                'last_recalculated_at' => now(),
            ]);
        }

        foreach (array_unique([$relocation->current_property_id, $relocation->new_property_id]) as $propertyId) {
            if (! $propertyId) {
                continue;
            }

            PropertyCurrentOccupancySnapshot::query()->updateOrCreate([
                'property_id' => $propertyId,
            ], [
                'host_user_id' => $relocation->host_user_id,
                'last_recalculated_at' => now(),
            ]);
        }
    }

    private function canApply(BookingRelocation $relocation): bool
    {
        if (! $relocation->new_sleeping_place_id || ! $this->consents->allRequiredConsentsGiven($relocation)) {
            return false;
        }

        if ($relocation->requires_payment) {
            return $relocation->payment_status === 'paid';
        }

        return true;
    }

    private function nights(mixed $checkIn, mixed $checkOut): int
    {
        $start = CarbonImmutable::parse($checkIn)->startOfDay();
        $end = CarbonImmutable::parse($checkOut)->startOfDay();

        return max(0, (int) $start->diffInDays($end));
    }
}
