<?php

namespace App\Services\Properties;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Property;
use App\Models\User;

class PropertyPrivacyService
{
    public function canShowExactAddress(User $user, Property $property, ?Booking $booking = null): bool
    {
        if ($property->isOwnedBy($user)) {
            return true;
        }

        if ((bool) $property->show_exact_address_before_booking) {
            return true;
        }

        if (! $this->bookingBelongsToGuest($booking, $property, $user)) {
            return false;
        }

        if ((bool) $property->show_exact_address_after_payment && $booking?->payment_status === PaymentStatus::Paid) {
            return true;
        }

        return (bool) $property->show_exact_address_after_confirmation
            && $this->bookingHasConfirmedAccess($booking);
    }

    public function canShowApartmentNumber(User $user, Property $property, ?Booking $booking = null): bool
    {
        return $this->canShowExactAddress($user, $property, $booking);
    }

    public function canShowDoorCode(User $user, Property $property, ?Booking $booking = null): bool
    {
        if ($property->isOwnedBy($user)) {
            return true;
        }

        $property->loadMissing('accessDetails');
        $access = $property->accessDetails;

        if (! $access || ! $this->bookingBelongsToGuest($booking, $property, $user)) {
            return false;
        }

        if ((bool) $access->code_visible_after_payment && $booking?->payment_status === PaymentStatus::Paid) {
            return true;
        }

        return (bool) $access->code_visible_after_confirmation
            && $this->bookingHasConfirmedAccess($booking);
    }

    public function canShowKeySafeLocation(User $user, Property $property, ?Booking $booking = null): bool
    {
        $property->loadMissing('accessDetails');

        return (bool) $property->accessDetails?->has_key_safe
            && $this->canShowDoorCode($user, $property, $booking);
    }

    public function getSafePublicAddress(Property $property): string
    {
        return $this->publicAddress($property, null, null);
    }

    public function publicAddress(Property $property, ?User $viewer = null, ?Booking $booking = null): string
    {
        $property->loadMissing('cityModel');

        if ($viewer instanceof User && $this->canShowExactAddress($viewer, $property, $booking)) {
            return $this->exactAddress($property);
        }

        $parts = array_filter([
            $property->cityModel?->name ?: $property->city,
            $property->district,
        ]);

        return $parts === []
            ? __('property.privacy.location_hidden')
            : __('property.privacy.approximate_address', ['address' => implode(', ', $parts)]);
    }

    private function exactAddress(Property $property): string
    {
        $street = trim(implode(' ', array_filter([
            $property->street ?: $property->address_line_1,
            $property->house_number ?: $property->building,
        ])));

        $parts = array_filter([
            $property->cityModel?->name ?: $property->city,
            $property->district,
            $street !== '' ? $street : null,
            $property->apartment_number ? __('property.address_parts.apartment', ['number' => $property->apartment_number]) : null,
            $property->floor ? __('property.address_parts.floor', ['number' => $property->floor]) : null,
        ]);

        return $parts === [] ? __('property.privacy.location_hidden') : implode(', ', $parts);
    }

    private function bookingBelongsToGuest(?Booking $booking, Property $property, User $user): bool
    {
        return $booking instanceof Booking
            && (int) $booking->property_id === (int) $property->id
            && (int) $booking->guest_user_id === (int) $user->id;
    }

    private function bookingHasConfirmedAccess(?Booking $booking): bool
    {
        if (! $booking instanceof Booking) {
            return false;
        }

        if ($booking->payment_status === PaymentStatus::Paid) {
            return true;
        }

        $status = $booking->status instanceof BookingStatus ? $booking->status : BookingStatus::tryFrom((string) $booking->status);

        return in_array($status, [
            BookingStatus::Confirmed,
            BookingStatus::Paid,
            BookingStatus::ReadyForCheckIn,
            BookingStatus::CheckedIn,
            BookingStatus::InProgress,
            BookingStatus::ActiveStay,
            BookingStatus::CheckedOut,
            BookingStatus::Completed,
        ], true);
    }
}
