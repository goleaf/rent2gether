<?php

namespace App\Livewire\SavedSearches;

use App\Models\SavedSearch;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class SavedSearchesPage extends Component
{
    public int $visibleCount = 20;

    public function mount(SavedSearchService $savedSearches): void
    {
        $user = auth()->user();

        if ($user instanceof User) {
            $savedSearches->checkDueForUser($user);
        }
    }

    #[On('saved-search-created')]
    #[On('saved-search-updated')]
    public function refreshSearches(): void
    {
        unset($this->summary, $this->cards);
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function summary(): array
    {
        $query = SavedSearch::query()->forUser((int) auth()->id());

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->active()->count(),
            'new' => (clone $query)->where('new_matches_count', '>', 0)->count(),
            'price_drops' => (clone $query)->where('price_drops_count', '>', 0)->count(),
            'available_again' => (clone $query)->where('available_again_count', '>', 0)->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function cards(): array
    {
        return SavedSearch::query()
            ->select([
                'id',
                'user_id',
                'city_id',
                'title',
                'name',
                'status',
                'city',
                'district',
                'check_in_date',
                'check_out_date',
                'nights_count',
                'budget_min',
                'budget_max',
                'currency',
                'notification_frequency',
                'last_checked_at',
                'new_matches_count',
                'price_drops_count',
                'available_again_count',
                'is_active',
                'created_at',
            ])
            ->forUser((int) auth()->id())
            ->with('cityModel:id,name')
            ->orderByDesc('is_active')
            ->orderBy('status')
            ->orderByDesc('new_matches_count')
            ->orderByDesc('price_drops_count')
            ->orderByDesc('available_again_count')
            ->orderByDesc('created_at')
            ->limit($this->visibleCount)
            ->get()
            ->map(fn (SavedSearch $search): array => $this->card($search))
            ->all();
    }

    public function render(): View
    {
        return view('livewire.saved-searches.saved-searches-page')
            ->layout('layouts.app', [
                'title' => __('saved_searches.title'),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function card(SavedSearch $search): array
    {
        $currency = strtoupper($search->currency ?: 'EUR');

        return [
            'id' => $search->id,
            'title' => $search->displayTitle(),
            'location' => collect([$search->cityModel?->name ?: $search->city, $search->district])->filter()->implode(', '),
            'dates' => $search->check_in_date && $search->check_out_date
                ? $search->check_in_date->format('d.m').' - '.$search->check_out_date->format('d.m')
                : __('saved_searches.no_dates'),
            'nights' => $search->nights_count,
            'budget' => $search->budget_max !== null
                ? Number::currency((float) $search->budget_max, $currency, app()->getLocale())
                : __('saved_searches.no_budget'),
            'status' => $search->status,
            'status_label' => __('saved_searches.statuses.'.($search->status ?: 'active')),
            'frequency' => __('saved_searches.frequency.'.($search->notification_frequency ?: 'on_visit')),
            'new_matches_count' => $search->new_matches_count,
            'price_drops_count' => $search->price_drops_count,
            'available_again_count' => $search->available_again_count,
            'last_checked' => $search->last_checked_at?->diffForHumans() ?: __('saved_searches.never_checked'),
            'href' => route('saved-searches.show', ['locale' => app()->getLocale(), 'savedSearch' => $search]),
        ];
    }
}
