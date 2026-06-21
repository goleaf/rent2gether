<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationHostResponse;
use App\Models\BookingRelocationOption;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookingRelocationHostResponseService
{
    public function __construct(
        private readonly BookingRelocationPrivacyService $privacy,
        private readonly BookingRelocationConsentService $consents,
        private readonly BookingRelocationHoldService $holds,
        private readonly BookingRelocationEventService $events,
        private readonly BookingRelocationNotificationService $notifications,
    ) {}

    public function approve(User $host, BookingRelocation $relocation, ?string $message = null): BookingRelocationHostResponse
    {
        if (! $this->privacy->canHostRespond($host, $relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        $response = $this->response($host, $relocation, 'approve', $message);
        $hostConsent = $this->consents->requestHostConsent($relocation->refresh());
        $this->consents->accept($host, $hostConsent, $message);

        $nextStatus = $relocation->refresh()->requires_payment ? 'waiting_payment' : 'approved';
        $relocation->forceFill([
            'status' => $nextStatus,
            'approved_at' => $nextStatus === 'approved' ? now() : null,
            'host_comment' => $message ?: $relocation->host_comment,
        ])->save();

        $this->events->record($relocation->refresh(), 'host_consented', ['user_id' => $host->id]);

        return $response;
    }

    public function reject(User $host, BookingRelocation $relocation, string $reason): BookingRelocationHostResponse
    {
        if (! $this->privacy->canHostRespond($host, $relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        $response = $this->response($host, $relocation, 'reject', rejectionReason: $reason);
        $relocation->forceFill([
            'status' => 'rejected',
            'rejected_at' => now(),
            'host_comment' => $reason,
        ])->save();

        $this->holds->releaseNewPlaceHold($relocation->refresh(), 'rejected');
        $this->events->record($relocation, 'relocation_cancelled', ['reason' => $reason, 'user_id' => $host->id]);
        $this->notifications->notifyRelocationRejected($relocation->refresh());

        return $response;
    }

    public function askQuestion(User $host, BookingRelocation $relocation, string $message): BookingRelocationHostResponse
    {
        if (! $this->privacy->canHostRespond($host, $relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        $relocation->forceFill(['status' => 'waiting_guest_consent'])->save();

        return $this->response($host, $relocation, 'ask_question', $message);
    }

    public function offerAlternative(User $host, BookingRelocation $relocation, SleepingPlace $place): BookingRelocationOption
    {
        if (! $this->privacy->canHostRespond($host, $relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        return app(BookingRelocationOptionService::class)->createOption($relocation, $place, hostNote: null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function proposeRelocationTime(User $host, BookingRelocation $relocation, array $data): BookingRelocationHostResponse
    {
        if (! $this->privacy->canHostRespond($host, $relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        return $this->response(
            $host,
            $relocation,
            'propose_relocation_time',
            $data['message'] ?? null,
            proposedDate: $data['proposed_relocation_date'] ?? null,
            proposedTime: $data['proposed_relocation_time'] ?? null,
        );
    }

    private function response(
        User $host,
        BookingRelocation $relocation,
        string $type,
        ?string $message = null,
        ?string $proposedDate = null,
        ?string $proposedTime = null,
        ?string $rejectionReason = null,
    ): BookingRelocationHostResponse {
        return BookingRelocationHostResponse::query()->create([
            'booking_relocation_id' => $relocation->id,
            'host_user_id' => $host->id,
            'response_type' => $type,
            'message' => $message,
            'alternative_sleeping_place_id' => null,
            'alternative_room_id' => null,
            'proposed_relocation_date' => $proposedDate,
            'proposed_relocation_time' => $proposedTime,
            'rejection_reason' => $rejectionReason,
        ]);
    }
}
