<?php

namespace App\Services\Bookings;

use App\Models\Booking;

class BookingVerificationService
{
    public function __construct(
        private readonly BookingRequirementService $requirements,
    ) {}

    public function markPhoneVerified(Booking $booking): Booking
    {
        $booking->forceFill([
            'phone_verified_at' => now(),
        ])->save();
        $this->refreshVerificationStatus($booking);
        $this->requirements->markCompleted($booking, 'phone_verification');

        return $booking->fresh();
    }

    public function markIdentityVerified(Booking $booking): Booking
    {
        $booking->forceFill([
            'identity_verified_at' => now(),
        ])->save();
        $this->refreshVerificationStatus($booking);
        $this->requirements->markCompleted($booking, 'identity_verification');

        return $booking->fresh();
    }

    public function markDocumentsVerified(Booking $booking): Booking
    {
        $booking->forceFill([
            'documents_verified_at' => now(),
        ])->save();
        $this->refreshVerificationStatus($booking);
        $this->requirements->markCompleted($booking, 'document_verification');

        return $booking->fresh();
    }

    public function markVerificationFailed(Booking $booking, string $reason): Booking
    {
        $booking->forceFill([
            'verification_status' => 'failed',
            'cancellation_reason' => $reason,
        ])->save();

        foreach (['phone_verification', 'identity_verification', 'document_verification'] as $key) {
            if ($booking->requirements()->where('requirement_key', $key)->exists()) {
                $this->requirements->markFailed($booking, $key, $reason);
            }
        }

        return $booking->fresh();
    }

    public function getVerificationStatus(Booking $booking): string
    {
        $required = [
            'phone' => (bool) $booking->requires_phone_verification,
            'identity' => (bool) $booking->requires_identity_verification,
            'documents' => (bool) $booking->requires_document_verification,
        ];

        if (! in_array(true, $required, true)) {
            return 'not_required';
        }

        $passed = (! $required['phone'] || $booking->phone_verified_at !== null)
            && (! $required['identity'] || $booking->identity_verified_at !== null)
            && (! $required['documents'] || $booking->documents_verified_at !== null);

        return $passed ? 'passed' : 'pending';
    }

    private function refreshVerificationStatus(Booking $booking): void
    {
        $fresh = $booking->fresh();

        if ($fresh instanceof Booking) {
            $fresh->forceFill([
                'verification_status' => $this->getVerificationStatus($fresh),
            ])->save();
        }
    }
}
