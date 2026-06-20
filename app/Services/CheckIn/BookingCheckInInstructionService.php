<?php

namespace App\Services\CheckIn;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\User;

class BookingCheckInInstructionService
{
    /**
     * @return array<string, mixed>
     */
    public function getGuestInstructions(User $guest, Booking $booking): array
    {
        $booking->loadMissing(['property:id,address_line_1,house_number,apartment_number,city,district,show_exact_address_after_confirmation,show_exact_address_after_payment', 'host:id,name,phone,phone_verified,email']);

        $exactAddressVisible = $this->canShowExactAddress($guest, $booking);
        $accessCodesVisible = $this->canShowAccessCodes($guest, $booking);

        return [
            'exact_address_visible' => $exactAddressVisible,
            'access_codes_visible' => $accessCodesVisible,
            'exact_address' => $exactAddressVisible ? $this->exactAddress($booking) : null,
            'approximate_area' => trim(collect([$booking->property?->city, $booking->property?->district])->filter()->implode(', ')),
            'instructions' => $exactAddressVisible ? $booking->check_in_instructions : __('check_in.privacy.address_hidden'),
            'host_contact' => $this->getHostContact($guest, $booking),
            'host_representative_contact' => $this->getHostRepresentativeContact($guest, $booking),
        ];
    }

    public function canShowExactAddress(User $guest, Booking $booking): bool
    {
        if ((int) $booking->guest_user_id !== (int) $guest->id || ! $this->confirmedForGuest($booking)) {
            return false;
        }

        $booking->loadMissing('property:id,show_exact_address_after_confirmation,show_exact_address_after_payment');

        if ((bool) $booking->property?->show_exact_address_after_confirmation) {
            return true;
        }

        return (bool) $booking->property?->show_exact_address_after_payment
            && $this->paymentStatusValue($booking) === PaymentStatus::Paid->value;
    }

    public function canShowAccessCodes(User $guest, Booking $booking): bool
    {
        return $this->canShowExactAddress($guest, $booking);
    }

    /**
     * @return array<string, mixed>
     */
    public function getHostContact(User $guest, Booking $booking): array
    {
        if ((int) $booking->guest_user_id !== (int) $guest->id || ! $this->confirmedForGuest($booking)) {
            return ['chat' => false, 'name' => null, 'phone' => null, 'email' => null];
        }

        $booking->loadMissing('host:id,name,phone,phone_verified,email');

        return [
            'chat' => true,
            'name' => $booking->host?->name,
            'phone' => $booking->host?->phone_verified ? $booking->host?->phone : null,
            'email' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getHostRepresentativeContact(User $guest, Booking $booking): array
    {
        if (! $this->canShowExactAddress($guest, $booking)) {
            return ['available' => false, 'name' => null, 'phone' => null];
        }

        return ['available' => false, 'name' => null, 'phone' => null];
    }

    private function exactAddress(Booking $booking): string
    {
        return collect([
            $booking->property?->address_line_1,
            $booking->property?->house_number,
            $booking->property?->apartment_number ? __('check_in.fields.apartment_short', ['number' => $booking->property->apartment_number]) : null,
        ])->filter()->implode(', ');
    }

    private function confirmedForGuest(Booking $booking): bool
    {
        return in_array($this->statusValue($booking), [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
        ], true);
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }

    private function paymentStatusValue(Booking $booking): string
    {
        return $booking->payment_status instanceof PaymentStatus
            ? $booking->payment_status->value
            : (string) $booking->payment_status;
    }
}
