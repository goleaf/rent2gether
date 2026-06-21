<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;
use App\Models\BookingListingMismatchWarning;
use Illuminate\Support\Collection;

class ListingMismatchWarningService
{
    /**
     * @return Collection<int, BookingListingMismatchWarning>
     */
    public function generateWarnings(BookingListingMismatchReport $report): Collection
    {
        return collect([
            $this->detectMissingEvidence($report),
            $this->detectLateReport($report),
            $this->detectSimilarRecentReports($report),
            $this->detectUnsafeClaim($report),
        ])->filter()
            ->map(fn (array $warning): BookingListingMismatchWarning => $this->recordWarning($report, $warning));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detectMissingEvidence(BookingListingMismatchReport $report): ?array
    {
        if ($report->media()->exists()) {
            return null;
        }

        return [
            'warning_key' => 'photo_evidence_missing',
            'severity' => 'info',
            'message_key' => 'listing_mismatch.warnings.photo_evidence_missing',
            'blocking' => false,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detectLateReport(BookingListingMismatchReport $report): ?array
    {
        if (! $report->discovered_at || $report->reported_at->diffInHours($report->discovered_at) <= 24) {
            return null;
        }

        return [
            'warning_key' => 'issue_reported_late',
            'severity' => 'warning',
            'message_key' => 'listing_mismatch.warnings.issue_reported_late',
            'blocking' => false,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detectSimilarRecentReports(BookingListingMismatchReport $report): ?array
    {
        $hasSimilar = BookingListingMismatchReport::query()
            ->where('id', '!=', $report->id)
            ->where('sleeping_place_id', $report->sleeping_place_id)
            ->where('mismatch_type', $report->mismatch_type)
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();

        if (! $hasSimilar) {
            return null;
        }

        return [
            'warning_key' => 'host_has_similar_recent_reports',
            'severity' => 'warning',
            'message_key' => 'listing_mismatch.warnings.host_has_similar_recent_reports',
            'blocking' => false,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detectUnsafeClaim(BookingListingMismatchReport $report): ?array
    {
        if ($report->severity !== 'unsafe') {
            return null;
        }

        return [
            'warning_key' => 'unsafe_claim_requires_urgent_action',
            'severity' => 'urgent',
            'message_key' => 'listing_mismatch.warnings.unsafe_claim_requires_urgent_action',
            'blocking' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $warning
     */
    public function recordWarning(BookingListingMismatchReport $report, array $warning): BookingListingMismatchWarning
    {
        return $report->warnings()->updateOrCreate(
            ['warning_key' => $warning['warning_key']],
            [
                'severity' => $warning['severity'] ?? 'warning',
                'message_key' => $warning['message_key'] ?? 'listing_mismatch.warnings.'.$warning['warning_key'],
                'message_params_json' => $warning['message_params_json'] ?? null,
                'visible_to_guest' => $warning['visible_to_guest'] ?? true,
                'visible_to_host' => $warning['visible_to_host'] ?? true,
                'blocking' => $warning['blocking'] ?? false,
            ]
        );
    }
}
