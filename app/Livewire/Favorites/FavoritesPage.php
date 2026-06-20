<?php

namespace App\Livewire\Favorites;

use App\Models\Favorite;
use App\Models\User;
use App\Services\Favorites\FavoriteCardPresenter;
use App\Services\Favorites\FavoriteChangeNotificationService;
use App\Services\Favorites\FavoriteCollectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class FavoritesPage extends Component
{
    public bool $createCollectionOpen = false;

    public function mount(FavoriteCollectionService $collections, FavoriteChangeNotificationService $notifications): void
    {
        $user = auth()->user();

        if ($user instanceof User) {
            $collections->ensureDefaultCollections($user);
            $notifications->notifyDueReminders($user);
        }
    }

    #[On('favorite-collections-changed')]
    #[On('favorite-removed')]
    public function refreshFavorites(): void
    {
        $this->createCollectionOpen = false;

        unset($this->summary, $this->recentCards, $this->priceChangedCards, $this->availableAgainCards, $this->reminderCards);
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function summary(): array
    {
        $query = Favorite::query()->forUser((int) auth()->id());

        return [
            'total' => (clone $query)->count(),
            'available' => (clone $query)->where('is_currently_available', true)->count(),
            'price_changed' => (clone $query)->where('price_changed', true)->count(),
            'available_again' => (clone $query)->where('became_available_again', true)->count(),
            'reminders' => (clone $query)->whereNotNull('remind_at')->whereNull('reminder_sent_at')->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function recentCards(): array
    {
        return $this->cards(
            $this->cardQuery()->recentlyAdded()->limit(6)
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function priceChangedCards(): array
    {
        return $this->cards(
            $this->cardQuery()->priceChanged()->recentlyAdded()->limit(4)
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function availableAgainCards(): array
    {
        return $this->cards(
            $this->cardQuery()->where('became_available_again', true)->recentlyAdded()->limit(4)
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function reminderCards(): array
    {
        return $this->cards(
            $this->cardQuery()->withReminder()->orderBy('remind_at')->limit(4)
        );
    }

    public function render(): View
    {
        return view('livewire.favorites.favorites-page');
    }

    private function cardQuery(): Builder
    {
        return Favorite::query()
            ->select($this->favoriteColumns())
            ->forUser((int) auth()->id())
            ->whereNotNull('sleeping_place_id')
            ->with($this->cardRelations());
    }

    /**
     * @return list<string>
     */
    private function favoriteColumns(): array
    {
        return [
            'id',
            'user_id',
            'favorite_collection_id',
            'property_id',
            'room_id',
            'sleeping_place_id',
            'collection',
            'note',
            'personal_note',
            'priority',
            'decision_status',
            'check_in',
            'check_out',
            'check_in_date',
            'check_out_date',
            'nights_count',
            'guests_count',
            'currency',
            'price_at_save',
            'price_per_night_snapshot',
            'total_price_snapshot',
            'deposit_snapshot',
            'current_price_per_night',
            'current_total_price',
            'current_deposit',
            'price_changed',
            'price_change_amount',
            'is_currently_available',
            'became_unavailable',
            'became_available_again',
            'partial_availability',
            'remind_at',
            'reminder_sent_at',
            'added_at',
            'created_at',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cardRelations(): array
    {
        $locales = array_values(array_unique(array_filter([
            app()->getLocale(),
            config('app.fallback_locale', 'en'),
            'en',
            'ru',
        ])));

        return [
            'sleepingPlace' => fn ($query) => $query
                ->select([
                    'id',
                    'room_id',
                    'property_id',
                    'type',
                    'status',
                    'place_number',
                    'display_name',
                    'base_price_per_night',
                    'deposit_amount',
                    'currency',
                ])
                ->withCount([
                    'reviews as published_reviews_count' => fn (Builder $review) => $review->visible()->guestToPlace(),
                ])
                ->withAvg([
                    'reviews as published_reviews_rating' => fn (Builder $review) => $review->visible()->guestToPlace(),
                ], 'overall_rating')
                ->with([
                    'translations' => fn ($translation) => $translation
                        ->select(['id', 'sleeping_place_id', 'locale', 'title', 'summary'])
                        ->whereIn('locale', $locales),
                    'room:id,property_id,type,status,gender_policy,beds_count,max_guests,occupied_places_count',
                    'property:id,city_id,host_user_id,type,status,city,district',
                    'property.cityModel:id,name',
                    'cardMedia:id,mediable_type,mediable_id,disk,path,thumb_path,thumbnail_path,mobile_path,full_path,alt_text,caption_en,caption_ru,is_primary,is_cover,sort_order,status',
                ]),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cards(Builder $query): array
    {
        return app(FavoriteCardPresenter::class)->presentMany($query->get());
    }
}
