<?php

namespace App\Services\CheckIn;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInInstruction;
use App\Models\User;

class BookingCheckInInstructionService
{
    public function __construct(
        private readonly BookingCheckInAccessDisclosureService $disclosures,
    ) {}

    public function createInstructionSnapshot(Booking $booking): BookingCheckInInstruction
    {
        $booking->load([
            'property:id,address_line_1,house_number,apartment_number,city,district,show_exact_address_after_confirmation,show_exact_address_after_payment',
            'property.accessDetails:id,property_id,key_pickup_instruction,key_return_instruction,check_in_instruction,night_entry_instruction,door_code_encrypted,intercom_code_encrypted,key_safe_code_encrypted,show_access_details_after_booking',
            'room:id,title,room_number',
            'sleepingPlace:id,display_name,place_number',
        ]);

        $checkIn = BookingCheckIn::query()->firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'check_in_date' => $booking->check_in_date,
                'planned_check_in_time' => $this->timeString($booking->arrival_time ?: $booking->check_in_time),
                'status' => 'instructions_available',
            ],
        );

        $access = $booking->property?->accessDetails;

        return BookingCheckInInstruction::query()->updateOrCreate(
            ['booking_check_in_id' => $checkIn->id],
            [
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'instruction_title' => __('check_in.title'),
                'public_instruction_text' => $booking->check_in_instructions,
                'address_instruction_text' => $access?->check_in_instruction,
                'building_entry_instruction' => $access?->check_in_instruction,
                'room_finding_instruction' => $booking->room?->title,
                'sleeping_place_instruction' => $booking->sleepingPlace?->display_name,
                'key_pickup_instruction' => $access?->key_pickup_instruction,
                'key_return_instruction' => $access?->key_return_instruction,
                'night_entry_instruction' => $access?->night_entry_instruction,
                'emergency_instruction' => $access?->what_if_code_fails ?? null,
                'exact_address_snapshot' => $this->exactAddress($booking),
                'room_identifier_snapshot' => $booking->room?->room_number ?: $booking->room?->title,
                'sleeping_place_identifier_snapshot' => $booking->sleepingPlace?->place_number ?: $booking->sleepingPlace?->display_name,
                'door_code_encrypted' => $access?->door_code_encrypted,
                'intercom_code_encrypted' => $access?->intercom_code_encrypted,
                'key_safe_code_encrypted' => $access?->key_safe_code_encrypted,
                'visible_from' => now(),
                'visible_until' => $booking->check_out_date,
            ],
        )->refresh();
    }

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

    /**
     * @return array<string, mixed>
     */
    public function getVisibleInstructions(User $guest, BookingCheckIn $checkIn): array
    {
        $checkIn->loadMissing([
            'booking.property:id,address_line_1,house_number,apartment_number,city,district,show_exact_address_after_confirmation,show_exact_address_after_payment',
            'instruction',
        ]);

        $exactAddressVisible = $this->canShowExactAddress($guest, $checkIn);
        $accessCodesVisible = $this->canShowAccessCodes($guest, $checkIn);

        if ($exactAddressVisible) {
            $this->disclosures->recordDisclosure($guest, $checkIn, 'exact_address');
        }

        if ($accessCodesVisible && $checkIn->instruction?->door_code_encrypted) {
            $this->disclosures->recordDisclosure($guest, $checkIn, 'door_code');
        }

        return [
            'exact_address_visible' => $exactAddressVisible,
            'access_codes_visible' => $accessCodesVisible,
            'exact_address' => $exactAddressVisible ? $checkIn->instruction?->exact_address_snapshot : null,
            'approximate_area' => trim(collect([$checkIn->booking?->property?->city, $checkIn->booking?->property?->district])->filter()->implode(', ')),
            'instructions' => $exactAddressVisible ? $checkIn->instruction?->public_instruction_text : __('check_in.privacy.address_hidden'),
            'door_code' => $accessCodesVisible ? $checkIn->instruction?->door_code_encrypted : null,
            'intercom_code' => $accessCodesVisible ? $checkIn->instruction?->intercom_code_encrypted : null,
            'key_safe_code' => $accessCodesVisible ? $checkIn->instruction?->key_safe_code_encrypted : null,
            'night_entry_instruction' => $accessCodesVisible ? $checkIn->instruction?->night_entry_instruction : null,
        ];
    }

    public function canShowExactAddress(User $guest, Booking|BookingCheckIn $target): bool
    {
        $booking = $target instanceof BookingCheckIn ? $target->booking()->firstOrFail() : $target;

        if ((int) $booking->guest_user_id !== (int) $guest->id || ! $this->confirmedForGuest($booking)) {
            return false;
        }

        if ($target instanceof BookingCheckIn) {
            $target->loadMissing('instruction');

            if ($target->instruction?->visible_from && now()->lt($target->instruction->visible_from)) {
                return false;
            }

            if ($target->instruction?->visible_until && now()->gt($target->instruction->visible_until)) {
                return false;
            }
        }

        $booking->loadMissing('property:id,show_exact_address_after_confirmation,show_exact_address_after_payment');

        if ((bool) $booking->property?->show_exact_address_after_confirmation) {
            return true;
        }

        return (bool) $booking->property?->show_exact_address_after_payment
            && $this->paymentStatusValue($booking) === PaymentStatus::Paid->value;
    }

    public function canShowAccessCodes(User $guest, Booking|BookingCheckIn $target): bool
    {
        if (! $this->canShowExactAddress($guest, $target)) {
            return false;
        }

        if ($target instanceof Booking) {
            $target->loadMissing('property.accessDetails:property_id,show_access_details_after_booking');

            return (bool) ($target->property?->accessDetails?->show_access_details_after_booking ?? true);
        }

        $target->loadMissing('instruction', 'booking.property.accessDetails:property_id,show_access_details_after_booking');

        return (bool) ($target->booking?->property?->accessDetails?->show_access_details_after_booking ?? true);
    }

    public function canShowHostContact(User $guest, BookingCheckIn $checkIn): bool
    {
        return (int) $checkIn->guest_user_id === (int) $guest->id
            && $this->confirmedForGuest($checkIn->booking()->firstOrFail());
    }

    public function canShowRepresentativeContact(User $guest, BookingCheckIn $checkIn): bool
    {
        return $this->canShowHostContact($guest, $checkIn);
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
        $streetLine = trim(collect([
            $booking->property?->address_line_1,
            $booking->property?->house_number,
        ])->filter()->implode(' '));

        return collect([
            $streetLine !== '' ? $streetLine : null,
            $booking->property?->apartment_number ? __('check_in.fields.apartment_short', ['number' => $booking->property->apartment_number]) : null,
        ])->filter()->implode(', ');
    }

    private function confirmedForGuest(Booking $booking): bool
    {
        return in_array($this->statusValue($booking), [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            BookingStatus::ReadyForCheckInCore->value,
            BookingStatus::CheckedIn->value,
            BookingStatus::GuestCheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::StayInProgress->value,
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

    private function timeString(mixed $time): ?string
    {
        return is_object($time) && method_exists($time, 'format') ? $time->format('H:i') : $time;
    }
}
