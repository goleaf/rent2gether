<?php

namespace App\Services\Favorites;

use App\Models\Favorite;
use App\Models\User;
use App\Services\Localization\SupportedContentLocales;
use Illuminate\Database\Eloquent\Builder;

class FavoriteCardQuery
{
    public function __construct(
        private readonly SupportedContentLocales $locales,
    ) {}

    /**
     * Builds the reusable card query for a user's sleeping-place favorites.
     */
    public function forUser(User|int $user): Builder
    {
        return Favorite::query()
            ->select($this->favoriteColumns())
            ->forUser($user)
            ->whereNotNull('sleeping_place_id')
            ->with($this->cardRelations());
    }

    /**
     * Builds the reusable card query for one hydrated favorite card.
     */
    public function forFavorite(User|int $user, int $favoriteId): Builder
    {
        return $this->forUser($user)->whereKey($favoriteId);
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
        $locales = $this->locales->preferred();

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
                    'cardMedia' => fn ($media) => $media
                        ->select(['id', 'mediable_type', 'mediable_id', 'disk', 'path', 'thumb_path', 'thumbnail_path', 'mobile_path', 'full_path', 'alt_text', 'is_primary', 'is_cover', 'sort_order', 'status'])
                        ->with(['translations' => fn ($translation) => $translation
                            ->select(['id', 'media_item_id', 'locale', 'caption'])
                            ->whereIn('locale', $locales)]),
                ]),
        ];
    }
}
