<?php

namespace App\Services\Reviews;

use App\Models\Booking;
use App\Models\ReviewPolicy;

class ReviewPolicyService
{
    public function getGlobalPolicy(): ReviewPolicy
    {
        return ReviewPolicy::query()
            ->where('scope_type', 'global')
            ->where('active', true)
            ->orderByDesc('id')
            ->first()
            ?? $this->createDefaultGlobalPolicy();
    }

    public function resolveForBooking(Booking $booking): ReviewPolicy
    {
        $specific = $this->activePolicy('sleeping_place', $booking->sleeping_place_id)
            ?: $this->activePolicy('room', $booking->room_id)
            ?: $this->activePolicy('property', $booking->property_id);

        return $specific ?: $this->getGlobalPolicy();
    }

    public function createDefaultGlobalPolicy(): ReviewPolicy
    {
        return ReviewPolicy::query()->firstOrCreate(
            [
                'scope_type' => 'global',
                'scope_id' => null,
            ],
            [
                'review_window_days' => 14,
                'edit_window_hours' => 24,
                'double_blind_enabled' => true,
                'publish_after_both_submitted' => true,
                'publish_after_window_expired' => true,
                'allow_review_photos' => true,
                'allow_host_response' => true,
                'allow_guest_response_future' => false,
                'minimum_stay_nights_for_review' => 1,
                'active' => true,
            ],
        );
    }

    private function activePolicy(string $scopeType, int|string|null $scopeId): ?ReviewPolicy
    {
        if (! $scopeId) {
            return null;
        }

        return ReviewPolicy::query()
            ->where('active', true)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->orderByDesc('id')
            ->first();
    }
}
