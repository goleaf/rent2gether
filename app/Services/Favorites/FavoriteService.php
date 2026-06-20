<?php

namespace App\Services\Favorites;

use App\Data\Favorites\FavoriteContext;
use App\Data\Favorites\FavoriteToggleResult;
use App\Models\Favorite;
use App\Models\FavoriteCollection;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FavoriteService
{
    public function __construct(
        private readonly FavoriteCollectionService $collections,
        private readonly FavoriteSnapshotService $snapshots,
    ) {}

    public function toggle(User $user, int $sleepingPlaceId, FavoriteContext $context): FavoriteToggleResult
    {
        $favorite = Favorite::query()
            ->forUser($user)
            ->where('sleeping_place_id', $sleepingPlaceId)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return new FavoriteToggleResult(false, null, 'favorites.removed');
        }

        return new FavoriteToggleResult(
            selected: true,
            favorite: $this->add($user, $sleepingPlaceId, $context->collectionId, $context),
            messageKey: 'favorites.saved',
        );
    }

    public function add(User $user, int $sleepingPlaceId, ?int $collectionId, FavoriteContext $context): Favorite
    {
        $this->collections->ensureDefaultCollections($user);

        $place = SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'status',
                'place_number',
                'display_name',
                'base_price_per_night',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'cleaning_fee',
                'deposit_amount',
                'currency',
                'min_nights',
                'max_nights',
                'max_guests',
            ])
            ->with([
                'room:id,property_id,status',
                'property:id,status',
            ])
            ->findOrFail($sleepingPlaceId);

        $collection = $this->collectionForAdd($user, $collectionId);
        $snapshot = $this->snapshots->createSnapshot($place, $context, $user);

        return DB::transaction(function () use ($user, $place, $collection, $snapshot, $context): Favorite {
            $favorite = Favorite::query()
                ->where('user_id', $user->id)
                ->where('sleeping_place_id', $place->id)
                ->first();

            $attributes = [
                ...$snapshot,
                'user_id' => $user->id,
                'favorite_collection_id' => $collection?->id,
                'collection' => $collection?->slug ?: $collection?->title ?: 'default',
                'priority' => $this->priorityValue($context->priority),
            ];

            if ($favorite) {
                $favorite->update($attributes);

                return $favorite->refresh();
            }

            return Favorite::query()->create([
                ...$attributes,
                'bed_id' => null,
            ]);
        });
    }

    public function remove(User $user, int $sleepingPlaceId): void
    {
        Favorite::query()
            ->forUser($user)
            ->where('sleeping_place_id', $sleepingPlaceId)
            ->delete();
    }

    public function removeFavorite(User $user, int $favoriteId): void
    {
        $favorite = $this->favoriteForUser($user, $favoriteId);

        $favorite->delete();
    }

    public function moveToCollection(User $user, int $favoriteId, int $collectionId): Favorite
    {
        $favorite = $this->favoriteForUser($user, $favoriteId);
        $collection = $this->collectionForUser($user, $collectionId);

        $favorite->update([
            'favorite_collection_id' => $collection->id,
            'collection' => $collection->slug ?: $collection->title,
        ]);

        return $favorite->refresh();
    }

    public function copyToCollection(User $user, int $favoriteId, int $collectionId): Favorite
    {
        return $this->moveToCollection($user, $favoriteId, $collectionId);
    }

    public function updateNote(User $user, int $favoriteId, ?string $note): Favorite
    {
        $favorite = $this->favoriteForUser($user, $favoriteId);
        $note = $note === null ? null : Str::limit(trim($note), 1000, '');

        $favorite->update([
            'note' => $note,
            'personal_note' => $note,
        ]);

        return $favorite->refresh();
    }

    public function updatePriority(User $user, int $favoriteId, string $priority): Favorite
    {
        $favorite = $this->favoriteForUser($user, $favoriteId);

        $favorite->update(['priority' => $this->priorityValue($priority)]);

        return $favorite->refresh();
    }

    public function updateDecisionStatus(User $user, int $favoriteId, string $status): Favorite
    {
        $status = Validator::validate(['status' => $status], [
            'status' => ['required', 'string', 'max:80'],
        ])['status'];
        $favorite = $this->favoriteForUser($user, $favoriteId);

        $favorite->update(['decision_status' => $status]);

        return $favorite->refresh();
    }

    public function favoriteForUser(User $user, int $favoriteId): Favorite
    {
        $favorite = Favorite::query()->whereKey($favoriteId)->firstOrFail();

        if ((int) $favorite->user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }

        return $favorite;
    }

    private function collectionForAdd(User $user, ?int $collectionId): ?FavoriteCollection
    {
        if ($collectionId) {
            return $this->collectionForUser($user, $collectionId);
        }

        return FavoriteCollection::query()
            ->forUser($user)
            ->active()
            ->default()
            ->ordered()
            ->first();
    }

    private function collectionForUser(User $user, int $collectionId): FavoriteCollection
    {
        $collection = FavoriteCollection::query()->whereKey($collectionId)->firstOrFail();

        $this->collections->authorize($user, $collection);

        return $collection;
    }

    private function priorityValue(string $priority): int
    {
        return match ($priority) {
            'urgent', 'high', 'almost_chosen' => 9,
            'medium', 'normal', 'saved' => 5,
            'low', 'backup' => 1,
            default => max(0, min(9, (int) $priority)),
        };
    }
}
