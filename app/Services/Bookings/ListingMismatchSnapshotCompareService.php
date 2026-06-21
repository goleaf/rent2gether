<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;

class ListingMismatchSnapshotCompareService
{
    public function __construct(
        private readonly ListingMismatchWarningService $warnings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function compareWithBookingSnapshot(BookingListingMismatchReport $report): array
    {
        $result = $this->compareKey($report, $this->snapshotKeyForType((string) $report->mismatch_type));
        $this->persistComparison($report, (float) $result['confidence'], $result);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function compareAmenity(BookingListingMismatchReport $report, string $amenityKey): array
    {
        $result = $this->compareKey($report, $amenityKey);
        $this->persistComparison($report, (float) $result['confidence'], $result);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function compareRule(BookingListingMismatchReport $report, string $ruleKey): array
    {
        return $this->compareAmenity($report, $ruleKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function compareSleepingPlaceFeature(BookingListingMismatchReport $report, string $featureKey): array
    {
        return $this->compareAmenity($report, $featureKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function compareRoomFeature(BookingListingMismatchReport $report, string $featureKey): array
    {
        return $this->compareAmenity($report, $featureKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function comparePropertyFeature(BookingListingMismatchReport $report, string $featureKey): array
    {
        return $this->compareAmenity($report, $featureKey);
    }

    public function calculateAutoMatchConfidence(BookingListingMismatchReport $report): float
    {
        return (float) $this->compareWithBookingSnapshot($report)['confidence'];
    }

    /**
     * @return array<string, mixed>
     */
    private function compareKey(BookingListingMismatchReport $report, string $key): array
    {
        $snapshot = $this->listingSnapshot($report);
        $promised = data_get($snapshot, $key);
        $wasPromised = $this->snapshotValueMeansPromised($promised);
        $confidence = $wasPromised ? 0.85 : 0.25;

        $this->warnings->recordWarning($report, [
            'warning_key' => $wasPromised ? 'claimed_missing_amenity_was_listed' : 'claimed_feature_was_not_listed',
            'severity' => $wasPromised ? 'info' : 'warning',
            'message_key' => $wasPromised
                ? 'listing_mismatch.warnings.claimed_missing_amenity_was_listed'
                : 'listing_mismatch.warnings.claimed_feature_was_not_listed',
            'blocking' => false,
        ]);

        return [
            'snapshot_key' => $key,
            'promised_value' => $promised,
            'was_promised' => $wasPromised,
            'confidence' => $confidence,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function listingSnapshot(BookingListingMismatchReport $report): array
    {
        $report->loadMissing('booking');
        $snapshot = $report->booking?->nightly_price_snapshot ?: [];

        return data_get($snapshot, '_snapshots.listing', []);
    }

    private function snapshotValueMeansPromised(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value > 0;
        }

        return filled($value) && ! in_array($value, ['no', 'none', 'false', '0'], true);
    }

    private function snapshotKeyForType(string $mismatchType): string
    {
        return match ($mismatchType) {
            'missing_wifi', 'wifi_not_working' => 'has_wifi',
            'missing_locker' => 'has_locker',
            'missing_lock' => 'has_lockable_locker',
            'missing_bedding' => 'has_bedding',
            'missing_towel' => 'has_towel',
            'missing_pillow' => 'has_pillow',
            'missing_blanket' => 'has_blanket',
            'missing_privacy_curtain' => 'has_privacy_curtain',
            'missing_socket' => 'has_socket',
            'wrong_bed_type' => 'bed_type',
            'wrong_bunk_level' => 'bunk_level',
            'kitchen_not_available' => 'kitchen_available',
            'more_people_than_listed' => 'room_people_count',
            'noise_level_mismatch' => 'quiet_room',
            'self_check_in_not_available' => 'self_check_in',
            'wrong_address' => 'exact_address',
            default => $mismatchType,
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function persistComparison(BookingListingMismatchReport $report, float $confidence, array $context): void
    {
        $report->forceFill([
            'snapshot_compared' => true,
            'auto_match_confidence' => max((float) ($report->auto_match_confidence ?? 0), $confidence),
            'what_was_promised' => $report->what_was_promised ?: json_encode($context, JSON_THROW_ON_ERROR),
        ])->save();

        app(ListingMismatchEventService::class)->record($report->fresh(), 'snapshot_compared', $context);
    }
}
