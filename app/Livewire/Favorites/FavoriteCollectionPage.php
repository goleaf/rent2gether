<?php

namespace App\Livewire\Favorites;

use App\Models\FavoriteCollection;
use App\Models\User;
use App\Services\Favorites\FavoriteCardPresenter;
use App\Services\Favorites\FavoriteCardQuery;
use App\Services\Favorites\FavoriteCollectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class FavoriteCollectionPage extends Component
{
    public int $collectionId;

    public string $filter = 'all';

    public string $sort = 'recent';

    public int $visibleCount = 20;

    /** @var list<int> */
    public array $selectedForCompare = [];

    public function mount(FavoriteCollection $favoriteCollection, FavoriteCollectionService $collections): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);
        $collections->authorize($user, $favoriteCollection);

        $this->collectionId = $favoriteCollection->id;
    }

    #[On('favorite-filter-changed')]
    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->visibleCount = 20;
        unset($this->cards);
    }

    #[On('favorite-sort-changed')]
    public function setSort(string $sort): void
    {
        $this->sort = $sort;
        unset($this->cards);
    }

    #[On('favorite-compare-toggled')]
    public function toggleCompare(int $sleepingPlaceId): void
    {
        if ($sleepingPlaceId <= 0) {
            return;
        }

        if (in_array($sleepingPlaceId, $this->selectedForCompare, true)) {
            $this->selectedForCompare = array_values(array_diff($this->selectedForCompare, [$sleepingPlaceId]));

            return;
        }

        if (count($this->selectedForCompare) >= 4) {
            $this->addError('selectedForCompare', __('favorites.compare_limit'));

            return;
        }

        $this->selectedForCompare[] = $sleepingPlaceId;
    }

    #[On('favorite-removed')]
    #[On('favorite-collections-changed')]
    public function refreshCards(): void
    {
        unset($this->collection, $this->cards);
    }

    public function loadMore(): void
    {
        $this->visibleCount = min(100, $this->visibleCount + 20);
        unset($this->cards);
    }

    public function compareSelected(): void
    {
        if (count($this->selectedForCompare) < 2) {
            $this->addError('selectedForCompare', __('favorites.compare_choose_two'));

            return;
        }

        $this->redirect(route('compare.index', [
            'locale' => app()->getLocale(),
            'places' => implode(',', array_slice($this->selectedForCompare, 0, 4)),
        ]), navigate: true);
    }

    #[Computed]
    public function collection(): FavoriteCollection
    {
        return FavoriteCollection::query()
            ->select([
                'id',
                'user_id',
                'title',
                'type',
                'description',
                'check_in_date',
                'check_out_date',
                'nights_count',
                'guests_count',
                'is_default',
                'is_archived',
                'updated_at',
            ])
            ->withCounts()
            ->findOrFail($this->collectionId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function cards(): array
    {
        $query = app(FavoriteCardQuery::class)
            ->forUser((int) auth()->id())
            ->inCollection($this->collectionId);

        $this->applyFilter($query);
        $this->applySort($query);

        return app(FavoriteCardPresenter::class)->presentMany($query->limit($this->visibleCount)->get());
    }

    public function render(): View
    {
        return view('livewire.favorites.favorite-collection-page')
            ->layout('layouts.app', [
                'title' => $this->collection->displayTitle(),
            ]);
    }

    private function applyFilter(Builder $query): void
    {
        match ($this->filter) {
            'available' => $query->where('is_currently_available', true),
            'price_changed' => $query->where('price_changed', true),
            'price_dropped' => $query->where('price_change_amount', '<', 0),
            'with_note' => $query->where(function (Builder $builder): void {
                $builder->whereNotNull('personal_note')->orWhereNotNull('note');
            }),
            'high_priority' => $query->where('priority', '>=', 8),
            'almost_chosen' => $query->where('decision_status', 'almost_chosen'),
            'backup' => $query->where('decision_status', 'backup'),
            default => null,
        };
    }

    private function applySort(Builder $query): void
    {
        match ($this->sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'available_first' => $query->orderByDesc('is_currently_available')->orderByDesc('created_at'),
            'unavailable_first' => $query->orderBy('is_currently_available')->orderByDesc('created_at'),
            'cheap_first' => $query->orderBy('current_price_per_night')->orderBy('price_per_night_snapshot'),
            'expensive_first' => $query->orderByDesc('current_price_per_night')->orderByDesc('price_per_night_snapshot'),
            'price_dropped' => $query->orderBy('price_change_amount')->orderByDesc('created_at'),
            'high_priority' => $query->orderByDesc('priority')->orderByDesc('created_at'),
            'almost_chosen' => $query->orderBy('decision_status')->orderByDesc('priority')->orderByDesc('created_at'),
            default => $query->recentlyAdded(),
        };
    }
}
