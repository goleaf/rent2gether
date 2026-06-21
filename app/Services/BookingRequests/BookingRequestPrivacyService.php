<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use App\Models\User;
use App\Services\Users\UserProfileVisibilityService;
use Illuminate\Support\Arr;

class BookingRequestPrivacyService
{
    public function __construct(
        private readonly UserProfileVisibilityService $profiles,
    ) {}

    public function canGuestView(User $guest, BookingRequest $request): bool
    {
        return (int) $request->guest_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, BookingRequest $request): bool
    {
        return (int) $request->host_user_id === (int) $host->id;
    }

    public function canHostRespond(User $host, BookingRequest $request): bool
    {
        return $this->canHostView($host, $request)
            && in_array($request->status, [
                BookingRequest::STATUS_SUBMITTED,
                BookingRequest::STATUS_ACTIVE,
                BookingRequest::STATUS_HOST_SEEN,
                BookingRequest::STATUS_WAITING_HOST_RESPONSE,
            ], true);
    }

    public function canGuestRespond(User $guest, BookingRequest $request): bool
    {
        return $this->canGuestView($guest, $request)
            && $request->status === BookingRequest::STATUS_WAITING_GUEST_RESPONSE;
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingRequest $request): array
    {
        abort_unless($this->canGuestView($guest, $request), 403);

        return [
            'id' => $request->id,
            'request_number' => $request->request_number,
            'request_type' => $request->request_type,
            'status' => $request->status,
            'check_in_date' => $request->check_in_date?->toDateString(),
            'check_out_date' => $request->check_out_date?->toDateString(),
            'nights_count' => (int) $request->nights_count,
            'guests_count' => (int) $request->guests_count,
            'total_amount' => $request->total_amount,
            'deposit_amount' => $request->deposit_amount,
            'currency' => $request->currency,
            'host_response' => $request->host_response,
            'rejection_reason' => $request->rejection_reason,
            'expires_at' => $request->expires_at?->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingRequest $request): array
    {
        abort_unless($this->canHostView($host, $request), 403);

        $request->loadMissing(['guest.profile', 'guest.userLanguages', 'guest.activitySummary']);
        $profile = $this->profiles->buildHostViewOfGuest($host, $request->guest, $request);

        return [
            'id' => $request->id,
            'request_number' => $request->request_number,
            'request_type' => $request->request_type,
            'status' => $request->status,
            'guest_profile' => $this->safeGuestProfile($profile),
            'booking_context' => [
                'property_id' => $request->property_id,
                'room_id' => $request->room_id,
                'sleeping_place_id' => $request->sleeping_place_id,
                'check_in_date' => $request->check_in_date?->toDateString(),
                'check_in_time' => $request->check_in_time,
                'check_out_date' => $request->check_out_date?->toDateString(),
                'check_out_time' => $request->check_out_time,
                'nights_count' => (int) $request->nights_count,
                'guests_count' => (int) $request->guests_count,
                'trip_purpose' => $request->trip_purpose,
                'planned_arrival_time' => $request->planned_arrival_time,
                'guest_message' => $request->guest_message,
            ],
            'price' => [
                'total_amount' => $request->total_amount,
                'deposit_amount' => $request->deposit_amount,
                'cleaning_fee_amount' => $request->cleaning_fee_amount,
                'service_fee_amount' => $request->service_fee_amount,
                'currency' => $request->currency,
            ],
            'expires_at' => $request->expires_at?->toISOString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    private function safeGuestProfile(array $profile): array
    {
        return Arr::except($profile, [
            'phone',
            'email',
            'date_of_birth',
            'birth_date',
            'documents',
            'document_files',
            'user_documents',
            'verification_metadata',
            'metadata_json',
            'file_path',
            'private_notes',
            'emergency_contact_phone',
            'emergency_contact_name',
        ]);
    }
}
