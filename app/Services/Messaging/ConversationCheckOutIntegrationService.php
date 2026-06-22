<?php

namespace App\Services\Messaging;

use App\Models\BookingCheckOut;

class ConversationCheckOutIntegrationService
{
    public function addCheckoutSoonEvent(BookingCheckOut $checkOut): void
    {
        $this->add($checkOut, 'checkout_soon', 'important');
    }

    public function addGuestCheckedOutEvent(BookingCheckOut $checkOut): void
    {
        $this->add($checkOut, 'guest_checked_out', 'important');
    }

    public function addCheckoutCompletedEvent(BookingCheckOut $checkOut): void
    {
        $this->add($checkOut, 'checkout_completed');
    }

    private function add(BookingCheckOut $checkOut, string $eventKey, string $importance = 'normal'): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($checkOut->booking()->firstOrFail(), $eventKey, [
            'source_type' => 'check_out',
            'source_id' => $checkOut->id,
            'importance_level' => $importance,
        ]);
    }
}
