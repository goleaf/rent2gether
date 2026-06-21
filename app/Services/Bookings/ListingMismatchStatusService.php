<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingListingMismatchReport;
use App\Models\User;

class ListingMismatchStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(BookingListingMismatchReport $report, string $newStatus, ?User $user = null, array $context = []): BookingListingMismatchReport
    {
        $oldStatus = (string) $report->status;

        if ($oldStatus === $newStatus) {
            return $report;
        }

        if (! $this->canTransition($report, $newStatus)) {
            abort(422, __('listing_mismatch.validation.invalid_status_transition'));
        }

        $report->forceFill([
            ...$this->timestampAttributes($newStatus),
            'status' => $newStatus,
        ])->save();

        $report->statusLogs()->create([
            'booking_id' => $report->booking_id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $report->fresh();
    }

    public function canTransition(BookingListingMismatchReport $report, string $newStatus): bool
    {
        if ((string) $report->status === $newStatus) {
            return true;
        }

        return ! in_array((string) $report->status, ['closed', 'cancelled'], true);
    }

    public function syncBookingStatus(BookingListingMismatchReport $report): Booking
    {
        $booking = $report->booking()->firstOrFail();

        if (in_array((string) $report->status, ['dispute_opened', 'complaint_created'], true)) {
            $booking->forceFill([
                'status' => BookingStatus::DisputeOpened,
                'has_dispute' => true,
                'has_complaint' => true,
            ])->save();
        }

        if (in_array((string) $report->status, ['reported', 'confirmed', 'partially_confirmed'], true)) {
            $booking->forceFill(['has_complaint' => true])->save();
        }

        return $booking->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function timestampAttributes(string $newStatus): array
    {
        return match ($newStatus) {
            'confirmed', 'partially_confirmed', 'rejected', 'fixed', 'dispute_opened', 'complaint_created' => ['resolved_at' => now()],
            'closed' => ['closed_at' => now()],
            default => [],
        };
    }
}
