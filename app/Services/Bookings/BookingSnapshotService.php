<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingQuote;
use App\Services\Pricing\BookingPriceSnapshotService as PricingSnapshotService;

class BookingSnapshotService
{
    public function __construct(
        private readonly PricingSnapshotService $priceSnapshots,
    ) {}

    public function createAllSnapshots(Booking $booking, ?BookingQuote $quote): void
    {
        if ($quote instanceof BookingQuote) {
            $this->createPriceSnapshot($booking, $quote);
        }

        $this->createCancellationSnapshot($booking);
        $this->createDepositSnapshot($booking);
        $this->createListingSnapshot($booking);
        $this->createGuestIntakeSnapshot($booking);
        $this->createRulesSnapshot($booking);
    }

    public function createPriceSnapshot(Booking $booking, BookingQuote $quote): void
    {
        $this->priceSnapshots->createFromQuote($booking, $quote);
    }

    public function createCancellationSnapshot(Booking $booking): void
    {
        $this->mergeSnapshot($booking, 'cancellation', [
            'policy' => $this->value($booking->cancellation_policy),
            'free_cancel_before' => $booking->free_cancel_before?->toISOString(),
            'terms' => $booking->cancellation_terms,
        ]);
    }

    public function createDepositSnapshot(Booking $booking): void
    {
        $this->mergeSnapshot($booking, 'deposit', [
            'deposit_mode' => $booking->deposit_mode,
            'deposit_amount' => (float) $booking->deposit_amount,
            'currency' => $booking->currency,
            'has_deposit_issue' => (bool) $booking->has_deposit_issue,
        ]);
    }

    public function createListingSnapshot(Booking $booking): void
    {
        $booking->loadMissing(['sleepingPlace', 'room', 'property']);

        $this->mergeSnapshot($booking, 'listing', [
            'sleeping_place_id' => $booking->sleeping_place_id,
            'sleeping_place_title' => $booking->sleepingPlace?->display_name ?: $booking->sleepingPlace?->title,
            'room_id' => $booking->room_id,
            'room_name' => $booking->room?->name,
            'property_id' => $booking->property_id,
            'property_name' => $booking->property?->title ?: $booking->property?->name,
        ]);
    }

    public function createGuestIntakeSnapshot(Booking $booking): void
    {
        $booking->loadMissing(['guestIntake', 'bookingGuests']);

        $this->mergeSnapshot($booking, 'guest_intake', [
            'guests_count' => (int) $booking->guests_count,
            'guest_message' => $booking->guest_message,
            'booking_guests' => $booking->bookingGuests
                ->map(fn ($guest): array => [
                    'guest_type' => $guest->guest_type,
                    'is_main_guest' => (bool) $guest->is_main_guest,
                    'verification_status' => $guest->verification_status,
                ])
                ->values()
                ->all(),
        ]);
    }

    public function createRulesSnapshot(Booking $booking): void
    {
        $this->mergeSnapshot($booking, 'rules', [
            'rules_accepted_at' => $booking->rules_accepted_at?->toISOString(),
            'requires_phone_verification' => (bool) $booking->requires_phone_verification,
            'requires_identity_verification' => (bool) $booking->requires_identity_verification,
            'requires_document_verification' => (bool) $booking->requires_document_verification,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mergeSnapshot(Booking $booking, string $key, array $payload): void
    {
        $snapshot = $booking->nightly_price_snapshot ?: [];
        $snapshot['_snapshots'][$key] = $payload;

        $booking->forceFill([
            'nightly_price_snapshot' => $snapshot,
        ])->save();
    }

    private function value(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return $value === null ? null : (string) $value;
    }
}
