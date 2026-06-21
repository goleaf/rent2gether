<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingHostResponse;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BookingHostApprovalService
{
    public function __construct(
        private readonly BookingStatusService $statuses,
        private readonly BookingRequirementService $requirements,
        private readonly BookingCalendarIntegrationService $calendar,
        private readonly BookingPrivacyService $privacy,
        private readonly BookingNotificationIntegrationService $notifications,
    ) {}

    public function approve(User $host, Booking $booking, ?string $message = null): BookingHostResponse
    {
        $this->authorizeHost($host, $booking);

        $response = $booking->hostResponses()->create([
            'host_user_id' => $host->id,
            'response_type' => BookingHostResponse::TYPE_APPROVED,
            'message' => $message,
        ]);

        $this->requirements->markCompleted($booking, 'host_confirmation');

        $nextStatus = in_array($this->value($booking->payment_status), [PaymentStatus::Paid->value, PaymentStatus::Refunded->value], true)
            ? BookingStatus::Confirmed->value
            : BookingStatus::WaitingPayment->value;

        $this->statuses->transition($booking, $nextStatus, $host, [
            'event_key' => 'host_confirmed',
            'event_type' => 'host_action',
        ]);

        $this->notifications->notifyGuestConfirmed($booking->fresh());

        return $response->fresh();
    }

    public function reject(User $host, Booking $booking, string $reason): BookingHostResponse
    {
        $this->authorizeHost($host, $booking);

        $response = $booking->hostResponses()->create([
            'host_user_id' => $host->id,
            'response_type' => BookingHostResponse::TYPE_REJECTED,
            'rejection_reason' => $reason,
        ]);

        $booking->forceFill([
            'rejection_reason' => $reason,
            'rejected_by_user_id' => $host->id,
            'rejected_at' => now(),
        ])->save();

        $this->calendar->releaseBookingLocks($booking, 'rejected_by_host');
        $this->statuses->transition($booking, BookingStatus::RejectedByHost->value, $host, [
            'event_key' => 'host_rejected',
            'event_type' => 'host_action',
            'reason_key' => $reason,
        ]);
        $this->notifications->notifyGuestRejected($booking->fresh());

        return $response->fresh();
    }

    public function askGuestQuestion(User $host, Booking $booking, string $message): BookingHostResponse
    {
        $this->authorizeHost($host, $booking);

        $response = $booking->hostResponses()->create([
            'host_user_id' => $host->id,
            'response_type' => BookingHostResponse::TYPE_ASK_GUEST_QUESTION,
            'message' => $message,
        ]);

        $this->statuses->transition($booking, BookingStatus::WaitingGuestResponse->value, $host, [
            'event_type' => 'host_action',
        ]);

        return $response->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function proposeTimeChange(User $host, Booking $booking, array $data): BookingHostResponse
    {
        $this->authorizeHost($host, $booking);

        $response = $booking->hostResponses()->create([
            'host_user_id' => $host->id,
            'response_type' => BookingHostResponse::TYPE_PROPOSE_TIME_CHANGE,
            'message' => $data['message'] ?? null,
            'proposed_check_in_time' => $data['proposed_check_in_time'] ?? null,
            'proposed_check_out_time' => $data['proposed_check_out_time'] ?? null,
        ]);

        $this->statuses->transition($booking, BookingStatus::WaitingGuestResponse->value, $host, [
            'event_type' => 'host_action',
        ]);

        return $response->fresh();
    }

    private function authorizeHost(User $host, Booking $booking): void
    {
        if (! $this->privacy->canHostRespond($host, $booking)) {
            throw ValidationException::withMessages([
                'booking' => __('bookings.validation.host_cannot_respond'),
            ]);
        }
    }

    private function value(mixed $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
    }
}
