<?php

namespace App\Services\Waitlist;

use App\Actions\Bookings\BookingSubmit;
use App\Data\Waitlist\PriceData;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class WaitlistOfferService
{
    public function __construct(
        private readonly WaitlistQueueService $queue,
        private readonly WaitlistNotificationService $notifications,
    ) {}

    public function createOffer(WaitlistItem $item, PriceData $priceData): WaitlistOffer
    {
        $item->loadMissing('sleepingPlace');

        $offer = WaitlistOffer::query()->create([
            'waitlist_item_id' => $item->id,
            'user_id' => $item->user_id,
            'property_id' => $item->property_id,
            'room_id' => $item->room_id,
            'sleeping_place_id' => $item->sleeping_place_id,
            'status' => 'active',
            'offered_at' => now(),
            'offer_expires_at' => now()->addMinutes(30),
            'current_price_per_night' => $priceData->pricePerNight,
            'current_total_price' => $priceData->totalPrice,
            'current_deposit' => $priceData->deposit,
            'currency' => $priceData->currency,
            'hold_started_at' => now(),
            'hold_expires_at' => now()->addMinutes(30),
        ]);

        $item->update([
            'status' => 'offered',
            'offered_count' => (int) $item->offered_count + 1,
            'last_offered_at' => now(),
        ]);

        $this->notifications->notifyOfferCreated($offer->fresh());

        return $offer->fresh();
    }

    /**
     * @throws ValidationException
     */
    public function accept(User $user, WaitlistOffer $offer): Booking
    {
        Gate::forUser($user)->authorize('respond', $offer);

        $offer = $offer->fresh(['waitlistItem', 'sleepingPlace']);

        if ($offer->status !== 'active' || ($offer->offer_expires_at !== null && $offer->offer_expires_at->isPast())) {
            throw ValidationException::withMessages([
                'offer' => __('waitlist.messages.offer_expired'),
            ]);
        }

        $item = $offer->waitlistItem;
        $range = $this->queue->rangeForItem($item);

        $booking = app(BookingSubmit::class)->handle($user, $offer->sleepingPlace, [
            'check_in' => $range->checkIn->toDateString(),
            'check_out' => $range->checkOut->toDateString(),
            'guests_count' => max(1, (int) $item->guests_count),
            'guest_message' => $item->guest_message,
            'rules_accepted' => true,
            'profile_ready' => true,
            'payment_mode' => 'pay_now',
        ]);

        $booking->forceFill(['payment_status' => PaymentStatus::Unpaid])->save();

        $offer->update([
            'status' => 'converted_to_booking',
            'accepted_at' => now(),
            'booking_id' => $booking->id,
        ]);

        app(WaitlistService::class)->markCompleted($item->fresh());

        return $booking->fresh();
    }

    public function decline(User $user, WaitlistOffer $offer): ?WaitlistOffer
    {
        Gate::forUser($user)->authorize('respond', $offer);

        $offer = $offer->fresh(['waitlistItem.sleepingPlace']);
        $offer->update([
            'status' => 'declined',
            'declined_at' => now(),
            'skipped_at' => now(),
        ]);

        $item = $offer->waitlistItem;
        $item->update([
            'status' => 'active',
            'skipped_count' => (int) $item->skipped_count + 1,
        ]);

        return $this->queue->skipToNext($offer->fresh());
    }

    public function expire(WaitlistOffer $offer): ?WaitlistOffer
    {
        $offer = $offer->fresh(['waitlistItem.sleepingPlace']);
        $offer->update([
            'status' => 'expired',
            'expired_at' => now(),
            'skipped_at' => now(),
        ]);

        $item = $offer->waitlistItem;
        $item->update([
            'status' => 'active',
            'skipped_count' => (int) $item->skipped_count + 1,
        ]);

        $this->notifications->notifyOfferExpired($offer->fresh());

        return $this->queue->skipToNext($offer->fresh());
    }

    public function convertToBookingDraft(WaitlistOffer $offer): ?Booking
    {
        return null;
    }

    public function holdAvailability(WaitlistOffer $offer): void
    {
        $offer->update([
            'hold_started_at' => now(),
            'hold_expires_at' => now()->addMinutes(30),
        ]);
    }

    public function releaseHold(WaitlistOffer $offer): void
    {
        $offer->update([
            'hold_expires_at' => now(),
        ]);
    }
}
