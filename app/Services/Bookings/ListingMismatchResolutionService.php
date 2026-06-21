<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;
use App\Models\BookingListingMismatchResolutionOption;
use App\Models\User;

class ListingMismatchResolutionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createResolutionOption(BookingListingMismatchReport $report, string $type, array $data): BookingListingMismatchResolutionOption
    {
        $option = $report->resolutionOptions()->create([
            'resolution_type' => $type,
            'status' => $data['status'] ?? 'offered',
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? $report->currency,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? null,
            'booking_relocation_id' => $data['booking_relocation_id'] ?? null,
            'booking_cancellation_id' => $data['booking_cancellation_id'] ?? null,
            'booking_refund_id' => $data['booking_refund_id'] ?? null,
            'cleaning_task_id' => $data['cleaning_task_id'] ?? null,
            'maintenance_request_id' => $data['maintenance_request_id'] ?? null,
            'offered_by_user_id' => $data['offered_by_user_id'] ?? null,
            'accepted_by_user_id' => $data['accepted_by_user_id'] ?? null,
            'offered_at' => $data['offered_at'] ?? now(),
            'accepted_at' => $data['accepted_at'] ?? null,
            'rejected_at' => $data['rejected_at'] ?? null,
            'completed_at' => $data['completed_at'] ?? null,
        ]);

        $report->forceFill([
            'resolution_type' => $type,
            'resolution_status' => 'offered',
        ])->save();

        app(ListingMismatchEventService::class)->record($report->fresh(), 'resolution_offered', ['resolution_option_id' => $option->id]);

        return $option->fresh();
    }

    public function acceptResolution(BookingListingMismatchResolutionOption $option, User $user): BookingListingMismatchResolutionOption
    {
        $option->forceFill([
            'status' => 'accepted',
            'accepted_by_user_id' => $user->id,
            'accepted_at' => now(),
        ])->save();

        $option->report?->forceFill(['resolution_status' => 'accepted'])->save();

        return $option->fresh();
    }

    public function rejectResolution(BookingListingMismatchResolutionOption $option, User $user): BookingListingMismatchResolutionOption
    {
        $option->forceFill([
            'status' => 'rejected',
            'accepted_by_user_id' => $user->id,
            'rejected_at' => now(),
        ])->save();

        $option->report?->forceFill(['resolution_status' => 'rejected'])->save();

        return $option->fresh();
    }

    public function markResolutionCompleted(BookingListingMismatchResolutionOption $option): BookingListingMismatchResolutionOption
    {
        $option->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();

        $option->report?->forceFill(['resolution_status' => 'completed'])->save();

        return $option->fresh();
    }

    public function applyResolution(BookingListingMismatchReport $report): void
    {
        $report->forceFill(['resolution_status' => 'completed'])->save();
    }
}
