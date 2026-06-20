<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingGuestIntake;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Arr;

class BookingGuestIntakeService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createForBooking(User $guest, array $data): BookingGuestIntake
    {
        $place = SleepingPlace::query()
            ->select(['id', 'property_id', 'room_id'])
            ->findOrFail((int) $data['sleeping_place_id']);

        return BookingGuestIntake::query()->create($this->payload($guest, $place, $data));
    }

    /**
     * @param  object|array<string, mixed>  $quote
     * @param  array<string, mixed>  $data
     */
    public function createForQuote(User $guest, object|array $quote, array $data): BookingGuestIntake
    {
        $quoteData = is_array($quote) ? $quote : get_object_vars($quote);

        return $this->createForBooking($guest, [
            ...$data,
            'booking_quote_id' => $quoteData['id'] ?? null,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? $quoteData['sleeping_place_id'],
        ]);
    }

    /**
     * @param  object|array<string, mixed>  $request
     * @param  array<string, mixed>  $data
     */
    public function createForRequest(User $guest, object|array $request, array $data): BookingGuestIntake
    {
        $requestData = is_array($request) ? $request : get_object_vars($request);

        return $this->createForBooking($guest, [
            ...$data,
            'booking_request_id' => $requestData['id'] ?? null,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? $requestData['sleeping_place_id'],
        ]);
    }

    public function copyToBooking(BookingGuestIntake $intake, Booking $booking): BookingGuestIntake
    {
        $intake->forceFill([
            'booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'user_id' => $booking->guest_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
        ])->save();

        return $intake->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildHostSummary(BookingGuestIntake $intake): array
    {
        return [
            'trip_purpose' => $intake->trip_purpose,
            'planned_arrival_time' => $intake->planned_arrival_time,
            'planned_departure_time' => $intake->planned_departure_time,
            'needs_early_check_in' => (bool) ($intake->needs_early_check_in || $intake->early_check_in_requested),
            'needs_late_check_out' => (bool) ($intake->needs_late_check_out || $intake->late_check_out_requested),
            'has_large_suitcase' => (bool) $intake->has_large_suitcase,
            'has_pet' => (bool) $intake->has_pet,
            'smokes' => (bool) $intake->smokes,
            'needs_quiet' => (bool) $intake->needs_quiet,
            'needs_desk' => (bool) ($intake->needs_desk || $intake->needs_workspace),
            'needs_fast_wifi' => (bool) $intake->needs_fast_wifi,
            'needs_registration' => (bool) $intake->needs_registration,
            'needs_work_documents' => (bool) $intake->needs_work_documents,
            'special_requests' => $intake->special_requests,
            'message_to_host' => $intake->message_to_host ?: $intake->host_message,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(User $guest, SleepingPlace $place, array $data): array
    {
        $allowed = Arr::only($data, [
            'booking_quote_id',
            'booking_request_id',
            'booking_id',
            'trip_purpose',
            'planned_arrival_time',
            'planned_departure_time',
            'needs_early_check_in',
            'needs_late_check_out',
            'luggage_amount',
            'has_large_suitcase',
            'has_pet',
            'smokes',
            'needs_quiet',
            'needs_desk',
            'needs_fast_wifi',
            'needs_registration',
            'needs_work_documents',
            'special_requests',
            'message_to_host',
        ]);

        return [
            ...$allowed,
            'user_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'status' => 'draft',
            'early_check_in_requested' => (bool) ($data['needs_early_check_in'] ?? false),
            'late_check_out_requested' => (bool) ($data['needs_late_check_out'] ?? false),
            'baggage_level' => $data['luggage_amount'] ?? null,
            'needs_workspace' => (bool) ($data['needs_desk'] ?? false),
            'host_message' => $data['message_to_host'] ?? null,
        ];
    }
}
