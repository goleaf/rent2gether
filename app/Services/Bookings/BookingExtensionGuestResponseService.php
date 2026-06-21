<?php

namespace App\Services\Bookings;

use App\Models\BookingExtension;
use App\Models\BookingExtensionGuestResponse;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookingExtensionGuestResponseService
{
    public function __construct(
        private readonly BookingExtensionPrivacyService $privacy,
        private readonly BookingExtensionPriceService $pricing,
        private readonly BookingExtensionLineService $lines,
        private readonly BookingExtensionHoldService $holds,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function acceptHostProposal(User $guest, BookingExtension $extension, array $data): BookingExtensionGuestResponse
    {
        $this->authorize($guest, $extension);

        $response = $this->createResponse($guest, $extension, 'accept_host_proposal', [
            'accepted_new_check_out_date' => $data['accepted_new_check_out_date'],
            'accepted_new_check_out_time' => $data['accepted_new_check_out_time'] ?? null,
        ]);

        $extension->forceFill([
            'new_check_out_date' => $data['accepted_new_check_out_date'],
            'requested_new_checkout_date' => $data['accepted_new_check_out_date'],
            'new_check_out' => $data['accepted_new_check_out_date'],
            'new_check_out_time' => $data['accepted_new_check_out_time'] ?? $extension->new_check_out_time,
        ])->save();

        $price = $this->pricing->priceData($extension->refresh());
        unset($price['date_prices']);

        $extension->forceFill([
            ...$price,
            'additional_nights' => $price['additional_nights_count'],
            'extra_nights' => $price['additional_nights_count'],
            'additional_amount' => $price['accommodation_amount'],
            'extra_amount' => $price['accommodation_amount'],
            'total_extra' => $price['total_payable'],
            'requires_payment' => $price['total_payable'] > 0,
            'payment_required' => $price['total_payable'] > 0,
            'payment_status' => $price['total_payable'] > 0 ? 'waiting_payment' : 'not_required',
            'status' => $price['total_payable'] > 0 ? 'approved_waiting_payment' : 'approved',
            'approved_at' => now(),
        ])->save();

        $this->holds->releaseHold($extension->refresh(), 'proposal_repriced');
        $this->lines->rebuildLines($extension->refresh());
        $this->holds->createTemporaryHold($extension->refresh());

        app(BookingExtensionEventService::class)->record($extension->refresh(), 'guest_accepted_proposal', ['user_id' => $guest->id]);

        return $response;
    }

    public function rejectHostProposal(User $guest, BookingExtension $extension, string $message): BookingExtensionGuestResponse
    {
        $this->authorize($guest, $extension);

        return $this->createResponse($guest, $extension, 'reject_host_proposal', [
            'message' => $message,
        ]);
    }

    public function answerQuestion(User $guest, BookingExtension $extension, string $message): BookingExtensionGuestResponse
    {
        $this->authorize($guest, $extension);

        return $this->createResponse($guest, $extension, 'answer_question', [
            'message' => $message,
        ]);
    }

    public function cancelRequest(User $guest, BookingExtension $extension): BookingExtensionGuestResponse
    {
        $this->authorize($guest, $extension);

        $response = $this->createResponse($guest, $extension, 'cancel_request', []);

        app(BookingExtensionService::class)->cancelByGuest($guest, $extension);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createResponse(User $guest, BookingExtension $extension, string $type, array $data): BookingExtensionGuestResponse
    {
        return BookingExtensionGuestResponse::query()->create([
            'booking_extension_id' => $extension->id,
            'guest_user_id' => $guest->id,
            'response_type' => $type,
            'message' => $data['message'] ?? null,
            'accepted_new_check_out_date' => $data['accepted_new_check_out_date'] ?? null,
            'accepted_new_check_out_time' => $data['accepted_new_check_out_time'] ?? null,
        ]);
    }

    private function authorize(User $guest, BookingExtension $extension): void
    {
        $extension->loadMissing('booking');

        if (! $this->privacy->canGuestView($guest, $extension)) {
            throw new AuthorizationException(__('booking_extensions.messages.not_allowed'));
        }
    }
}
