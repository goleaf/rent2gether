<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingCheckInProblem;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostRepresentative;
use App\Models\HostUnresponsivePolicySnapshot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HostUnresponsiveService
{
    public function __construct(
        private readonly HostUnresponsiveNumberService $numbers,
        private readonly HostUnresponsivePolicySnapshotService $snapshots,
        private readonly HostUnresponsiveDetectionService $detection,
        private readonly HostUnresponsiveContactService $contacts,
        private readonly HostUnresponsiveInstructionService $instructions,
        private readonly HostUnresponsiveEventService $events,
        private readonly HostUnresponsiveNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromGuestReport(User $guest, Booking $booking, array $data): BookingHostUnresponsiveCase
    {
        $booking = $this->bookingForCase($booking);

        if (! $this->detection->canReport($guest, $booking)) {
            throw ValidationException::withMessages([
                'booking' => __('host_unresponsive.validation.cannot_report'),
            ]);
        }

        return DB::transaction(function () use ($guest, $booking, $data): BookingHostUnresponsiveCase {
            $case = $this->createOrLoadCase($booking, [
                'case_type' => $data['case_type'] ?? 'check_in_no_response',
                'reason_key' => $data['reason_key'] ?? 'host_not_answering_messages',
                'guest_comment' => $data['guest_comment'] ?? $data['message'] ?? null,
                'guest_marked_arrived' => (bool) ($data['guest_marked_arrived'] ?? false),
                'guest_at_address' => (bool) ($data['guest_at_address'] ?? false),
                'guest_waiting_outside' => (bool) ($data['guest_waiting_outside'] ?? false),
                'guest_feels_unsafe' => (bool) ($data['guest_feels_unsafe'] ?? false),
                'actual_guest_arrival_at' => ($data['guest_marked_arrived'] ?? false) ? now() : null,
            ]);

            $case->guestActions()->create([
                'booking_id' => $case->booking_id,
                'guest_user_id' => $guest->id,
                'action_type' => 'reported_host_not_answering',
                'message' => $data['message'] ?? null,
                'guest_location_note' => $data['guest_location_note'] ?? null,
            ]);

            $this->events->record($case, 'host_unresponsive_reported', ['user_id' => $guest->id]);
            $this->contacts->sendUrgentAlert($case);
            $case = $case->fresh(['booking.hostUnresponsivePolicySnapshot']);

            if ((bool) $case->booking?->hostUnresponsivePolicySnapshot?->auto_show_instructions_if_allowed) {
                $this->instructions->showAllowedInstructions($guest, $case);
            }

            app(HostUnresponsiveCheckInIntegrationService::class)->markCheckInHostUnresponsive($case->fresh());
            app(HostUnresponsiveNoShowIntegrationService::class)->blockNoShowWhileActive($case->fresh());
            $this->notifications->notifyGuestHostContactAttempted($case->fresh());

            return $case->fresh(['contactAttempts', 'guestActions']);
        });
    }

    public function createFromCheckInProblem(BookingCheckInProblem $problem): BookingHostUnresponsiveCase
    {
        $problem->loadMissing('guest', 'booking');

        if (! $problem->guest instanceof User || ! $problem->booking instanceof Booking) {
            throw ValidationException::withMessages([
                'booking' => __('host_unresponsive.validation.booking_missing_place'),
            ]);
        }

        $case = $this->createFromGuestReport($problem->guest, $problem->booking, [
            'case_type' => 'check_in_no_response',
            'reason_key' => $problem->problem_type === 'representative_not_answering'
                ? 'representative_not_answering'
                : 'host_not_answering_messages',
            'message' => $problem->description,
            'guest_wants_cancellation' => $problem->guest_wants_cancellation,
            'guest_wants_refund' => $problem->guest_wants_refund,
            'guest_wants_relocation' => $problem->guest_wants_relocation,
            'guest_marked_arrived' => true,
        ]);

        $problem->forceFill([
            'source_created_host_unresponsive_case_id' => $case->id,
            'status' => 'host_notified',
        ])->save();

        $case->forceFill(['check_in_problem_id' => $problem->id])->save();

        return $case->fresh();
    }

    public function getForBooking(Booking $booking): ?BookingHostUnresponsiveCase
    {
        return BookingHostUnresponsiveCase::query()
            ->where('booking_id', $booking->id)
            ->latest('id')
            ->first();
    }

    public function markResolved(BookingHostUnresponsiveCase $case): BookingHostUnresponsiveCase
    {
        return app(HostUnresponsiveStatusService::class)->transition($case, 'resolved', null, [
            'reason_key' => 'host_unresponsive.events.case_resolved',
        ]);
    }

    public function markUnresolved(BookingHostUnresponsiveCase $case): BookingHostUnresponsiveCase
    {
        return app(HostUnresponsiveStatusService::class)->transition($case, 'unresolved', null, [
            'reason_key' => 'host_unresponsive.events.deadline_expired',
        ]);
    }

    public function closeCase(BookingHostUnresponsiveCase $case): BookingHostUnresponsiveCase
    {
        $closed = app(HostUnresponsiveStatusService::class)->transition($case, 'closed');
        $this->events->record($closed, 'case_closed');

        return $closed;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createOrLoadCase(Booking $booking, array $attributes): BookingHostUnresponsiveCase
    {
        $existing = $this->getForBooking($booking);

        if ($existing instanceof BookingHostUnresponsiveCase && ! in_array($existing->status, ['closed', 'cancelled'], true)) {
            $existing->forceFill([
                ...$attributes,
                'status' => 'reported',
            ])->save();

            return $existing->fresh();
        }

        $snapshot = $this->snapshots->getForBooking($booking);
        $representative = $this->representativeForBooking($booking);
        $deadlineMinutes = $this->deadlineMinutes($snapshot, (string) ($attributes['case_type'] ?? 'check_in_no_response'), (bool) ($attributes['guest_feels_unsafe'] ?? false), (bool) ($attributes['guest_waiting_outside'] ?? false));
        $deadlineAt = $deadlineMinutes > 0 ? now()->addMinutes($deadlineMinutes) : now();

        return BookingHostUnresponsiveCase::query()->create([
            'case_number' => $this->numbers->generate(),
            'booking_id' => $booking->id,
            'booking_check_in_id' => $booking->checkIn?->id,
            'booking_stay_id' => $booking->stay?->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'host_representative_id' => $representative?->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'status' => 'reported',
            'check_in_date' => $booking->check_in_date ?: $booking->check_in,
            'planned_check_in_time' => $this->timeString($booking->arrival_time ?: $booking->check_in_time),
            'check_in_window' => $booking->checkIn?->check_in_window,
            'response_deadline_minutes' => $deadlineMinutes,
            'response_deadline_at' => $deadlineAt,
            'guest_wants_help' => true,
            'currency' => $booking->currency ?: 'EUR',
            ...$attributes,
        ])->fresh();
    }

    private function representativeForBooking(Booking $booking): ?HostRepresentative
    {
        return HostRepresentative::query()
            ->where('host_user_id', $booking->host_user_id)
            ->where('active', true)
            ->where('can_help_with_check_in', true)
            ->orderByDesc('can_be_contacted_by_guest')
            ->latest('id')
            ->first();
    }

    private function deadlineMinutes(HostUnresponsivePolicySnapshot $snapshot, string $caseType, bool $unsafe, bool $waitingOutside): int
    {
        if ($unsafe) {
            return (int) $snapshot->urgent_response_minutes;
        }

        if ($waitingOutside) {
            return (int) $snapshot->guest_waiting_outside_response_minutes;
        }

        return match ($caseType) {
            'pre_check_in_no_response' => (int) $snapshot->pre_check_in_response_minutes,
            'night_entry_no_response' => (int) $snapshot->night_entry_response_minutes,
            'during_stay_urgent_no_response' => (int) $snapshot->urgent_response_minutes,
            default => (int) $snapshot->check_in_response_minutes,
        };
    }

    private function bookingForCase(Booking $booking): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'booking_number',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in',
                'check_out',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'arrival_time',
                'currency',
                'check_in_instruction_available',
                'total',
                'total_amount',
                'total_payable',
                'deposit_amount',
                'cleaning_fee_amount',
                'service_fee_amount',
            ])
            ->with(['checkIn', 'stay', 'sleepingPlace:id,user_id,property_id', 'property:id,user_id,host_user_id'])
            ->findOrFail($booking->id);
    }

    private function timeString(mixed $time): ?string
    {
        return is_object($time) && method_exists($time, 'format') ? $time->format('H:i') : $time;
    }
}
