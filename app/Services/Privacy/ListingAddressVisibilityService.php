<?php

namespace App\Services\Privacy;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Models\User;
use App\Services\Localization\LocalizedModelContentResolver;

class ListingAddressVisibilityService
{
    public function __construct(
        private readonly LocalizedModelContentResolver $contentResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function addressFor(Property $property, ?User $viewer = null): array
    {
        $property->loadMissing(['cityModel', 'host.setting', 'translations']);

        $canSeeExactAddress = $this->canSeeExactAddress($property, $viewer);
        $canSeeInstructions = $this->canSeeCheckInInstructions($property, $viewer);
        $exactAddress = $canSeeExactAddress ? $this->exactAddress($property) : null;
        $approximateArea = $this->approximateArea($property);

        return [
            'can_see_exact' => $canSeeExactAddress,
            'can_see_check_in_instructions' => $canSeeInstructions,
            'address' => $exactAddress
                ? __('listing.detail.property.exact_address', ['address' => $exactAddress])
                : ($approximateArea ?: __('listing.detail.property.address_missing')),
            'exact_address' => $exactAddress,
            'approximate_area' => $approximateArea,
            'instructions' => $canSeeInstructions ? $this->checkInInstructions($property) : null,
            'note' => $canSeeExactAddress
                ? __('listing.detail.property.address_visible_note')
                : __('listing.detail.property.address_private_note'),
        ];
    }

    public function canSeeExactAddress(Property $property, ?User $viewer = null): bool
    {
        if (! $viewer instanceof User) {
            return $this->hostAllowsExactBeforeBooking($property);
        }

        if ($property->isOwnedBy($viewer)) {
            return true;
        }

        if ($this->hostAllowsExactBeforeBooking($property)) {
            return true;
        }

        return $this->propertyAllowsExactAfterPayment($property)
            && $this->hasAccessBooking($property, $viewer);
    }

    public function canSeeCheckInInstructions(Property $property, ?User $viewer = null): bool
    {
        if (! $viewer instanceof User) {
            return false;
        }

        if ($property->isOwnedBy($viewer)) {
            return true;
        }

        if (! $this->hostPreference($property, 'show_checkin_instructions_after_confirmation')) {
            return false;
        }

        return $this->hasAccessBooking($property, $viewer);
    }

    public function canSeeHostPhone(Property $property, ?User $viewer = null): bool
    {
        if (! $viewer instanceof User) {
            return false;
        }

        if ($property->isOwnedBy($viewer)) {
            return true;
        }

        if (! $this->hostPreference($property, 'show_phone_after_confirmed_booking')) {
            return false;
        }

        return $this->hasAccessBooking($property, $viewer);
    }

    private function hasAccessBooking(Property $property, User $viewer): bool
    {
        return Booking::query()
            ->select(['id'])
            ->where('property_id', $property->id)
            ->where('guest_user_id', $viewer->id)
            ->where(function ($query): void {
                $query
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->orWhereIn('status', $this->addressAccessStatusValues());
            })
            ->exists();
    }

    private function hostAllowsExactBeforeBooking(Property $property): bool
    {
        if (! $this->hostPreference($property, 'show_exact_address_before_booking')) {
            return false;
        }

        if ($this->hostPreference($property, 'hide_sensitive_public_listing_info')) {
            return false;
        }

        return (bool) $property->show_exact_address_before_booking;
    }

    private function propertyAllowsExactAfterPayment(Property $property): bool
    {
        $value = $property->getAttribute('show_exact_address_after_payment');

        return $value === null || (bool) $value;
    }

    private function hostPreference(Property $property, string $key): bool
    {
        $property->loadMissing('host.setting');

        return PrivacyPreferences::host($property->host?->setting?->privacy_preferences_json, $key);
    }

    /**
     * @return list<string>
     */
    private function addressAccessStatusValues(): array
    {
        return [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
            BookingStatus::LeavingSoon->value,
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::AwaitingReview->value,
            BookingStatus::Closed->value,
        ];
    }

    private function exactAddress(Property $property): ?string
    {
        $street = trim(implode(' ', array_filter([
            $property->address_line_1 ?: $property->street,
            $property->house_number ?: $property->building,
        ])));

        $parts = array_filter([
            $property->cityModel?->name ?: $property->city,
            $property->district,
            $street !== '' ? $street : null,
            $property->address_line_2,
            $property->apartment_number
                ? __('listing.detail.property.address_parts.apartment', ['number' => $property->apartment_number])
                : ($property->apartment ? __('listing.detail.property.address_parts.apartment', ['number' => $property->apartment]) : null),
            $property->floor
                ? __('listing.detail.property.address_parts.floor', ['number' => $property->floor])
                : null,
        ]);

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function approximateArea(Property $property): ?string
    {
        if (! $this->hostPreference($property, 'show_approximate_area_before_booking')) {
            return null;
        }

        $parts = array_filter([
            $property->cityModel?->name ?: $property->city,
            $property->district,
            $property->nearest_transport,
        ]);

        return $parts === []
            ? null
            : __('listing.detail.property.approximate_address', ['address' => implode(', ', $parts)]);
    }

    private function checkInInstructions(Property $property): ?string
    {
        $translation = $this->contentResolver->resolve(
            $property->translations,
            app()->getLocale(),
            config('app.fallback_locale', 'en'),
        );

        return $property->access_instructions
            ?: ($translation instanceof PropertyTranslation ? $translation->check_in_instructions : null);
    }
}
