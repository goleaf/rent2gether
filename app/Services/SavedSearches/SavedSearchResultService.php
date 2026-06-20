<?php

namespace App\Services\SavedSearches;

use App\Models\SavedSearch;
use App\Models\SavedSearchResult;
use App\Models\SleepingPlace;
use Illuminate\Support\Collection;

class SavedSearchResultService
{
    public function __construct(
        private readonly SavedSearchMatcherService $matcher,
        private readonly SavedSearchSnapshotService $snapshots,
    ) {}

    /**
     * @param  Collection<int, SleepingPlace>  $places
     * @return Collection<int, SavedSearchResult>
     */
    public function syncMatches(SavedSearch $search, Collection $places): Collection
    {
        return $places
            ->map(fn (SleepingPlace $place): SavedSearchResult => $this->syncMatch($search, $place))
            ->values();
    }

    public function syncMatch(SavedSearch $search, SleepingPlace $place): SavedSearchResult
    {
        $existing = SavedSearchResult::query()
            ->where('saved_search_id', $search->id)
            ->where('sleeping_place_id', $place->id)
            ->first();

        $matchScore = $this->matcher->calculateMatchScore($search, $place);

        if (! $existing) {
            return SavedSearchResult::query()->create([
                ...$this->snapshots->createResultSnapshot($search, $place),
                'match_score' => $matchScore,
            ]);
        }

        $wasUnavailable = $existing->status === 'unavailable' || $existing->became_unavailable;

        $existing->forceFill([
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'last_seen_at' => now(),
            'last_matched_at' => now(),
            'status' => 'matched',
            'match_score' => $matchScore,
            'became_available_again' => $existing->became_available_again || $wasUnavailable,
        ])->save();

        return $existing->refresh();
    }

    public function refreshExistingResults(SavedSearch $search): void
    {
        SavedSearchResult::query()
            ->where('saved_search_id', $search->id)
            ->with([
                'savedSearch.user:id',
                'sleepingPlace:id,room_id,property_id,status,base_price_per_night,weekly_price,monthly_price,weekend_price,cleaning_fee,deposit_amount,currency,min_nights,max_nights,max_guests',
                'sleepingPlace.room:id,property_id,status',
                'sleepingPlace.property:id,status',
            ])
            ->get()
            ->each(fn (SavedSearchResult $result) => $this->snapshots->refreshResultSnapshot($result));
    }
}
