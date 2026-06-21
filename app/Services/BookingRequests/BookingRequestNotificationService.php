<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use App\Models\BookingRequestHostResponse;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

class BookingRequestNotificationService
{
    public function notifyHostNewRequest(BookingRequest $request): void
    {
        $this->notify($request->host, 'booking_request_new', $request, 'notifications.booking_request_new.title', 'notifications.booking_request_new.body');
    }

    public function notifyGuestRequestApproved(BookingRequest $request): void
    {
        $this->notify($request->guest, 'booking_request_approved', $request, 'notifications.booking_request_approved.title', 'notifications.booking_request_approved.body');
    }

    public function notifyGuestRequestRejected(BookingRequest $request): void
    {
        $this->notify($request->guest, 'booking_request_rejected', $request, 'notifications.booking_request_rejected.title', 'notifications.booking_request_rejected.body');
    }

    public function notifyGuestQuestionAsked(BookingRequestHostResponse $response): void
    {
        $response->loadMissing('bookingRequest.guest');
        $this->notify($response->bookingRequest->guest, 'booking_request_question', $response->bookingRequest, 'notifications.booking_request_question.title', 'notifications.booking_request_question.body');
    }

    public function notifyHostGuestResponded(BookingRequest $request): void
    {
        $this->notify($request->host, 'booking_request_guest_responded', $request, 'notifications.booking_request_guest_responded.title', 'notifications.booking_request_guest_responded.body');
    }

    public function notifyRequestExpired(BookingRequest $request): void
    {
        $this->notify($request->guest, 'booking_request_expired', $request, 'notifications.booking_request_expired.title', 'notifications.booking_request_expired.body');
    }

    public function notifyRequestWithdrawn(BookingRequest $request): void
    {
        $this->notify($request->host, 'booking_request_withdrawn', $request, 'notifications.booking_request_withdrawn.title', 'notifications.booking_request_withdrawn.body');
    }

    private function notify(?User $user, string $type, BookingRequest $request, string $titleKey, string $bodyKey): void
    {
        if (! $user instanceof User) {
            return;
        }

        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'sleeping_place_id' => $request->sleeping_place_id,
            'data' => [
                'booking_request_id' => $request->id,
                'reference' => $request->request_number,
                'params' => [
                    'reference' => $request->request_number,
                    'date' => $request->check_in_date?->toDateString(),
                    'deadline' => $request->expires_at?->toDateTimeString(),
                ],
            ],
            'title_key' => $titleKey,
            'body_key' => $bodyKey,
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }
}
