<?php

namespace App\Services\Bookings;

use App\Enums\SleepingPlaceStatus;
use App\Models\BookingListingMismatchReport;
use App\Models\HostListingSuggestion;
use Illuminate\Support\Collection;

class ListingMismatchSearchIntegrationService
{
    public function markSleepingPlaceRequestOnlyIfSerious(BookingListingMismatchReport $report): void
    {
        if (! in_array($report->severity, ['high', 'urgent', 'unsafe'], true)) {
            return;
        }

        $report->sleepingPlace?->forceFill(['publication_status' => 'request_only'])->save();
    }

    public function hideSleepingPlaceIfUnsafe(BookingListingMismatchReport $report): void
    {
        if ($report->severity !== 'unsafe') {
            return;
        }

        $report->sleepingPlace?->forceFill(['status' => SleepingPlaceStatus::Hidden])->save();
    }

    /**
     * @return Collection<int, HostListingSuggestion>
     */
    public function createHostListingSuggestions(BookingListingMismatchReport $report): Collection
    {
        return collect([
            HostListingSuggestion::query()->create([
                'user_id' => $report->host_user_id,
                'property_id' => $report->property_id,
                'room_id' => $report->room_id,
                'sleeping_place_id' => $report->sleeping_place_id,
                'suggestion_key' => 'listing_mismatch_update_listing',
                'severity' => in_array($report->severity, ['urgent', 'unsafe'], true) ? 'urgent' : 'warning',
                'message_key' => 'listing_mismatch.suggestions.update_listing',
                'action_key' => 'listing_mismatch.actions.update_listing',
                'status' => 'open',
            ]),
        ]);
    }

    public function refreshSearchIndexes(BookingListingMismatchReport $report): void
    {
        app(ListingMismatchEventService::class)->record($report, 'snapshot_compared', ['search_refresh' => true]);
    }
}
