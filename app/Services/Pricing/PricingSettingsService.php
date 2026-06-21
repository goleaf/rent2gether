<?php

namespace App\Services\Pricing;

use App\Models\SleepingPlace;
use App\Models\SleepingPlacePricingSetting;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PricingSettingsService
{
    /**
     * Fetches active pricing settings or creates safe defaults from legacy Sleeping Place fields.
     */
    public function getForSleepingPlace(SleepingPlace $place): SleepingPlacePricingSetting
    {
        $place->loadMissing('pricingSettings');

        if ($place->pricingSettings instanceof SleepingPlacePricingSetting) {
            return $place->pricingSettings;
        }

        return $this->createDefaultForSleepingPlace($place);
    }

    /**
     * Creates default pricing settings that preserve existing quote totals.
     */
    public function createDefaultForSleepingPlace(SleepingPlace $place): SleepingPlacePricingSetting
    {
        $basePrice = $this->money($place->base_price_per_night ?: $place->base_price ?: 20);
        $maxGuests = max(1, (int) ($place->max_guests_count ?: $place->max_guests ?: 1));
        $deposit = $this->money($place->deposit_amount ?? 0);

        $settings = $place->pricingSettings()->create([
            'currency' => strtoupper((string) ($place->currency ?: 'EUR')),
            'base_nightly_price' => $basePrice,
            'weekday_price' => null,
            'weekend_price' => $place->weekend_price,
            'holiday_price' => $place->holiday_price,
            'weekly_price' => $place->weekly_price,
            'monthly_price' => $place->monthly_price,
            'pricing_strategy' => SleepingPlacePricingSetting::STRATEGY_PER_NIGHT_WITH_DISCOUNTS,
            'weekend_days_json' => ['saturday', 'sunday'],
            'extra_guest_allowed' => (bool) $place->second_guest_allowed,
            'included_guests_count' => 1,
            'max_guests_count' => $maxGuests,
            'extra_guest_fee' => $place->second_guest_fee,
            'early_check_in_mode' => $place->early_check_in_allowed
                ? SleepingPlacePricingSetting::TIME_MODE_FREE
                : SleepingPlacePricingSetting::TIME_MODE_NOT_ALLOWED,
            'late_checkout_mode' => $place->late_check_out_allowed
                ? SleepingPlacePricingSetting::TIME_MODE_FREE
                : SleepingPlacePricingSetting::TIME_MODE_NOT_ALLOWED,
            'cleaning_fee' => $place->cleaning_fee,
            'deposit_required' => $deposit > 0,
            'deposit_amount' => $deposit > 0 ? $deposit : null,
            'deposit_payable_now' => true,
            'deposit_refundable' => true,
            'guest_service_fee_type' => SleepingPlacePricingSetting::FEE_PERCENT,
            'guest_service_fee_value' => 5,
            'host_service_fee_type' => SleepingPlacePricingSetting::FEE_NONE,
            'host_service_fee_value' => 0,
            'tax_fee_type' => SleepingPlacePricingSetting::FEE_NONE,
            'tax_fee_value' => 0,
            'city_fee_type' => SleepingPlacePricingSetting::FEE_NONE,
            'city_fee_value' => 0,
            'active' => true,
        ]);

        $place->setRelation('pricingSettings', $settings);

        return $settings;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateForSleepingPlace(User $host, SleepingPlace $place, array $data): SleepingPlacePricingSetting
    {
        if (! $this->hostOwnsPlace($host, $place)) {
            throw ValidationException::withMessages([
                'sleeping_place' => __('pricing.validation.host_owns_sleeping_place'),
            ]);
        }

        $settings = $this->getForSleepingPlace($place);
        $settings->forceFill($this->validateSettings($data))->save();

        return $settings->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validateSettings(array $data): array
    {
        $payload = [];
        $allowed = [
            'currency',
            'base_nightly_price',
            'weekday_price',
            'weekend_price',
            'holiday_price',
            'weekly_price',
            'monthly_price',
            'pricing_strategy',
            'weekend_days_json',
            'extra_guest_allowed',
            'included_guests_count',
            'max_guests_count',
            'extra_guest_fee',
            'early_check_in_mode',
            'early_check_in_fee',
            'late_checkout_mode',
            'late_checkout_fee',
            'cleaning_fee',
            'deposit_required',
            'deposit_amount',
            'deposit_payable_now',
            'deposit_refundable',
            'guest_service_fee_type',
            'guest_service_fee_value',
            'host_service_fee_type',
            'host_service_fee_value',
            'tax_fee_type',
            'tax_fee_value',
            'city_fee_type',
            'city_fee_value',
            'active',
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (isset($payload['currency'])) {
            $payload['currency'] = strtoupper((string) $payload['currency']);
        }

        if (isset($payload['base_nightly_price']) && (float) $payload['base_nightly_price'] < 0) {
            throw ValidationException::withMessages([
                'base_nightly_price' => __('pricing.validation.price_cannot_be_negative'),
            ]);
        }

        if (isset($payload['included_guests_count'], $payload['max_guests_count'])
            && (int) $payload['included_guests_count'] > (int) $payload['max_guests_count']) {
            throw ValidationException::withMessages([
                'max_guests_count' => __('pricing.validation.max_guests_too_low'),
            ]);
        }

        return $payload;
    }

    private function hostOwnsPlace(User $host, SleepingPlace $place): bool
    {
        $place->loadMissing('property');

        return (int) $place->user_id === (int) $host->id
            || (int) ($place->property?->host_user_id ?? 0) === (int) $host->id;
    }

    private function money(mixed $amount): float
    {
        return round((float) $amount, 2);
    }
}
