<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Data\Hints\HintContext;
use App\Models\Favorite;
use App\Models\ListingHintSnapshot;
use App\Models\SavedSearchResult;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Collection;

class GuestHintService
{
    public function __construct(
        private readonly ListingHintCalculatorService $calculator,
        private readonly HintDismissalService $dismissals,
        private readonly HintPriorityService $priority,
        private readonly HintVisibilityService $visibility,
        private readonly PriceHintService $prices,
    ) {}

    /**
     * @return Collection<int, GuestHintData>
     */
    public function getHintsForCard(?User $user, SleepingPlace $place, HintContext $context): Collection
    {
        $hints = $this->allHints($place, $context)
            ->filter(fn (GuestHintData $hint): bool => $this->visibility->shouldShowOnCard($hint));

        return $this->priority->chooseForCard($this->filterDismissedHintsForContext($user, $place, $hints, 'card'), 3);
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    public function getHintsForDetail(?User $user, SleepingPlace $place, HintContext $context): Collection
    {
        $hints = $this->allHints($place, $context)
            ->filter(fn (GuestHintData $hint): bool => $this->visibility->shouldShowOnDetail($hint));

        return $this->priority
            ->sortByImportanceAndContext($this->filterDismissedHintsForContext($user, $place, $hints, 'detail'), 'detail')
            ->values();
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    public function getHintsBeforeBooking(User $user, SleepingPlace $place, HintContext $context): Collection
    {
        $hints = $this->allHints($place, $context)
            ->filter(fn (GuestHintData $hint): bool => $this->visibility->shouldShowBeforeBooking($hint));

        return $this->priority->chooseBeforeBooking(
            $this->filterDismissedHintsForContext($user, $place, $hints, 'before_booking'),
        );
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    public function getHintsForFavorites(User $user, Favorite $favorite): Collection
    {
        $hint = $this->prices->priceChangedForFavorite($favorite);

        return collect([$hint])->filter()->values();
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    public function getHintsForSavedSearch(User $user, SavedSearchResult $result): Collection
    {
        $hints = collect();

        if ($result->price_changed) {
            $hints->push(new GuestHintData(
                key: (float) ($result->price_change_amount ?? 0) < 0 ? 'price_dropped' : 'price_increased',
                category: 'price',
                type: (float) ($result->price_change_amount ?? 0) < 0 ? 'positive' : 'warning',
                importance: 'medium',
                priority: 70,
                messageKey: (float) ($result->price_change_amount ?? 0) < 0 ? 'guest_hints.messages.price_dropped' : 'guest_hints.messages.price_increased',
                showInSavedSearch: true,
                source: 'saved_search',
            ));
        }

        if ($result->became_available_again) {
            $hints->push(new GuestHintData(
                key: 'became_available_again',
                category: 'availability',
                type: 'positive',
                importance: 'medium',
                priority: 72,
                messageKey: 'guest_hints.messages.became_available_again',
                showInSavedSearch: true,
                source: 'saved_search',
            ));
        }

        return $hints->values();
    }

    /**
     * @param  Collection<int, GuestHintData>  $hints
     * @return Collection<int, GuestHintData>
     */
    public function limitHints(Collection $hints, int $limit): Collection
    {
        return $this->priority->sortByImportanceAndContext($hints, 'generic')->take($limit)->values();
    }

    /**
     * @param  Collection<int, GuestHintData>  $hints
     * @return Collection<int, GuestHintData>
     */
    public function filterDismissedHints(?User $user, Collection $hints): Collection
    {
        return $hints
            ->filter(fn (GuestHintData $hint): bool => $hint->isCriticalBeforeBooking() || ! $this->dismissals->isDismissed($user, $hint->key, null, 'generic'))
            ->values();
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    private function allHints(SleepingPlace $place, HintContext $context): Collection
    {
        return $this->priority->preventDuplicateSimilarHints(
            $this->snapshotHints($place)->merge($this->calculator->calculateDynamicHints($place, $context)),
        );
    }

    /**
     * @return Collection<int, GuestHintData>
     */
    private function snapshotHints(SleepingPlace $place): Collection
    {
        return ListingHintSnapshot::query()
            ->fresh()
            ->where('sleeping_place_id', $place->id)
            ->orderByDesc('priority')
            ->get()
            ->toBase()
            ->map(fn (ListingHintSnapshot $snapshot): GuestHintData => GuestHintData::fromSnapshot($snapshot))
            ->values();
    }

    /**
     * @param  Collection<int, GuestHintData>  $hints
     * @return Collection<int, GuestHintData>
     */
    private function filterDismissedHintsForContext(?User $user, SleepingPlace $place, Collection $hints, string $context): Collection
    {
        return $hints
            ->filter(fn (GuestHintData $hint): bool => $hint->isCriticalBeforeBooking() || ! $this->dismissals->isDismissed($user, $hint->key, $place, $context))
            ->values();
    }
}
