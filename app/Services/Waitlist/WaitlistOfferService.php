<?php

namespace App\Services\Waitlist;

use App\Actions\Bookings\BookingSubmit;
use App\Data\Waitlist\PriceData;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class WaitlistOfferService
{
    public function __construct(
        private readonly WaitlistQueueService $queue,
        private readonly WaitlistNotificationService $notifications,
        private readonly WaitlistAutoRequestService $autoRequests,
    ) {}

    public function createOffer(WaitlistItem $item, PriceData $priceData): WaitlistOffer
    {
        return DB::transaction(function () use ($item, $priceData): WaitlistOffer {
            $item = WaitlistItem::query()
                ->with(['sleepingPlace', 'user:id'])
                ->lockForUpdate()
                ->findOrFail($item->id);

            $existingOffer = $this->activeOfferForItem($item);

            if ($existingOffer instanceof WaitlistOffer) {
                return $existingOffer->fresh();
            }

            WaitlistOffer::query()
                ->where('waitlist_item_id', $item->id)
                ->active()
                ->whereNotNull('offer_expires_at')
                ->where('offer_expires_at', '<=', now())
                ->update([
                    'status' => 'expired',
                    'expired_at' => now(),
                    'skipped_at' => now(),
                ]);

            $eligibility = $this->queue->isEligible($item, $item->sleepingPlace, $priceData);

            if (! $eligibility->eligible) {
                throw ValidationException::withMessages([
                    'waitlist' => __('waitlist.messages.not_available_anymore'),
                ]);
            }

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
            $this->notifications->notifyHostHasWaitingGuests($item->sleepingPlace);

            $booking = $this->autoRequests->sendRequestToHost($item->fresh());

            if ($booking instanceof Booking) {
                $offer->update([
                    'status' => 'converted_to_booking',
                    'accepted_at' => now(),
                    'booking_id' => $booking->id,
                    'system_note' => 'waitlist.messages.auto_request_sent',
                ]);

                $item->update([
                    'status' => 'awaiting_host',
                    'completed_at' => null,
                ]);
            }

            return $offer->fresh();
        });
    }

    /**
     * @throws ValidationException
     */
    public function accept(User $user, WaitlistOffer $offer): Booking
    {
        Gate::forUser($user)->authorize('respond', $offer);

        return DB::transaction(function () use ($user, $offer): Booking {
            $offer = WaitlistOffer::query()
                ->with(['waitlistItem', 'sleepingPlace'])
                ->lockForUpdate()
                ->findOrFail($offer->id);

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
        });
    }

    public function decline(User $user, WaitlistOffer $offer): ?WaitlistOffer
    {
        Gate::forUser($user)->authorize('respond', $offer);

        return DB::transaction(function () use ($offer): ?WaitlistOffer {
            $offer = WaitlistOffer::query()
                ->with('waitlistItem.sleepingPlace')
                ->lockForUpdate()
                ->findOrFail($offer->id);

            $offer->update([
                'status' => 'declined',
                'declined_at' => now(),
                'skipped_at' => now(),
            ]);

            $item = $this->markItemSkipped($offer->waitlistItem);
            $this->notifications->notifySkippedToNext($item);

            return $this->queue->skipToNext($offer->fresh());
        });
    }

    public function expire(WaitlistOffer $offer): ?WaitlistOffer
    {
        return DB::transaction(function () use ($offer): ?WaitlistOffer {
            $offer = WaitlistOffer::query()
                ->with('waitlistItem.sleepingPlace')
                ->lockForUpdate()
                ->findOrFail($offer->id);

            if ($offer->status !== 'active') {
                return null;
            }

            $offer->update([
                'status' => 'expired',
                'expired_at' => now(),
                'skipped_at' => now(),
            ]);

            $item = $this->markItemSkipped($offer->waitlistItem);

            $this->notifications->notifyOfferExpired($offer->fresh());
            $this->notifications->notifySkippedToNext($item);

            return $this->queue->skipToNext($offer->fresh());
        });
    }

    public function convertToBookingDraft(WaitlistOffer $offer): ?Booking
    {
        return $this->autoRequests->createBookingDraft($offer);
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

    private function activeOfferForItem(WaitlistItem $item): ?WaitlistOffer
    {
        return WaitlistOffer::query()
            ->where('waitlist_item_id', $item->id)
            ->active()
            ->where(function ($query): void {
                $query->whereNull('offer_expires_at')
                    ->orWhere('offer_expires_at', '>', now());
            })
            ->first();
    }

    private function markItemSkipped(WaitlistItem $item): WaitlistItem
    {
        $skippedCount = (int) $item->skipped_count + 1;

        $item->update([
            'status' => $skippedCount >= (int) $item->max_skips ? 'expired' : 'active',
            'skipped_count' => $skippedCount,
        ]);

        return $item->fresh();
    }
}
