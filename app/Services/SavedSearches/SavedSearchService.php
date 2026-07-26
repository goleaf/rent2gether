<?php

namespace App\Services\SavedSearches;

use App\Data\SavedSearches\SavedSearchRunResult;
use App\Models\City;
use App\Models\SavedSearch;
use App\Models\User;
use App\Support\SavedSearches\SavedSearchFormOptions;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SavedSearchService
{
    public function __construct(
        private readonly SavedSearchMatcherService $matcher,
        private readonly SavedSearchResultService $results,
        private readonly SavedSearchNotificationService $notifications,
        private readonly SavedSearchFrequencyService $frequency,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): SavedSearch
    {
        $attributes = $this->normalizedData($data);

        return SavedSearch::query()->create([
            ...$attributes,
            'user_id' => $user->id,
            'locale' => $attributes['locale'] ?? app()->getLocale(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, SavedSearch $search, array $data): SavedSearch
    {
        $this->authorize($user, $search);

        $search->update($this->normalizedData($data, $search));

        return $search->refresh();
    }

    public function pause(User $user, SavedSearch $search): SavedSearch
    {
        $this->authorize($user, $search);

        $search->update([
            'status' => 'paused',
            'is_active' => false,
        ]);

        return $search->refresh();
    }

    public function resume(User $user, SavedSearch $search): SavedSearch
    {
        $this->authorize($user, $search);

        $search->update([
            'status' => 'active',
            'is_active' => true,
            'next_check_at' => $this->frequency->calculateNextCheckAt($search),
        ]);

        return $search->refresh();
    }

    public function archive(User $user, SavedSearch $search): SavedSearch
    {
        $this->authorize($user, $search);

        $search->update([
            'status' => 'archived',
            'is_active' => false,
        ]);

        return $search->refresh();
    }

    public function delete(User $user, SavedSearch $search): void
    {
        $this->authorize($user, $search);

        $search->delete();
    }

    public function runNow(User $user, SavedSearch $search): SavedSearchRunResult
    {
        $this->authorize($user, $search);

        $search->loadMissing('user:id');

        if ($this->datesHavePassed($search)) {
            $search->forceFill(['status' => 'expired', 'is_active' => false])->save();

            $this->notifications->createInAppNotification($user, $search, 'saved_search_expiring_dates');

            return new SavedSearchRunResult(0, 0, 0, 0, 'saved_searches.messages.expired');
        }

        $this->results->refreshExistingResults($search);

        $matches = $this->matcher->findMatches($search->refresh());
        $this->results->syncMatches($search, $matches);

        $counts = $this->currentCounts($search->refresh());

        $search->forceFill([
            'last_checked_at' => now(),
            'next_check_at' => $this->frequency->calculateNextCheckAt($search),
            'new_matches_count' => $counts['new'],
            'price_drops_count' => $counts['price_drops'],
            'available_again_count' => $counts['available_again'],
            'last_results_hash' => $this->resultsHash($search),
        ])->save();

        $search = $search->refresh();
        $this->notifications->notifyNewMatches($search);
        $this->notifications->notifyPriceDrops($search);
        $this->notifications->notifyAvailableAgain($search);
        $this->notifications->notifyBetterMatches($search);

        return new SavedSearchRunResult(
            matchedCount: $matches->count(),
            newMatchesCount: $counts['new'],
            priceDropsCount: $counts['price_drops'],
            availableAgainCount: $counts['available_again'],
        );
    }

    public function checkDueForUser(User $user, int $limit = 5): void
    {
        SavedSearch::query()
            ->forUser($user)
            ->active()
            ->withNotificationsEnabled()
            ->orderBy('next_check_at')
            ->limit($limit)
            ->get()
            ->each(function (SavedSearch $search) use ($user): void {
                if ($this->frequency->shouldCheck($search)) {
                    $this->runNow($user, $search);
                }
            });
    }

    private function authorize(User $user, SavedSearch $search): void
    {
        if ((int) $search->user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizedData(array $data, ?SavedSearch $existing = null): array
    {
        $validated = Validator::make($data, [
            'title' => ['nullable', 'string', 'max:160'],
            'name' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::in(SavedSearchFormOptions::statuses())],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'city_name' => ['nullable', 'string', 'max:160'],
            'district' => ['nullable', 'string', 'max:160'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'radius_meters' => ['nullable', 'integer', 'min:100', 'max:100000'],
            'check_in_date' => ['nullable', 'required_with:check_out_date', 'date'],
            'check_out_date' => ['nullable', 'required_with:check_in_date', 'date', 'after:check_in_date'],
            'guests_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'flexible_dates' => ['nullable', 'boolean'],
            'flexible_days' => ['nullable', 'integer', 'min:0', 'max:14'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'total_budget_max' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'room_type' => ['nullable', Rule::in(SavedSearchFormOptions::roomTypes())],
            'room_types' => ['nullable', 'array'],
            'room_types.*' => ['string', Rule::in(SavedSearchFormOptions::roomTypes())],
            'sleeping_place_type' => ['nullable', Rule::in(SavedSearchFormOptions::sleepingPlaceTypes())],
            'sleeping_place_types' => ['nullable', 'array'],
            'sleeping_place_types.*' => ['string', Rule::in(SavedSearchFormOptions::sleepingPlaceTypes())],
            'room_gender_policy' => ['nullable', Rule::in(SavedSearchFormOptions::roomGenderPolicies())],
            'required_amenity_ids' => ['nullable', 'array'],
            'required_amenities' => ['nullable', 'array'],
            'required_amenities.*' => ['string', Rule::in(SavedSearchFormOptions::requiredAmenities())],
            'preferred_amenity_ids' => ['nullable', 'array'],
            'excluded_rule_ids' => ['nullable', 'array'],
            'excluded_conditions' => ['nullable', 'array'],
            'excluded_conditions.*' => ['string', Rule::in(SavedSearchFormOptions::excludedConditions())],
            'only_verified_hosts' => ['nullable', 'boolean'],
            'only_verified_places' => ['nullable', 'boolean'],
            'only_instant_booking' => ['nullable', 'boolean'],
            'instant_booking' => ['nullable', 'boolean'],
            'only_with_reviews' => ['nullable', 'boolean'],
            'free_cancellation_only' => ['nullable', 'boolean'],
            'no_deposit_only' => ['nullable', 'boolean'],
            'lower_bunk_only' => ['nullable', 'boolean'],
            'exclude_upper_bunk' => ['nullable', 'boolean'],
            'exclude_sofa' => ['nullable', 'boolean'],
            'exclude_mattress' => ['nullable', 'boolean'],
            'require_locker' => ['nullable', 'boolean'],
            'require_workspace' => ['nullable', 'boolean'],
            'require_wifi' => ['nullable', 'boolean'],
            'require_kitchen' => ['nullable', 'boolean'],
            'require_washing_machine' => ['nullable', 'boolean'],
            'require_late_check_in' => ['nullable', 'boolean'],
            'avoid_smoking' => ['nullable', 'boolean'],
            'avoid_pets' => ['nullable', 'boolean'],
            'avoid_mixed_room' => ['nullable', 'boolean'],
            'notify_new_matches' => ['nullable', 'boolean'],
            'notify_price_drops' => ['nullable', 'boolean'],
            'notify_price_increases' => ['nullable', 'boolean'],
            'notify_available_again' => ['nullable', 'boolean'],
            'notify_better_match' => ['nullable', 'boolean'],
            'notify_new_places' => ['nullable', 'boolean'],
            'notify_price_drop' => ['nullable', 'boolean'],
            'notify_available' => ['nullable', 'boolean'],
            'notification_frequency' => ['nullable', Rule::in(SavedSearchFormOptions::notificationFrequencies())],
            'notify_frequency' => ['nullable', Rule::in(SavedSearchFormOptions::notificationFrequencies())],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
        ])
            ->after(function ($validator) use ($data, $existing): void {
                $budgetMin = $this->nullableMoney($this->firstInputValue($data, ['budget_min', 'price_min'], $existing?->budget_min ?? $existing?->price_min));
                $budgetMax = $this->nullableMoney($this->firstInputValue($data, ['budget_max', 'price_max'], $existing?->budget_max ?? $existing?->price_max));

                if ($budgetMin !== null && $budgetMax !== null && $budgetMax < $budgetMin) {
                    $validator->errors()->add('budget_max', __('saved_searches.validation.budget_order'));
                }
            })
            ->validate();

        $title = Str::limit(trim((string) ($data['title'] ?? $data['name'] ?? $existing?->title ?? $existing?->name ?? __('saved_searches.defaults.title'))), 160, '');
        $cityId = $this->nullableInt($this->firstInputValue($data, ['city_id'], $existing?->city_id));
        $cityName = $this->nullableString($this->firstInputValue($data, ['city_name'], $existing?->city));

        if ($cityId && ! $cityName) {
            $cityName = City::query()->whereKey($cityId)->value('name');
        }

        $checkIn = $this->firstInputValue(
            $data,
            ['check_in_date', 'check_in'],
            $existing?->check_in_date?->toDateString() ?? $existing?->check_in?->toDateString(),
        );
        $checkOut = $this->firstInputValue(
            $data,
            ['check_out_date', 'check_out'],
            $existing?->check_out_date?->toDateString() ?? $existing?->check_out?->toDateString(),
        );
        $counts = $this->dateCounts($checkIn, $checkOut);
        $budgetMin = $this->nullableMoney($this->firstInputValue($data, ['budget_min', 'price_min'], $existing?->budget_min ?? $existing?->price_min));
        $budgetMax = $this->nullableMoney($this->firstInputValue($data, ['budget_max', 'price_max'], $existing?->budget_max ?? $existing?->price_max));
        $frequency = (string) ($data['notification_frequency'] ?? $data['notify_frequency'] ?? $existing?->notification_frequency ?? 'on_visit');
        $status = (string) ($validated['status'] ?? $existing?->status ?? 'active');
        $roomTypes = $this->listFromData($data, 'room_types', 'room_type', $existing?->room_types_json ?? []);
        $sleepingPlaceTypes = $this->listFromData($data, 'sleeping_place_types', 'sleeping_place_type', $existing?->sleeping_place_types_json ?? []);
        $requiredAmenities = $this->selectedOptionKeys(
            data: $data,
            listKey: 'required_amenities',
            columns: SavedSearchFormOptions::requiredAmenityColumns(),
            existingKeys: (array) ($existing?->amenities ?? []),
            existing: $existing,
        );
        $excludedConditions = $this->selectedOptionKeys(
            data: $data,
            listKey: 'excluded_conditions',
            columns: SavedSearchFormOptions::excludedConditionColumns(),
            existingKeys: (array) ($existing?->excluded_conditions_json ?? []),
            existing: $existing,
        );

        return [
            'title' => $title,
            'name' => $title,
            'description' => $this->nullableString($this->firstInputValue($validated, ['description'], $existing?->description)),
            'status' => $status,
            'city_id' => $cityId,
            'city' => $cityName,
            'district' => $this->nullableString($this->firstInputValue($validated, ['district'], $existing?->district)),
            'location_text' => $this->nullableString($this->firstInputValue($validated, ['location_text'], $existing?->location_text)),
            'radius_meters' => $this->nullableInt($this->firstInputValue($validated, ['radius_meters'], $existing?->radius_meters)),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'nights' => $counts['nights'],
            'nights_count' => $counts['nights'],
            'calendar_days_count' => $counts['calendar_days'],
            'guests_count' => max(1, (int) ($data['guests_count'] ?? $existing?->guests_count ?? 1)),
            'flexible_dates' => (bool) ($data['flexible_dates'] ?? $existing?->flexible_dates ?? false),
            'flexible_days' => $validated['flexible_days'] ?? $existing?->flexible_days,
            'price_min' => $budgetMin,
            'price_max' => $budgetMax,
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'total_budget_max' => $this->nullableMoney($this->firstInputValue($data, ['total_budget_max'], $existing?->total_budget_max)),
            'currency' => strtoupper((string) ($data['currency'] ?? $existing?->currency ?? 'EUR')),
            'room_type' => $this->nullableString($this->firstInputValue($data, ['room_type'], $existing?->room_type)),
            'bed_type' => $this->nullableString($this->firstInputValue($data, ['sleeping_place_type', 'bed_type'], $existing?->bed_type)),
            'amenities' => $requiredAmenities,
            'filters' => $this->filtersJson($requiredAmenities, $excludedConditions),
            'filters_json' => $this->filtersJson($requiredAmenities, $excludedConditions),
            'room_types_json' => $roomTypes,
            'sleeping_place_types_json' => $sleepingPlaceTypes,
            'room_gender_policy' => $this->nullableString($this->firstInputValue($validated, ['room_gender_policy'], $existing?->room_gender_policy)),
            'required_amenity_ids_json' => $this->intList($data['required_amenity_ids'] ?? $existing?->required_amenity_ids_json ?? []),
            'preferred_amenity_ids_json' => $this->intList($data['preferred_amenity_ids'] ?? $existing?->preferred_amenity_ids_json ?? []),
            'excluded_rule_ids_json' => $this->intList($data['excluded_rule_ids'] ?? $existing?->excluded_rule_ids_json ?? []),
            'excluded_conditions_json' => $excludedConditions,
            'only_verified_hosts' => $this->boolValue($data, 'only_verified_hosts', $existing, false),
            'only_verified_places' => $this->boolValue($data, 'only_verified_places', $existing, false),
            'only_instant_booking' => $this->boolValue($data, 'only_instant_booking', $existing, false, 'instant_booking'),
            'only_with_reviews' => $this->boolValue($data, 'only_with_reviews', $existing, false),
            'free_cancellation_only' => $this->boolValue($data, 'free_cancellation_only', $existing, false),
            'no_deposit_only' => $this->boolValue($data, 'no_deposit_only', $existing, false),
            'max_deposit' => $this->nullableMoney($data['max_deposit'] ?? $existing?->max_deposit),
            'min_rating' => $this->nullableDecimal($data['min_rating'] ?? $existing?->min_rating),
            'min_cleanliness_rating' => $this->nullableDecimal($data['min_cleanliness_rating'] ?? $existing?->min_cleanliness_rating),
            'min_safety_rating' => $this->nullableDecimal($data['min_safety_rating'] ?? $existing?->min_safety_rating),
            'min_host_rating' => $this->nullableDecimal($data['min_host_rating'] ?? $existing?->min_host_rating),
            'max_people_in_room' => isset($data['max_people_in_room']) ? (int) $data['max_people_in_room'] : $existing?->max_people_in_room,
            'lower_bunk_only' => $this->boolValue($data, 'lower_bunk_only', $existing, false),
            'exclude_upper_bunk' => $this->boolValue($data, 'exclude_upper_bunk', $existing, false),
            'exclude_sofa' => $this->boolValue($data, 'exclude_sofa', $existing, false),
            'exclude_mattress' => $this->boolValue($data, 'exclude_mattress', $existing, false),
            'require_locker' => in_array('locker', $requiredAmenities, true),
            'require_workspace' => in_array('workspace', $requiredAmenities, true),
            'require_wifi' => in_array('wifi', $requiredAmenities, true),
            'require_kitchen' => in_array('kitchen', $requiredAmenities, true),
            'require_washing_machine' => in_array('washing_machine', $requiredAmenities, true),
            'require_late_check_in' => $this->boolValue($data, 'require_late_check_in', $existing, false),
            'avoid_smoking' => in_array('smoking', $excludedConditions, true),
            'avoid_pets' => in_array('pets', $excludedConditions, true),
            'avoid_mixed_room' => in_array('mixed_room', $excludedConditions, true),
            'notify_new_places' => $this->boolValue($data, 'notify_new_places', $existing, true, 'notify_new_matches'),
            'notify_price_drop' => $this->boolValue($data, 'notify_price_drop', $existing, true, 'notify_price_drops'),
            'notify_available' => $this->boolValue($data, 'notify_available', $existing, true, 'notify_available_again'),
            'notify_frequency' => $frequency,
            'notify_new_matches' => $this->boolValue($data, 'notify_new_matches', $existing, true),
            'notify_price_drops' => $this->boolValue($data, 'notify_price_drops', $existing, true),
            'notify_price_increases' => $this->boolValue($data, 'notify_price_increases', $existing, false),
            'notify_available_again' => $this->boolValue($data, 'notify_available_again', $existing, true),
            'notify_better_match' => $this->boolValue($data, 'notify_better_match', $existing, true),
            'notification_frequency' => $frequency,
            'quiet_hours_enabled' => $this->boolValue($data, 'quiet_hours_enabled', $existing, true),
            'quiet_hours_start' => $data['quiet_hours_start'] ?? $existing?->quiet_hours_start ?? '22:00',
            'quiet_hours_end' => $data['quiet_hours_end'] ?? $existing?->quiet_hours_end ?? '07:00',
            'is_active' => $status === 'active',
        ];
    }

    /**
     * @return array{nights:?int,calendar_days:?int}
     */
    private function dateCounts(mixed $checkIn, mixed $checkOut): array
    {
        if (! $checkIn || ! $checkOut) {
            return ['nights' => null, 'calendar_days' => null];
        }

        $start = CarbonImmutable::parse($checkIn)->startOfDay();
        $end = CarbonImmutable::parse($checkOut)->startOfDay();

        if ($end->lessThanOrEqualTo($start)) {
            return ['nights' => null, 'calendar_days' => null];
        }

        $nights = (int) $start->diffInDays($end);

        return ['nights' => $nights, 'calendar_days' => $nights + 1];
    }

    /**
     * @return array{new:int,price_drops:int,available_again:int}
     */
    private function currentCounts(SavedSearch $search): array
    {
        return [
            'new' => $search->results()->newMatches()->count(),
            'price_drops' => $search->results()->priceDropped()->count(),
            'available_again' => $search->results()->availableAgain()->count(),
        ];
    }

    private function datesHavePassed(SavedSearch $search): bool
    {
        $checkOut = $search->check_out_date ?: $search->check_out;

        return $checkOut !== null && CarbonImmutable::parse($checkOut)->endOfDay()->isPast();
    }

    private function resultsHash(SavedSearch $search): string
    {
        $ids = $search->results()
            ->orderBy('sleeping_place_id')
            ->pluck('sleeping_place_id')
            ->implode(',');

        return sha1($ids.'|'.$search->new_matches_count.'|'.$search->price_drops_count.'|'.$search->available_again_count);
    }

    private function nullableMoney(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : round((float) $value, 2);
    }

    private function nullableDecimal(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : round((float) $value, 2);
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  list<string>  $keys
     */
    private function firstInputValue(array $data, array $keys, mixed $default): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
        }

        return $default;
    }

    /**
     * @return list<string>
     */
    private function list(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(array_filter(array_map('strval', (array) $value)));
    }

    /**
     * @return list<int>
     */
    private function intList(mixed $value): array
    {
        return array_values(array_filter(array_map('intval', (array) $value)));
    }

    /**
     * @param  list<string>  $existing
     * @return list<string>
     */
    private function listFromData(array $data, string $listKey, string $singleKey, array $existing): array
    {
        if (array_key_exists($listKey, $data) || array_key_exists($singleKey, $data)) {
            return $this->list($data[$listKey] ?? ($data[$singleKey] ?? null));
        }

        return array_values(array_filter(array_map('strval', $existing)));
    }

    /**
     * @param  array<string, string>  $columns
     * @param  list<string>  $existingKeys
     * @return list<string>
     */
    private function selectedOptionKeys(
        array $data,
        string $listKey,
        array $columns,
        array $existingKeys,
        ?SavedSearch $existing,
    ): array {
        $keys = array_values(array_filter(array_map('strval', $existingKeys)));

        if ($existing instanceof SavedSearch) {
            foreach ($columns as $key => $column) {
                if ((bool) $existing->{$column}) {
                    $keys[] = $key;
                }
            }
        }

        if (array_key_exists($listKey, $data)) {
            $keys = $this->list($data[$listKey]);
        }

        foreach ($columns as $key => $column) {
            if (! array_key_exists($column, $data)) {
                continue;
            }

            $keys = array_values(array_diff($keys, [$key]));

            if ((bool) $data[$column]) {
                $keys[] = $key;
            }
        }

        return array_values(array_intersect(array_unique($keys), array_keys($columns)));
    }

    /**
     * @param  list<string>  $requiredAmenities
     * @param  list<string>  $excludedConditions
     * @return array<string, bool>
     */
    private function filtersJson(array $requiredAmenities, array $excludedConditions): array
    {
        return collect([...$requiredAmenities, ...array_map(fn (string $condition): string => 'avoid_'.$condition, $excludedConditions)])
            ->mapWithKeys(fn (string $key): array => [$key => true])
            ->all();
    }

    private function boolValue(array $data, string $key, ?SavedSearch $existing, bool $default, ?string $alias = null): bool
    {
        if (array_key_exists($key, $data)) {
            return (bool) $data[$key];
        }

        if ($alias !== null && array_key_exists($alias, $data)) {
            return (bool) $data[$alias];
        }

        if ($existing instanceof SavedSearch) {
            return (bool) $existing->{$key};
        }

        return $default;
    }
}
