<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;
use App\Models\BookingNoShowContactAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class BookingNoShowContactService
{
    public function __construct(
        private readonly BookingNoShowEventService $events,
        private readonly BookingNoShowNotificationService $notifications,
    ) {}

    public function sendInitialReminder(BookingNoShow $noShow): BookingNoShowContactAttempt
    {
        return $this->createAttempt($noShow, 'automatic_reminder', 'no_show.messages.guest_response_required');
    }

    public function sendGuestResponseRequest(BookingNoShow $noShow): BookingNoShowContactAttempt
    {
        $attempt = $this->createAttempt($noShow, 'guest_check_request', 'no_show.messages.host_reported_no_show');

        $noShow->forceFill([
            'guest_contacted_at' => now(),
        ])->save();

        $this->notifications->notifyGuestResponseRequired($noShow->fresh());

        return $attempt;
    }

    public function sendFinalWarning(BookingNoShow $noShow): BookingNoShowContactAttempt
    {
        $attempt = $this->createAttempt($noShow, 'final_warning', 'no_show.messages.final_warning');
        $this->notifications->notifyGuestFinalWarning($noShow->fresh());

        return $attempt;
    }

    public function recordHostMessage(User $host, BookingNoShow $noShow, string $message): BookingNoShowContactAttempt
    {
        return $this->createAttempt($noShow, 'host_message', null, $host, $message);
    }

    /**
     * @return Collection<int, BookingNoShowContactAttempt>
     */
    public function getContactAttempts(BookingNoShow $noShow): Collection
    {
        return $noShow->contactAttempts()
            ->orderByDesc('attempted_at')
            ->orderByDesc('id')
            ->get();
    }

    private function createAttempt(BookingNoShow $noShow, string $type, ?string $messageKey, ?User $actor = null, ?string $message = null): BookingNoShowContactAttempt
    {
        $attempt = $noShow->contactAttempts()->create([
            'booking_id' => $noShow->booking_id,
            'attempted_by_user_id' => $actor?->id,
            'contact_channel' => 'in_app',
            'attempt_type' => $type,
            'status' => 'sent',
            'message_key' => $messageKey,
            'message_text' => $message,
            'attempted_at' => now(),
        ]);

        $this->events->record($noShow->fresh(), 'guest_contact_attempted', [
            'attempt_type' => $type,
            'user_id' => $actor?->id,
        ]);

        return $attempt->fresh();
    }
}
