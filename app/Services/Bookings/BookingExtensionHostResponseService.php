<?php

namespace App\Services\Bookings;

use App\Models\BookingExtension;
use App\Models\BookingExtensionHostResponse;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookingExtensionHostResponseService
{
    public function __construct(
        private readonly BookingExtensionPrivacyService $privacy,
        private readonly BookingExtensionHoldService $holds,
        private readonly BookingExtensionEventService $events,
        private readonly BookingExtensionNotificationService $notifications,
    ) {}

    public function approve(User $host, BookingExtension $extension, ?string $message = null): BookingExtensionHostResponse
    {
        $this->authorize($host, $extension);

        $response = $this->createResponse($host, $extension, 'approve', [
            'message' => $message,
        ]);

        $extension->forceFill([
            'status' => $extension->requires_payment ? 'approved_waiting_payment' : 'approved',
            'payment_status' => $extension->requires_payment ? 'waiting_payment' : 'not_required',
            'host_response' => $message,
            'host_reply' => $message,
            'approved_at' => now(),
        ])->save();

        if ($extension->requires_payment) {
            app(BookingExtensionPaymentService::class)->createPaymentIfRequired($extension->refresh());
            $this->notifications->notifyGuestPaymentRequired($extension->refresh());
            $this->events->record($extension, 'payment_required', ['user_id' => $host->id]);
        } else {
            $this->notifications->notifyGuestExtensionApproved($extension->refresh());
        }

        $this->events->record($extension->refresh(), 'host_approved', ['user_id' => $host->id]);

        return $response;
    }

    public function reject(User $host, BookingExtension $extension, string $reason): BookingExtensionHostResponse
    {
        $this->authorize($host, $extension);

        $response = $this->createResponse($host, $extension, 'reject', [
            'rejection_reason' => $reason,
        ]);

        $extension->forceFill([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reject_reason' => $reason,
            'rejected_at' => now(),
            'declined_at' => now(),
        ])->save();

        $this->holds->releaseHold($extension->refresh(), 'rejected');
        $this->events->record($extension, 'host_rejected', ['user_id' => $host->id, 'reason' => $reason]);
        $this->notifications->notifyGuestExtensionRejected($extension->refresh());

        return $response;
    }

    public function askQuestion(User $host, BookingExtension $extension, string $message): BookingExtensionHostResponse
    {
        $this->authorize($host, $extension);

        $response = $this->createResponse($host, $extension, 'ask_question', [
            'message' => $message,
        ]);

        $extension->forceFill([
            'status' => 'waiting_guest_response',
            'host_response' => $message,
            'host_reply' => $message,
        ])->save();

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function proposeNewCheckout(User $host, BookingExtension $extension, array $data): BookingExtensionHostResponse
    {
        $this->authorize($host, $extension);

        $response = $this->createResponse($host, $extension, 'propose_new_checkout', [
            'message' => $data['message'] ?? null,
            'proposed_new_check_out_date' => $data['proposed_new_check_out_date'],
            'proposed_new_check_out_time' => $data['proposed_new_check_out_time'] ?? null,
        ]);

        $extension->forceFill([
            'status' => 'waiting_guest_response',
            'host_response' => $data['message'] ?? null,
        ])->save();

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createResponse(User $host, BookingExtension $extension, string $type, array $data): BookingExtensionHostResponse
    {
        return BookingExtensionHostResponse::query()->create([
            'booking_extension_id' => $extension->id,
            'host_user_id' => $host->id,
            'response_type' => $type,
            'message' => $data['message'] ?? null,
            'proposed_new_check_out_date' => $data['proposed_new_check_out_date'] ?? null,
            'proposed_new_check_out_time' => $data['proposed_new_check_out_time'] ?? null,
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);
    }

    private function authorize(User $host, BookingExtension $extension): void
    {
        $extension->loadMissing('booking');

        if (! $this->privacy->canHostRespond($host, $extension)) {
            throw new AuthorizationException(__('booking_extensions.messages.not_allowed'));
        }
    }
}
