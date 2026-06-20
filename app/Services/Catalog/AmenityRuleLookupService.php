<?php

namespace App\Services\Catalog;

use App\Models\Amenity;
use App\Models\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class AmenityRuleLookupService
{
    private const CACHE_SECONDS = 86400;

    /**
     * @param  list<string>  $categories
     * @return list<array{category:string,category_label:string,options:list<array{id:int,slug:string,category:string,label:string,description:?string}>}>
     */
    public function amenityGroups(?string $locale = null, ?string $search = null, array $categories = [], int $limit = 80): array
    {
        return $this->groupOptions(
            options: $this->amenityOptions($locale, $search, $categories, $limit),
            labelPrefix: 'host.amenities.categories',
            categoryOrder: AmenityRuleCatalog::AMENITY_CATEGORIES,
        );
    }

    /**
     * @param  list<string>  $categories
     * @return list<array{category:string,category_label:string,options:list<array{id:int,slug:string,category:string,label:string,description:?string}>}>
     */
    public function ruleGroups(?string $locale = null, ?string $search = null, array $categories = [], int $limit = 80): array
    {
        return $this->groupOptions(
            options: $this->ruleOptions($locale, $search, $categories, $limit),
            labelPrefix: 'host.rules.categories',
            categoryOrder: AmenityRuleCatalog::RULE_CATEGORIES,
        );
    }

    /**
     * @param  list<string>  $categories
     * @return list<array{id:int,slug:string,category:string,label:string,description:?string}>
     */
    public function amenityOptions(?string $locale = null, ?string $search = null, array $categories = [], int $limit = 80): array
    {
        return $this->filterOptions(
            options: $this->cachedAmenities($locale ?: app()->getLocale()),
            search: $search,
            categories: $categories,
            limit: $limit,
        );
    }

    /**
     * @param  list<string>  $categories
     * @return list<array{id:int,slug:string,category:string,label:string,description:?string}>
     */
    public function ruleOptions(?string $locale = null, ?string $search = null, array $categories = [], int $limit = 80): array
    {
        return $this->filterOptions(
            options: $this->cachedRules($locale ?: app()->getLocale()),
            search: $search,
            categories: $categories,
            limit: $limit,
        );
    }

    public static function clearAmenityCache(): void
    {
        foreach (self::localesForCache() as $locale) {
            Cache::forget(self::amenityCacheKey($locale));
        }
    }

    public static function clearRuleCache(): void
    {
        foreach (self::localesForCache() as $locale) {
            Cache::forget(self::ruleCacheKey($locale));
        }
    }

    public static function clearAll(): void
    {
        self::clearAmenityCache();
        self::clearRuleCache();
    }

    /**
     * @return Collection<int, array{id:int,slug:string,category:string,label:string,description:?string,search:string}>
     */
    private function cachedAmenities(string $locale): Collection
    {
        return collect(Cache::remember(self::amenityCacheKey($locale), self::CACHE_SECONDS, function () use ($locale): array {
            return Amenity::query()
                ->select(['id', 'slug', 'category', 'status'])
                ->visible()
                ->with(['translations' => fn ($query) => $query
                    ->select(['id', 'amenity_id', 'locale', 'name', 'description'])
                    ->whereIn('locale', $this->translationLocales($locale))])
                ->orderBy('category')
                ->orderBy('slug')
                ->get()
                ->map(fn (Amenity $amenity): array => $this->toOption(
                    translations: $amenity->translations,
                    ownerKey: 'amenity_id',
                    locale: $locale,
                    id: $amenity->id,
                    slug: $amenity->slug,
                    category: $amenity->category ?: 'property',
                ))
                ->all();
        }));
    }

    /**
     * @return Collection<int, array{id:int,slug:string,category:string,label:string,description:?string,search:string}>
     */
    private function cachedRules(string $locale): Collection
    {
        return collect(Cache::remember(self::ruleCacheKey($locale), self::CACHE_SECONDS, function () use ($locale): array {
            return Rule::query()
                ->select(['id', 'slug', 'category', 'status'])
                ->visible()
                ->with(['translations' => fn ($query) => $query
                    ->select(['id', 'rule_id', 'locale', 'name', 'description'])
                    ->whereIn('locale', $this->translationLocales($locale))])
                ->orderBy('category')
                ->orderBy('slug')
                ->get()
                ->map(fn (Rule $rule): array => $this->toOption(
                    translations: $rule->translations,
                    ownerKey: 'rule_id',
                    locale: $locale,
                    id: $rule->id,
                    slug: $rule->slug,
                    category: $rule->category ?: 'shared_room_behavior',
                ))
                ->all();
        }));
    }

    /**
     * @param  Collection<int, object>  $translations
     * @return array{id:int,slug:string,category:string,label:string,description:?string,search:string}
     */
    private function toOption(Collection $translations, string $ownerKey, string $locale, int $id, string $slug, string $category): array
    {
        $fallbackLocale = config('app.fallback_locale', 'en');
        $translation = $translations->firstWhere('locale', $locale)
            ?: $translations->firstWhere('locale', $fallbackLocale)
            ?: $translations->first();
        $label = $translation?->name ?: __($ownerKey === 'rule_id' ? 'host.lookup_fallback.rule' : 'host.lookup_fallback.amenity');
        $description = $translation?->description;

        return [
            'id' => $id,
            'slug' => $slug,
            'category' => $category,
            'label' => $label,
            'description' => $description,
            'search' => AmenityRuleCatalog::normalize($label.' '.$slug.' '.$description.' '.$category.' '.$ownerKey),
        ];
    }

    /**
     * @param  Collection<int, array{id:int,slug:string,category:string,label:string,description:?string,search:string}>  $options
     * @param  list<string>  $categories
     * @return list<array{id:int,slug:string,category:string,label:string,description:?string}>
     */
    private function filterOptions(Collection $options, ?string $search, array $categories, int $limit): array
    {
        $normalizedSearch = AmenityRuleCatalog::normalize($search);
        $categorySet = collect($categories)
            ->filter()
            ->map(fn (string $category): string => $category)
            ->values();

        return $options
            ->when($categorySet->isNotEmpty(), fn (Collection $items): Collection => $items
                ->whereIn('category', $categorySet->all()))
            ->when($normalizedSearch !== '', fn (Collection $items): Collection => $items
                ->filter(fn (array $option): bool => str_contains($option['search'], $normalizedSearch))
                ->sortBy(fn (array $option): string => (str_starts_with($option['search'], $normalizedSearch) ? '0' : '1').$option['label']))
            ->take(max(1, min(120, $limit)))
            ->map(fn (array $option): array => [
                'id' => $option['id'],
                'slug' => $option['slug'],
                'category' => $option['category'],
                'label' => $option['label'],
                'description' => $option['description'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{id:int,slug:string,category:string,label:string,description:?string}>  $options
     * @param  list<string>  $categoryOrder
     * @return list<array{category:string,category_label:string,options:list<array{id:int,slug:string,category:string,label:string,description:?string}>}>
     */
    private function groupOptions(array $options, string $labelPrefix, array $categoryOrder): array
    {
        $order = array_flip($categoryOrder);

        return collect($options)
            ->groupBy('category')
            ->sortKeysUsing(fn (string $a, string $b): int => ($order[$a] ?? 999) <=> ($order[$b] ?? 999))
            ->map(fn (Collection $items, string $category): array => [
                'category' => $category,
                'category_label' => __($labelPrefix.'.'.$category),
                'options' => $items->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function translationLocales(string $locale): array
    {
        return array_values(array_unique(array_filter([
            $locale,
            config('app.fallback_locale', 'en'),
            'en',
            'ru',
        ])));
    }

    /**
     * @return list<string>
     */
    private static function localesForCache(): array
    {
        return array_values(array_unique(array_filter([
            ...(array) config('localization.supported_locales', ['en', 'ru']),
            config('app.fallback_locale', 'en'),
            app()->getLocale(),
        ])));
    }

    private static function amenityCacheKey(string $locale): string
    {
        return 'catalog.amenities.'.$locale;
    }

    private static function ruleCacheKey(string $locale): string
    {
        return 'catalog.rules.'.$locale;
    }
}
