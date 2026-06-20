<?php

namespace App\Services\Privacy;

class PrivacyPreferences
{
    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'show_profile' => true,
            'show_languages' => true,
            'show_reviews' => true,
            'guest' => [
                'show_full_name_to_confirmed_hosts_only' => true,
                'show_display_name_publicly' => true,
                'show_age' => false,
                'show_age_range' => true,
                'show_city' => true,
                'show_languages' => true,
                'show_occupation' => true,
                'show_avatar' => true,
                'show_reviews' => true,
                'show_verification_status' => true,
                'show_phone_after_confirmed_booking' => true,
            ],
            'host' => [
                'show_exact_address_before_booking' => false,
                'show_approximate_area_before_booking' => true,
                'show_phone_after_confirmed_booking' => true,
                'show_checkin_instructions_after_confirmation' => true,
                'hide_sensitive_public_listing_info' => true,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $preferences
     * @return array<string, mixed>
     */
    public static function normalize(?array $preferences): array
    {
        $preferences ??= [];
        $normalized = self::defaults();
        $hasNestedGuestPreferences = isset($preferences['guest']) && is_array($preferences['guest']);

        $normalized['guest'] = array_replace(
            $normalized['guest'],
            array_filter($preferences['guest'] ?? [], fn (mixed $value): bool => $value !== null),
        );
        $normalized['host'] = array_replace(
            $normalized['host'],
            array_filter($preferences['host'] ?? [], fn (mixed $value): bool => $value !== null),
        );

        if (! $hasNestedGuestPreferences && array_key_exists('show_profile', $preferences)) {
            $normalized['show_profile'] = (bool) $preferences['show_profile'];
            $normalized['guest']['show_display_name_publicly'] = (bool) $preferences['show_profile'];
            $normalized['guest']['show_avatar'] = (bool) $preferences['show_profile'];
        }

        if (! $hasNestedGuestPreferences && array_key_exists('show_languages', $preferences)) {
            $normalized['show_languages'] = (bool) $preferences['show_languages'];
            $normalized['guest']['show_languages'] = (bool) $preferences['show_languages'];
        }

        if (! $hasNestedGuestPreferences && array_key_exists('show_reviews', $preferences)) {
            $normalized['show_reviews'] = (bool) $preferences['show_reviews'];
            $normalized['guest']['show_reviews'] = (bool) $preferences['show_reviews'];
        }

        $normalized['show_profile'] = (bool) $normalized['show_profile'];
        $normalized['show_languages'] = (bool) $normalized['guest']['show_languages'];
        $normalized['show_reviews'] = (bool) $normalized['guest']['show_reviews'];

        foreach (['guest', 'host'] as $group) {
            foreach ($normalized[$group] as $key => $value) {
                $normalized[$group][$key] = (bool) $value;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>|null  $preferences
     */
    public static function guest(?array $preferences, string $key): bool
    {
        return (bool) (self::normalize($preferences)['guest'][$key] ?? false);
    }

    /**
     * @param  array<string, mixed>|null  $preferences
     */
    public static function host(?array $preferences, string $key): bool
    {
        return (bool) (self::normalize($preferences)['host'][$key] ?? false);
    }
}
