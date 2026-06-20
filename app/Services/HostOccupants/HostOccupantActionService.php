<?php

namespace App\Services\HostOccupants;

use App\Enums\BookingExtensionStatus;
use App\Enums\BookingStatus;
use App\Enums\MessageThreadType;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\Conversation;
use App\Models\HostCleaningTask;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use App\Services\HostOccupants\Data\HostOccupantActionResultData;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class HostOccupantActionService
{
    public function __construct(
        private readonly HostOccupantPrivacyService $privacy,
        private readonly HostCurrentStaySnapshotService $snapshots,
        private readonly HostGuestStayFlagService $flags,
    ) {}

    public function markCheckedIn(User $host, Booking $booking): Booking
    {
        $this->authorizeBooking($host, $booking);

        $booking->forceFill([
            'status' => BookingStatus::CheckedIn,
            'checked_in_at' => now(),
            'host_confirmed_checkin_at' => now(),
        ])->save();

        $this->snapshots->refreshForBooking($booking->refresh());
        $this->flags->refreshFlagsForBooking($booking);

        return $booking->refresh();
    }

    public function markCheckedOut(User $host, Booking $booking): Booking
    {
        $this->authorizeBooking($host, $booking);

        $booking->forceFill([
            'status' => BookingStatus::CheckedOut,
            'checked_out_at' => now(),
            'host_confirmed_checkout_at' => now(),
        ])->save();

        $this->snapshots->refreshForBooking($booking->refresh());
        $this->flags->refreshFlagsForBooking($booking);

        return $booking->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function startCheckoutProcess(User $host, Booking $booking, string $reason): array
    {
        $this->authorizeBooking($host, $booking);

        $flagKey = $this->snapshots->detectStayStatus($booking) === 'checkout_overdue'
            ? 'checkout_overdue'
            : 'checkout_today';

        $this->flags->createFlag($host, $booking, $flagKey, 'high');

        return (new HostOccupantActionResultData(
            status: 'needs_review',
            messageKey: 'current_occupants.actions_results.checkout_review_started',
            bookingId: $booking->id,
        ))->toArray() + ['reason' => $reason];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function offerExtension(User $host, Booking $booking, array $data): array
    {
        $this->authorizeBooking($host, $booking);

        $newCheckout = CarbonImmutable::parse($data['requested_new_checkout_date'] ?? $booking->check_out_date)->toDateString();
        $additionalNights = max(0, (int) CarbonImmutable::parse($booking->check_out_date)->diffInDays(CarbonImmutable::parse($newCheckout), false));
        $amount = (float) ($data['additional_amount'] ?? 0);

        $extension = BookingExtension::query()->create([
            'booking_id' => $booking->id,
            'current_checkout_date' => $booking->check_out_date,
            'requested_new_checkout_date' => $newCheckout,
            'additional_nights' => $additionalNights,
            'additional_amount' => $amount,
            'extra_nights' => $additionalNights,
            'extra_amount' => $amount,
            'discount_amount' => 0,
            'total_extra' => $amount,
            'new_total' => ((float) $booking->total_amount) + $amount,
            'payment_required' => $amount > 0,
            'requires_host_approval' => true,
            'status' => BookingExtensionStatus::AwaitingHostApproval,
            'guest_message' => $data['message'] ?? null,
        ]);

        $this->snapshots->refreshForBooking($booking->refresh());
        $this->flags->refreshFlagsForBooking($booking);

        return (new HostOccupantActionResultData(
            status: 'created',
            messageKey: 'current_occupants.actions_results.extension_offered',
            bookingId: $booking->id,
            resourceId: $extension->id,
        ))->toArray();
    }

    public function approveExtension(User $host, Booking $booking): Booking
    {
        $this->authorizeBooking($host, $booking);

        $extension = $booking->extensions()
            ->whereIn('status', [
                BookingExtensionStatus::AwaitingHostApproval->value,
                BookingExtensionStatus::AwaitingPayment->value,
            ])
            ->latest('id')
            ->first();

        if ($extension) {
            $extension->forceFill([
                'status' => BookingExtensionStatus::Approved,
                'approved_at' => now(),
            ])->save();

            $booking->forceFill([
                'check_out_date' => $extension->requested_new_checkout_date,
                'check_out' => $extension->requested_new_checkout_date,
                'nights_count' => ($booking->nights_count ?? 0) + ($extension->additional_nights ?? 0),
                'nights' => ($booking->nights ?? 0) + ($extension->additional_nights ?? 0),
            ])->save();
        }

        $this->snapshots->refreshForBooking($booking->refresh());
        $this->flags->refreshFlagsForBooking($booking);

        return $booking->refresh();
    }

    public function declineExtension(User $host, Booking $booking): Booking
    {
        $this->authorizeBooking($host, $booking);

        $extension = $booking->extensions()
            ->where('status', BookingExtensionStatus::AwaitingHostApproval->value)
            ->latest('id')
            ->first();

        if ($extension) {
            $extension->forceFill([
                'status' => BookingExtensionStatus::Declined,
                'declined_at' => now(),
            ])->save();
        }

        $this->snapshots->refreshForBooking($booking->refresh());
        $this->flags->refreshFlagsForBooking($booking);

        return $booking->refresh();
    }

    public function createCleaningTask(User $host, Booking $booking): HostCleaningTask
    {
        $this->authorizeBooking($host, $booking);

        $task = HostCleaningTask::query()->create([
            'user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'booking_id' => $booking->id,
            'status' => 'planned',
            'scheduled_date' => $booking->check_out_date,
            'scheduled_time' => $booking->check_out_time?->format('H:i'),
            'reason' => 'after_checkout',
        ]);

        $this->snapshots->refreshForBooking($booking->refresh());
        $this->flags->refreshFlagsForBooking($booking);

        return $task;
    }

    /**
     * @return array<string, mixed>
     */
    public function createInspectionTask(User $host, Booking $booking): array
    {
        $this->authorizeBooking($host, $booking);

        $flag = $this->flags->createFlag($host, $booking, 'inspection_needed', 'medium');
        $this->snapshots->refreshForBooking($booking->refresh());

        return (new HostOccupantActionResultData(
            status: 'created',
            messageKey: 'current_occupants.actions_results.inspection_created',
            bookingId: $booking->id,
            resourceId: $flag->id,
        ))->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function messageGuest(User $host, Booking $booking, string $message): array
    {
        $this->authorizeBooking($host, $booking);

        if (! $this->privacy->canViewGuestContact($host, $booking)) {
            throw new AuthorizationException;
        }

        if (blank($message)) {
            throw ValidationException::withMessages([
                'message' => __('current_occupants.validation.message_required'),
            ]);
        }

        $thread = MessageThread::query()->firstOrCreate(
            [
                'booking_id' => $booking->id,
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $host->id,
            ],
            [
                'type' => MessageThreadType::CurrentStay,
                'property_id' => $booking->property_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'status' => 'open',
                'last_message_at' => now(),
            ],
        );
        $conversation = Conversation::query()->firstOrCreate(
            [
                'booking_id' => $booking->id,
                'participant_one_id' => $host->id,
                'participant_two_id' => $booking->guest_user_id,
            ],
            [
                'bed_id' => $booking->bed_id,
                'last_message_at' => now(),
            ],
        );

        $created = Message::query()->create([
            'conversation_id' => $conversation->id,
            'thread_id' => $thread->id,
            'sender_id' => $host->id,
            'sender_user_id' => $host->id,
            'recipient_user_id' => $booking->guest_user_id,
            'booking_id' => $booking->id,
            'property_id' => $booking->property_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'body' => $message,
            'locale' => app()->getLocale(),
        ]);

        $thread->forceFill(['last_message_at' => now()])->save();
        $conversation->forceFill(['last_message_at' => now()])->save();

        return (new HostOccupantActionResultData(
            status: 'sent',
            messageKey: 'current_occupants.actions_results.message_sent',
            bookingId: $booking->id,
            resourceId: $created->id,
        ))->toArray();
    }

    private function authorizeBooking(User $host, Booking $booking): void
    {
        if (! $this->privacy->canViewOccupant($host, $booking)) {
            throw new AuthorizationException;
        }
    }
}
