<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;
use Illuminate\Support\Collection;

class ListingMismatchCompensationService
{
    public function calculateSuggestedCompensation(BookingListingMismatchReport $report): float
    {
        $base = (float) ($report->booking?->total_payable ?: $report->booking?->total_amount ?: 0);

        return match ($report->severity) {
            'unsafe', 'urgent' => round($base * 0.3, 2),
            'high' => round($base * 0.2, 2),
            'medium' => round($base * 0.1, 2),
            default => round($base * 0.05, 2),
        };
    }

    /**
     * @return Collection<int, mixed>
     */
    public function createCompensationLines(BookingListingMismatchReport $report, float|int|string $amount): Collection
    {
        return collect([
            $report->compensationLines()->create([
                'line_type' => 'inconvenience_compensation',
                'label_key' => 'listing_mismatch.compensation_lines.inconvenience_compensation',
                'amount' => $amount,
                'currency' => $report->currency ?: 'EUR',
                'calculation_type' => 'fixed',
                'refundable' => true,
                'payable_to_guest' => true,
                'deduct_from_host_payout' => true,
                'reason_key' => 'listing_mismatch',
                'sort_order' => 10,
            ]),
        ]);
    }

    public function calculatePriceDifferenceRefund(BookingListingMismatchReport $report): float
    {
        return (float) $report->price_difference_amount;
    }

    public function calculateAffectedNightsRefund(BookingListingMismatchReport $report): float
    {
        $report->loadMissing('booking');
        $nightly = ((float) ($report->booking?->accommodation_amount ?: 0)) / max((int) ($report->booking?->nights_count ?: 1), 1);

        return round($nightly * max((int) ($report->booking?->nights_count ?: 1), 1) * 0.2, 2);
    }
}
