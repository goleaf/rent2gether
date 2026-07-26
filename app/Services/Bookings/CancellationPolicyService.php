<?php

namespace App\Services\Bookings;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCancellationPolicy;
use App\Models\SleepingPlaceCancellationPolicyRule;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CancellationPolicyService
{
    public function getForSleepingPlace(SleepingPlace $place): SleepingPlaceCancellationPolicy
    {
        $policy = $place->cancellationPolicies()
            ->with('rules')
            ->where('active', true)
            ->latest('id')
            ->first();

        return $policy instanceof SleepingPlaceCancellationPolicy
            ? $policy
            : $this->createDefaultForSleepingPlace($place);
    }

    public function createDefaultForSleepingPlace(SleepingPlace $place): SleepingPlaceCancellationPolicy
    {
        $policyType = (string) ($place->cancellation_policy ?: 'flexible');

        $policy = SleepingPlaceCancellationPolicy::query()->create([
            'sleeping_place_id' => $place->id,
            'policy_type' => $policyType,
            'title' => 'cancellations.policy_types.'.$policyType,
            'description' => null,
            'free_cancellation_until_hours_before_check_in' => $this->defaultFreeHours($policyType),
            'penalty_starts_hours_before_check_in' => $this->defaultFreeHours($policyType),
            'first_night_non_refundable' => $policyType === 'strict',
            'cleaning_fee_refundable_before_check_in' => true,
            'service_fee_refundable' => false,
            'deposit_always_refundable_before_check_in' => true,
            'active' => true,
        ]);

        $this->createDefaultRules($policy);

        return $policy->fresh('rules');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateForSleepingPlace(User $host, SleepingPlace $place, array $data): SleepingPlaceCancellationPolicy
    {
        abort_unless($this->hostOwnsPlace($host, $place), 403);

        return DB::transaction(function () use ($place, $data): SleepingPlaceCancellationPolicy {
            SleepingPlaceCancellationPolicy::query()
                ->where('sleeping_place_id', $place->id)
                ->where('active', true)
                ->update(['active' => false, 'updated_at' => now()]);

            $policyType = (string) ($data['policy_type'] ?? $place->cancellation_policy ?? 'flexible');

            $policy = SleepingPlaceCancellationPolicy::query()->create([
                'sleeping_place_id' => $place->id,
                'policy_type' => $policyType,
                'title' => (string) ($data['title'] ?? 'cancellations.policy_types.'.$policyType),
                'description' => $data['description'] ?? null,
                'free_cancellation_until_days_before_check_in' => $data['free_cancellation_until_days_before_check_in'] ?? null,
                'free_cancellation_until_hours_before_check_in' => $data['free_cancellation_until_hours_before_check_in'] ?? $this->defaultFreeHours($policyType),
                'penalty_starts_hours_before_check_in' => $data['penalty_starts_hours_before_check_in'] ?? $this->defaultFreeHours($policyType),
                'first_night_non_refundable' => (bool) ($data['first_night_non_refundable'] ?? $policyType === 'strict'),
                'cleaning_fee_refundable_before_check_in' => (bool) ($data['cleaning_fee_refundable_before_check_in'] ?? true),
                'service_fee_refundable' => (bool) ($data['service_fee_refundable'] ?? false),
                'deposit_always_refundable_before_check_in' => (bool) ($data['deposit_always_refundable_before_check_in'] ?? true),
                'active' => true,
            ]);

            $this->createDefaultRules($policy);

            return $policy->fresh('rules');
        });
    }

    /**
     * @return Collection<int, SleepingPlaceCancellationPolicyRule>
     */
    public function getRules(SleepingPlaceCancellationPolicy $policy): Collection
    {
        return $policy->rules()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function hostOwnsPlace(User $host, SleepingPlace $place): bool
    {
        $place->loadMissing('property');

        return (int) $place->user_id === (int) $host->id
            || (int) $place->property?->host_user_id === (int) $host->id
            || (int) $place->property?->user_id === (int) $host->id;
    }

    private function defaultFreeHours(string $policyType): int
    {
        return match ($policyType) {
            'moderate' => 120,
            'strict' => 168,
            'non_refundable' => 0,
            default => 24,
        };
    }

    private function createDefaultRules(SleepingPlaceCancellationPolicy $policy): void
    {
        $policy->rules()->createMany([
            [
                'rule_key' => 'free_before_deadline',
                'applies_when' => 'before_free_cancellation_deadline',
                'refund_percent' => 100,
                'currency' => $policy->sleepingPlace?->currency ?? 'EUR',
                'sort_order' => 10,
            ],
            [
                'rule_key' => $policy->policy_type === 'non_refundable' ? 'no_refund_after_check_in' : 'partial_after_deadline',
                'applies_when' => 'after_free_cancellation_deadline',
                'refund_percent' => $policy->policy_type === 'non_refundable' ? 0 : 50,
                'currency' => $policy->sleepingPlace?->currency ?? 'EUR',
                'sort_order' => 20,
            ],
            [
                'rule_key' => 'deposit_refund',
                'applies_when' => 'guest_cancels',
                'refund_percent' => 100,
                'currency' => $policy->sleepingPlace?->currency ?? 'EUR',
                'sort_order' => 30,
            ],
        ]);
    }
}
