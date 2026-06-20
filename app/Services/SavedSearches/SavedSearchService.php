<?php

namespace App\Services\SavedSearches;

use App\Data\SavedSearches\SavedSearchRunResult;
use App\Models\City;
use App\Models\SavedSearch;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        $validated = Validator::validate($data, [
            'title' => ['nullable', 'string', 'max:160'],
            'name' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'city_name' => ['nullable', 'string', 'max:160'],
            'district' => ['nullable', 'string', 'max:160'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'radius_meters' => ['nullable', 'integer', 'min:100', 'max:100000'],
            'check_in_date' => ['nullable', 'date'],
            'check_out_date' => ['nullable', 'date', 'after:check_in_date'],
            'guests_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'flexible_dates' => ['nullable', 'boolean'],
            'flexible_days' => ['nullable', 'integer', 'min:0', 'max:14'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'total_budget_max' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'room_type' => ['nullable', 'string', 'max:80'],
            'sleeping_place_type' => ['nullable', 'string', 'max:80'],
            'room_gender_policy' => ['nullable', 'string', 'max:80'],
            'required_amenity_ids' => ['nullable', 'array'],
            'preferred_amenity_ids' => ['nullable', 'array'],
            'excluded_rule_ids' => ['nullable', 'array'],
            'excluded_conditions' => ['nullable', 'array'],
            'notification_frequency' => ['nullable', 'string', 'max:40'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
        ]);

        $title = Str::limit(trim((string) ($data['title'] ?? $data['name'] ?? $existing?->title ?? $existing?->name ?? __('saved_searches.defaults.title'))), 160, '');
        $cityId = isset($data['city_id']) ? (int) $data['city_id'] : $existing?->city_id;
        $cityName = $data['city_name'] ?? $existing?->city;

        if ($cityId && ! $cityName) {
            $cityName = City::query()->whereKey($cityId)->value('name');
        }

        $checkIn = $data['check_in_date'] ?? $data['check_in'] ?? $existing?->check_in_date?->toDateString() ?? $existing?->check_in?->toDateString();
        $checkOut = $data['check_out_date'] ?? $data['check_out'] ?? $existing?->check_out_date?->toDateString() ?? $existing?->check_out?->toDateString();
        $counts = $this->dateCounts($checkIn, $checkOut);
        $budgetMin = $this->nullableMoney($data['budget_min'] ?? $data['price_min'] ?? $existing?->budget_min ?? $existing?->price_min);
        $budgetMax = $this->nullableMoney($data['budget_max'] ?? $data['price_max'] ?? $existing?->budget_max ?? $existing?->price_max);
        $frequency = (string) ($data['notification_frequency'] ?? $data['notify_frequency'] ?? $existing?->notification_frequency ?? 'on_visit');

        return [
            'title' => $title,
            'name' => $title,
            'description' => $validated['description'] ?? $existing?->description,
            'status' => $data['status'] ?? $existing?->status ?? 'active',
            'city_id' => $cityId,
            'city' => $cityName,
            'district' => $validated['district'] ?? $existing?->district,
            'location_text' => $validated['location_text'] ?? $existing?->location_text,
            'radius_meters' => $validated['radius_meters'] ?? $existing?->radius_meters,
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
            'total_budget_max' => $this->nullableMoney($data['total_budget_max'] ?? $existing?->total_budget_max),
            'currency' => strtoupper((string) ($data['currency'] ?? $existing?->currency ?? 'EUR')),
            'room_type' => $data['room_type'] ?? $existing?->room_type,
            'bed_type' => $data['sleeping_place_type'] ?? $data['bed_type'] ?? $existing?->bed_type,
            'room_types_json' => $this->list($data['room_types'] ?? ($data['room_type'] ?? null)),
            'sleeping_place_types_json' => $this->list($data['sleeping_place_types'] ?? ($data['sleeping_place_type'] ?? null)),
            'room_gender_policy' => $validated['room_gender_policy'] ?? $existing?->room_gender_policy,
            'required_amenity_ids_json' => $this->intList($data['required_amenity_ids'] ?? $existing?->required_amenity_ids_json ?? []),
            'preferred_amenity_ids_json' => $this->intList($data['preferred_amenity_ids'] ?? $existing?->preferred_amenity_ids_json ?? []),
            'excluded_rule_ids_json' => $this->intList($data['excluded_rule_ids'] ?? $existing?->excluded_rule_ids_json ?? []),
            'excluded_conditions_json' => $this->list($data['excluded_conditions'] ?? $existing?->excluded_conditions_json ?? []),
            'only_verified_hosts' => (bool) ($data['only_verified_hosts'] ?? $existing?->only_verified_hosts ?? false),
            'only_verified_places' => (bool) ($data['only_verified_places'] ?? $existing?->only_verified_places ?? false),
            'only_instant_booking' => (bool) ($data['only_instant_booking'] ?? $data['instant_booking'] ?? $existing?->only_instant_booking ?? false),
            'only_with_reviews' => (bool) ($data['only_with_reviews'] ?? $existing?->only_with_reviews ?? false),
            'free_cancellation_only' => (bool) ($data['free_cancellation_only'] ?? $existing?->free_cancellation_only ?? false),
            'no_deposit_only' => (bool) ($data['no_deposit_only'] ?? $existing?->no_deposit_only ?? false),
            'max_deposit' => $this->nullableMoney($data['max_deposit'] ?? $existing?->max_deposit),
            'min_rating' => $this->nullableDecimal($data['min_rating'] ?? $existing?->min_rating),
            'min_cleanliness_rating' => $this->nullableDecimal($data['min_cleanliness_rating'] ?? $existing?->min_cleanliness_rating),
            'min_safety_rating' => $this->nullableDecimal($data['min_safety_rating'] ?? $existing?->min_safety_rating),
            'min_host_rating' => $this->nullableDecimal($data['min_host_rating'] ?? $existing?->min_host_rating),
            'max_people_in_room' => isset($data['max_people_in_room']) ? (int) $data['max_people_in_room'] : $existing?->max_people_in_room,
            'lower_bunk_only' => (bool) ($data['lower_bunk_only'] ?? $existing?->lower_bunk_only ?? false),
            'exclude_upper_bunk' => (bool) ($data['exclude_upper_bunk'] ?? $existing?->exclude_upper_bunk ?? false),
            'exclude_sofa' => (bool) ($data['exclude_sofa'] ?? $existing?->exclude_sofa ?? false),
            'exclude_mattress' => (bool) ($data['exclude_mattress'] ?? $existing?->exclude_mattress ?? false),
            'require_locker' => (bool) ($data['require_locker'] ?? $existing?->require_locker ?? false),
            'require_workspace' => (bool) ($data['require_workspace'] ?? $existing?->require_workspace ?? false),
            'require_wifi' => (bool) ($data['require_wifi'] ?? $existing?->require_wifi ?? false),
            'require_kitchen' => (bool) ($data['require_kitchen'] ?? $existing?->require_kitchen ?? false),
            'require_washing_machine' => (bool) ($data['require_washing_machine'] ?? $existing?->require_washing_machine ?? false),
            'require_late_check_in' => (bool) ($data['require_late_check_in'] ?? $existing?->require_late_check_in ?? false),
            'avoid_smoking' => (bool) ($data['avoid_smoking'] ?? $existing?->avoid_smoking ?? false),
            'avoid_pets' => (bool) ($data['avoid_pets'] ?? $existing?->avoid_pets ?? false),
            'avoid_mixed_room' => (bool) ($data['avoid_mixed_room'] ?? $existing?->avoid_mixed_room ?? false),
            'notify_new_places' => (bool) ($data['notify_new_matches'] ?? $data['notify_new_places'] ?? true),
            'notify_price_drop' => (bool) ($data['notify_price_drops'] ?? $data['notify_price_drop'] ?? true),
            'notify_available' => (bool) ($data['notify_available_again'] ?? $data['notify_available'] ?? true),
            'notify_frequency' => $frequency,
            'notify_new_matches' => (bool) ($data['notify_new_matches'] ?? $existing?->notify_new_matches ?? true),
            'notify_price_drops' => (bool) ($data['notify_price_drops'] ?? $existing?->notify_price_drops ?? true),
            'notify_price_increases' => (bool) ($data['notify_price_increases'] ?? $existing?->notify_price_increases ?? false),
            'notify_available_again' => (bool) ($data['notify_available_again'] ?? $existing?->notify_available_again ?? true),
            'notify_better_match' => (bool) ($data['notify_better_match'] ?? $existing?->notify_better_match ?? true),
            'notification_frequency' => $frequency,
            'quiet_hours_enabled' => (bool) ($data['quiet_hours_enabled'] ?? $existing?->quiet_hours_enabled ?? true),
            'quiet_hours_start' => $data['quiet_hours_start'] ?? $existing?->quiet_hours_start ?? '22:00',
            'quiet_hours_end' => $data['quiet_hours_end'] ?? $existing?->quiet_hours_end ?? '07:00',
            'is_active' => ($data['status'] ?? $existing?->status ?? 'active') === 'active',
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
}
