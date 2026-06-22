<?php

namespace App\Services\Complaints;

use App\Enums\SleepingPlaceStatus;
use App\Models\ComplaintCase;
use App\Models\HostListingSuggestion;
use Illuminate\Support\Collection;

class ComplaintSearchIntegrationService
{
    public function markPlaceRequestOnlyIfSerious(ComplaintCase $case): void
    {
        if (! in_array($case->severity, ['high', 'urgent', 'emergency'], true)) {
            return;
        }

        $case->sleepingPlace?->forceFill(['publication_status' => 'request_only'])->save();
        app(ComplaintEventService::class)->record($case->fresh(), 'complaint_resolved', ['search_effect' => 'request_only']);
    }

    public function hidePlaceIfUnsafe(ComplaintCase $case): void
    {
        if (! in_array($case->severity, ['emergency'], true)) {
            return;
        }

        $case->sleepingPlace?->forceFill(['status' => SleepingPlaceStatus::Hidden])->save();
        app(ComplaintEventService::class)->record($case->fresh(), 'complaint_resolved', ['search_effect' => 'hidden']);
    }

    public function refreshSearchIndexes(ComplaintCase $case): void
    {
        app(ComplaintEventService::class)->record($case, 'complaint_resolved', ['search_effect' => 'refreshed']);
    }

    /**
     * @return Collection<int, HostListingSuggestion>
     */
    public function createHostSuggestions(ComplaintCase $case): Collection
    {
        return collect([
            HostListingSuggestion::query()->create([
                'user_id' => $case->host_user_id,
                'property_id' => $case->property_id,
                'room_id' => $case->room_id,
                'sleeping_place_id' => $case->sleeping_place_id,
                'suggestion_key' => 'complaint_update_listing',
                'severity' => in_array($case->severity, ['urgent', 'emergency'], true) ? 'urgent' : 'warning',
                'message_key' => 'complaints.suggestions.update_listing',
                'action_key' => 'complaints.actions.update_listing',
                'status' => 'open',
            ]),
        ]);
    }
}
