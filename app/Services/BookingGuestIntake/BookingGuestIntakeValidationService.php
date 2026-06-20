<?php

namespace App\Services\BookingGuestIntake;

use App\Models\BookingGuestIntake;
use Illuminate\Support\Arr;

class BookingGuestIntakeValidationService
{
    /**
     * @return array<string, list<array{key:string,severity:string,message:string}>>
     */
    public function inspect(BookingGuestIntake $intake): array
    {
        $intake->loadMissing(['property:id,amenities,rules,noise_level', 'room:id,has_desk,has_chair,noise_level,can_work_at_night,can_turn_light_at_night', 'sleepingPlace:id,early_check_in_allowed,late_check_out_allowed,has_luggage_space,has_power_socket']);

        $warnings = [];
        $blockingReasons = [];

        if ($intake->early_check_in_requested && ! $this->isTruthy($intake->sleepingPlace?->early_check_in_allowed)) {
            $warnings[] = $this->warning('early_check_in_unavailable');
        }

        if ($intake->late_check_out_requested && ! $this->isTruthy($intake->sleepingPlace?->late_check_out_allowed)) {
            $warnings[] = $this->warning('late_check_out_unavailable');
        }

        if ($intake->has_pet && $this->hasRule($intake, ['no_pets', 'pets_forbidden'])) {
            $blockingReasons[] = $this->blocking('pet_forbidden');
        }

        if ($intake->smokes && $this->hasRule($intake, ['no_smoking', 'strict_no_smoking'])) {
            $warnings[] = $this->warning('smoking_conflict');
        }

        if ($intake->needs_quiet && $this->roomIsNoisy($intake)) {
            $warnings[] = $this->warning('quiet_conflict');
        }

        if ($intake->needs_workspace && ! $this->hasWorkspace($intake)) {
            $warnings[] = $this->warning('workspace_missing');
        }

        if ($intake->needs_fast_wifi && ! $this->hasAmenity($intake, ['fast_wifi', 'high_speed_wifi'])) {
            $warnings[] = $this->warning('fast_wifi_missing');
        }

        if ($intake->needs_power_socket && ! $this->isTruthy($intake->sleepingPlace?->has_power_socket)) {
            $warnings[] = $this->warning('power_socket_missing');
        }

        if ($intake->needs_registration || $intake->needs_work_documents || $intake->needs_invoice || $intake->needs_receipt || $intake->needs_contract) {
            $warnings[] = $this->warning('documents_need_confirmation');
        }

        if (
            ($intake->has_large_suitcase || $intake->needs_luggage_storage_before_checkin || $intake->needs_luggage_storage_after_checkout)
            && ! $this->hasLuggageSupport($intake)
        ) {
            $warnings[] = $this->warning('luggage_space_missing');
        }

        if ($intake->needs_late_entry && ! $this->hasAmenity($intake, ['late_entry', 'twenty_four_seven_access', '24_7_access'])) {
            $warnings[] = $this->warning('late_entry_unavailable');
        }

        if ($intake->needs_self_check_in && ! $this->hasAmenity($intake, ['self_check_in', 'key_box', 'smart_lock'])) {
            $warnings[] = $this->warning('self_check_in_unavailable');
        }

        return [
            'warnings' => $this->uniqueByKey($warnings),
            'blocking_reasons' => $this->uniqueByKey($blockingReasons),
        ];
    }

    /**
     * @return list<array{key:string,severity:string,message:string}>
     */
    public function warnings(BookingGuestIntake $intake): array
    {
        return $this->inspect($intake)['warnings'];
    }

    /**
     * @return list<array{key:string,severity:string,message:string}>
     */
    public function blockingReasons(BookingGuestIntake $intake): array
    {
        return $this->inspect($intake)['blocking_reasons'];
    }

    /**
     * @param  list<array{key:string,severity:string,message:string}>  $items
     * @return list<array{key:string,severity:string,message:string}>
     */
    private function uniqueByKey(array $items): array
    {
        return array_values(collect($items)->unique('key')->all());
    }

    /**
     * @return array{key:string,severity:string,message:string}
     */
    private function warning(string $key): array
    {
        return [
            'key' => $key,
            'severity' => 'warning',
            'message' => __("guest_intake.warnings.{$key}"),
        ];
    }

    /**
     * @return array{key:string,severity:string,message:string}
     */
    private function blocking(string $key): array
    {
        return [
            'key' => $key,
            'severity' => 'blocking',
            'message' => __("guest_intake.blocking.{$key}"),
        ];
    }

    /**
     * @param  list<string>  $needles
     */
    private function hasRule(BookingGuestIntake $intake, array $needles): bool
    {
        return $this->containsAny($this->values($intake->property?->rules), $needles)
            || $this->containsAny($this->values($intake->room?->rules), $needles);
    }

    /**
     * @param  list<string>  $needles
     */
    private function hasAmenity(BookingGuestIntake $intake, array $needles): bool
    {
        return $this->containsAny($this->values($intake->property?->amenities), $needles);
    }

    private function roomIsNoisy(BookingGuestIntake $intake): bool
    {
        return in_array($intake->room?->noise_level, ['medium', 'moderate', 'high', 'noisy'], true)
            || in_array($intake->property?->noise_level, ['medium', 'moderate', 'high', 'noisy'], true);
    }

    private function hasWorkspace(BookingGuestIntake $intake): bool
    {
        return ($this->isTruthy($intake->room?->has_desk) && $this->isTruthy($intake->room?->has_chair))
            || $this->hasAmenity($intake, ['workspace', 'desk', 'coworking']);
    }

    private function hasLuggageSupport(BookingGuestIntake $intake): bool
    {
        return $this->isTruthy($intake->sleepingPlace?->has_luggage_space)
            || $this->hasAmenity($intake, ['luggage_storage', 'bag_storage']);
    }

    /**
     * @return list<string>
     */
    private function values(mixed $values): array
    {
        if (is_string($values)) {
            $decoded = json_decode($values, true);
            $values = is_array($decoded) ? $decoded : [$values];
        }

        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): ?string => is_scalar($value) ? (string) $value : Arr::get((array) $value, 'slug'),
            $values
        )));
    }

    /**
     * @param  list<string>  $haystack
     * @param  list<string>  $needles
     */
    private function containsAny(array $haystack, array $needles): bool
    {
        return count(array_intersect($haystack, $needles)) > 0;
    }

    private function isTruthy(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
