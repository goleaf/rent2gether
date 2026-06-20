<?php

namespace App\Services\Favorites;

use App\Models\FavoriteCollection;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FavoriteCollectionService
{
    /**
     * @return array<string, array{icon:string,color:string,sort:int}>
     */
    public function defaultCollections(): array
    {
        return [
            'cheap' => ['icon' => 'tag', 'color' => 'emerald', 'sort' => 10],
            'near_work' => ['icon' => 'briefcase', 'color' => 'blue', 'sort' => 20],
            'near_study' => ['icon' => 'academic-cap', 'color' => 'violet', 'sort' => 30],
            'long_stay' => ['icon' => 'calendar-days', 'color' => 'indigo', 'sort' => 40],
            'weekend' => ['icon' => 'sun', 'color' => 'amber', 'sort' => 50],
            'best_reviews' => ['icon' => 'star', 'color' => 'yellow', 'sort' => 60],
            'discuss' => ['icon' => 'chat-bubble-left-right', 'color' => 'sky', 'sort' => 70],
            'almost_chosen' => ['icon' => 'check-circle', 'color' => 'green', 'sort' => 80],
            'backup' => ['icon' => 'bookmark', 'color' => 'zinc', 'sort' => 90],
        ];
    }

    public function ensureDefaultCollections(User $user): void
    {
        foreach ($this->defaultCollections() as $type => $meta) {
            FavoriteCollection::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $type,
                    'is_default' => true,
                ],
                [
                    'title' => __('favorites.default_collections.'.$type),
                    'slug' => $type,
                    'description' => null,
                    'icon' => $meta['icon'],
                    'color' => $meta['color'],
                    'sort_order' => $meta['sort'],
                    'guests_count' => 1,
                    'currency' => 'EUR',
                    'is_pinned' => false,
                    'is_archived' => false,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): FavoriteCollection
    {
        $validated = Validator::validate($data, [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:40'],
            'type' => ['nullable', 'string', 'max:80'],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')],
            'check_in_date' => ['nullable', 'date'],
            'check_out_date' => ['nullable', 'date', 'after:check_in_date'],
            'nights_count' => ['nullable', 'integer', 'min:1', 'max:365'],
            'guests_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $title = trim((string) $validated['title']);

        return FavoriteCollection::query()->create([
            'user_id' => $user->id,
            'title' => $title,
            'slug' => $this->uniqueSlug($user, $title),
            'description' => Arr::get($validated, 'description'),
            'icon' => Arr::get($validated, 'icon', 'heart'),
            'color' => Arr::get($validated, 'color', 'emerald'),
            'type' => Arr::get($validated, 'type', 'custom') ?: 'custom',
            'city_id' => Arr::get($validated, 'city_id'),
            'check_in_date' => Arr::get($validated, 'check_in_date'),
            'check_out_date' => Arr::get($validated, 'check_out_date'),
            'nights_count' => Arr::get($validated, 'nights_count'),
            'guests_count' => (int) Arr::get($validated, 'guests_count', 1),
            'budget_min' => Arr::get($validated, 'budget_min'),
            'budget_max' => Arr::get($validated, 'budget_max'),
            'currency' => Arr::get($validated, 'currency'),
            'sort_order' => $this->nextSortOrder($user),
            'is_default' => false,
            'is_pinned' => (bool) Arr::get($validated, 'is_pinned', false),
            'is_archived' => false,
        ]);
    }

    public function rename(User $user, FavoriteCollection $collection, string $title): FavoriteCollection
    {
        $this->authorize($user, $collection);

        $title = Validator::validate(['title' => $title], [
            'title' => ['required', 'string', 'max:120'],
        ])['title'];

        $collection->update([
            'title' => trim((string) $title),
        ]);

        return $collection->refresh();
    }

    public function archive(User $user, FavoriteCollection $collection): void
    {
        $this->authorize($user, $collection);

        $collection->update(['is_archived' => true]);
    }

    public function restore(User $user, FavoriteCollection $collection): void
    {
        $this->authorize($user, $collection);

        $collection->update(['is_archived' => false]);
    }

    public function delete(User $user, FavoriteCollection $collection): void
    {
        $this->authorize($user, $collection);

        $collection->delete();
    }

    /**
     * @param  list<int>  $collectionIds
     */
    public function reorder(User $user, array $collectionIds): void
    {
        $ownedIds = FavoriteCollection::query()
            ->forUser($user)
            ->whereIn('id', $collectionIds)
            ->pluck('id')
            ->all();

        foreach (array_values(array_unique($collectionIds)) as $index => $collectionId) {
            if (! in_array($collectionId, $ownedIds, true)) {
                continue;
            }

            FavoriteCollection::query()
                ->where('user_id', $user->id)
                ->whereKey($collectionId)
                ->update(['sort_order' => ($index + 1) * 10]);
        }
    }

    public function authorize(User $user, FavoriteCollection $collection): void
    {
        if ((int) $collection->user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }

    private function uniqueSlug(User $user, string $title): string
    {
        $base = Str::slug($title) ?: 'collection';
        $slug = $base;
        $counter = 2;

        while (FavoriteCollection::query()->where('user_id', $user->id)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function nextSortOrder(User $user): int
    {
        return ((int) FavoriteCollection::query()->forUser($user)->max('sort_order')) + 10;
    }
}
