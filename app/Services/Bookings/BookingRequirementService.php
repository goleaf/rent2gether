<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingRequirement;
use Illuminate\Support\Collection;

class BookingRequirementService
{
    /**
     * @return Collection<int, BookingRequirement>
     */
    public function createRequirements(Booking $booking): Collection
    {
        $requirements = collect([
            ['rules_acceptance', true],
        ]);

        if ((bool) $booking->requires_phone_verification) {
            $requirements->push(['phone_verification', true]);
        }

        if ((bool) $booking->requires_identity_verification) {
            $requirements->push(['identity_verification', true]);
        }

        if ((bool) $booking->requires_document_verification) {
            $requirements->push(['document_verification', true]);
        }

        if (in_array($this->value($booking->payment_status), ['unpaid', 'awaiting_payment', 'waiting_payment', 'pending', 'partially_paid'], true)) {
            $requirements->push(['payment', true]);
        }

        if (in_array((string) $booking->approval_type, ['requires_host_confirmation', 'request_only'], true)) {
            $requirements->push(['host_confirmation', true]);
        }

        return $requirements
            ->unique(fn (array $requirement): string => $requirement[0])
            ->map(fn (array $requirement): BookingRequirement => $booking->requirements()->updateOrCreate(
                ['requirement_key' => $requirement[0]],
                [
                    'required' => $requirement[1],
                    'status' => $this->initialStatus($booking, $requirement[0]),
                    'message_key' => 'bookings.requirements.'.$requirement[0],
                ],
            ))
            ->values();
    }

    public function markCompleted(Booking $booking, string $requirementKey): BookingRequirement
    {
        $requirement = $this->firstOrCreate($booking, $requirementKey);
        $requirement->forceFill([
            'status' => BookingRequirement::STATUS_COMPLETED,
            'completed_at' => now(),
        ])->save();

        return $requirement;
    }

    public function markFailed(Booking $booking, string $requirementKey, ?string $reason = null): BookingRequirement
    {
        $requirement = $this->firstOrCreate($booking, $requirementKey);
        $requirement->forceFill([
            'status' => BookingRequirement::STATUS_FAILED,
            'message_key' => $reason ?: 'bookings.requirements.'.$requirementKey,
        ])->save();

        return $requirement;
    }

    /**
     * @return Collection<int, BookingRequirement>
     */
    public function getBlockingRequirements(Booking $booking): Collection
    {
        return $booking->requirements()
            ->where('required', true)
            ->whereIn('status', [BookingRequirement::STATUS_PENDING, BookingRequirement::STATUS_FAILED])
            ->get();
    }

    public function allRequiredCompleted(Booking $booking): bool
    {
        return $this->getBlockingRequirements($booking)->isEmpty();
    }

    private function firstOrCreate(Booking $booking, string $requirementKey): BookingRequirement
    {
        return $booking->requirements()->firstOrCreate(
            ['requirement_key' => $requirementKey],
            [
                'status' => BookingRequirement::STATUS_PENDING,
                'required' => true,
                'message_key' => 'bookings.requirements.'.$requirementKey,
            ],
        );
    }

    private function initialStatus(Booking $booking, string $requirementKey): string
    {
        return match ($requirementKey) {
            'rules_acceptance' => $booking->rules_accepted_at ? BookingRequirement::STATUS_COMPLETED : BookingRequirement::STATUS_PENDING,
            'phone_verification' => $booking->phone_verified_at ? BookingRequirement::STATUS_COMPLETED : BookingRequirement::STATUS_PENDING,
            'identity_verification' => $booking->identity_verified_at ? BookingRequirement::STATUS_COMPLETED : BookingRequirement::STATUS_PENDING,
            'document_verification' => $booking->documents_verified_at ? BookingRequirement::STATUS_COMPLETED : BookingRequirement::STATUS_PENDING,
            'payment' => in_array($this->value($booking->payment_status), ['paid', 'refunded', 'partially_refunded'], true)
                ? BookingRequirement::STATUS_COMPLETED
                : BookingRequirement::STATUS_PENDING,
            default => BookingRequirement::STATUS_PENDING,
        };
    }

    private function value(mixed $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
    }
}
