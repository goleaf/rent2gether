<?php

namespace App\Services\BookingRequests;

class BookingRequestService
{
    public function __construct(
        public readonly BookingRequestCreationService $creation,
        public readonly BookingRequestHostResponseService $hostResponses,
        public readonly BookingRequestGuestResponseService $guestResponses,
        public readonly BookingRequestConversionService $conversion,
        public readonly BookingRequestExpirationService $expiration,
        public readonly BookingRequestPrivacyService $privacy,
    ) {}
}
