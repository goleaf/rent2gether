<?php

namespace App\Services\Waitlist;

use App\Data\Waitlist\DateRange;
use App\Data\Waitlist\WaitlistContext;
use App\Data\Waitlist\WaitlistJoinResult;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class WaitlistService
{
    public function __construct(
        private readonly WaitlistQueueService $queue,
        private readonly WaitlistNotificationService $notifications,
    ) {}

    public function join(User $user, SleepingPlace $place, WaitlistContext $context): WaitlistJoinResult
    {
        return DB::transaction(function () use ($user, $place, $context): WaitlistJoinResult {
            $range = $context->dateRange();
            $payload = $this->payloadForContext($user, $place, $context);

            $existing = WaitlistItem::query()
                ->forUser($user)
                ->forSleepingPlace($place)
                ->forDateRange($range)
                ->lockForUpdate()
                ->first();

            if ($existing instanceof WaitlistItem && in_array($existing->status, ['active', 'waiting', 'offered', 'awaiting_guest', 'awaiting_host', 'awaiting_payment'], true)) {
                $item = $existing->fresh();

                return new WaitlistJoinResult(
                    item: $item,
                    position: $this->queue->calculatePosition($item),
                    alreadyJoined: true,
                );
            }

            if ($existing instanceof WaitlistItem) {
                $existing->update([
                    ...$payload,
                    'status' => 'active',
                    'position' => null,
                    'skipped_count' => 0,
                    'cancelled_at' => null,
                    'completed_at' => null,
                    'last_checked_at' => null,
                    'last_offered_at' => null,
                ]);

                $item = $existing->fresh();
            } else {
                $item = WaitlistItem::query()->create($payload);
            }

            $this->queue->recalculatePositions($place);
            $this->notifications->notifyJoined($item->fresh());

            $item = $item->fresh();

            return new WaitlistJoinResult(
                item: $item,
                position: (int) $item->position,
            );
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, WaitlistItem $item, array $data): WaitlistItem
    {
        Gate::forUser($user)->authorize('update', $item);

        $allowed = collect($data)->only([
            'desired_check_in_date',
            'desired_check_out_date',
            'guests_count',
            'flexible_dates',
            'flexible_days',
            'min_nights',
            'max_nights',
            'max_price_per_night',
            'max_total_price',
            'max_deposit',
            'ready_to_book_immediately',
            'ready_to_pay_immediately',
            'auto_send_request',
            'auto_create_booking_draft',
            'notify_available',
            'notify_price_drop',
            'notify_similar_available',
            'notify_offer_expiring',
            'guest_message',
            'expires_at',
        ])->all();

        if (array_key_exists('desired_check_in_date', $allowed) || array_key_exists('desired_check_out_date', $allowed)) {
            $checkIn = (string) ($allowed['desired_check_in_date'] ?? $item->desired_check_in_date?->toDateString());
            $checkOut = (string) ($allowed['desired_check_out_date'] ?? $item->desired_check_out_date?->toDateString());
            $range = new DateRange($checkIn, $checkOut);

            if (! $range->valid()) {
                throw ValidationException::withMessages([
                    'desired_check_out_date' => __('validation.after', [
                        'attribute' => __('waitlist.check_out'),
                        'date' => __('waitlist.check_in'),
                    ]),
                ]);
            }

            $duplicate = WaitlistItem::query()
                ->forUser($user)
                ->forSleepingPlace((int) $item->sleeping_place_id)
                ->forDateRange($range)
                ->whereKeyNot($item->id)
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'desired_check_in_date' => __('waitlist.messages.already_joined_for_dates'),
                ]);
            }

            $allowed['desired_check_in'] = $range->checkIn->toDateString();
            $allowed['desired_check_out'] = $range->checkOut->toDateString();
            $allowed['desired_check_in_date'] = $range->checkIn->toDateString();
            $allowed['desired_check_out_date'] = $range->checkOut->toDateString();
            $allowed['nights_count'] = $range->nightsCount;
            $allowed['calendar_days_count'] = $range->calendarDaysCount;
            $allowed['position'] = null;
        }

        if (array_key_exists('max_price_per_night', $allowed)) {
            $allowed['max_price'] = $allowed['max_price_per_night'];
        }

        if (array_key_exists('ready_to_book_immediately', $allowed)) {
            $allowed['ready_to_book'] = $allowed['ready_to_book_immediately'];
        }

        if (array_key_exists('auto_send_request', $allowed)) {
            $allowed['auto_request'] = $allowed['auto_send_request'];
        }

        $item->update($allowed);
        $this->queue->recalculatePositions($item->sleepingPlace);

        return $item->fresh();
    }

    public function cancel(User $user, WaitlistItem $item): WaitlistItem
    {
        Gate::forUser($user)->authorize('cancel', $item);

        $item->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->queue->recalculatePositions($item->sleepingPlace);

        return $item->fresh();
    }

    public function pause(User $user, WaitlistItem $item): WaitlistItem
    {
        Gate::forUser($user)->authorize('update', $item);

        $item->update(['status' => 'paused']);
        $this->queue->recalculatePositions($item->sleepingPlace);

        return $item->fresh();
    }

    public function resume(User $user, WaitlistItem $item): WaitlistItem
    {
        Gate::forUser($user)->authorize('update', $item);

        $item->update(['status' => 'active']);
        $this->queue->recalculatePositions($item->sleepingPlace);

        return $item->fresh();
    }

    public function expireOldItems(): int
    {
        return WaitlistItem::query()
            ->active()
            ->where(function ($query): void {
                $query->where('desired_check_in_date', '<', now()->toDateString())
                    ->orWhere('expires_at', '<=', now());
            })
            ->update(['status' => 'expired']);
    }

    public function markCompleted(WaitlistItem $item): WaitlistItem
    {
        $item->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->queue->recalculatePositions($item->sleepingPlace);

        return $item->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadForContext(User $user, SleepingPlace $place, WaitlistContext $context): array
    {
        $range = $context->dateRange();

        return [
            'user_id' => $user->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'status' => 'active',
            'source' => $context->source,
            'desired_check_in' => $range->checkIn->toDateString(),
            'desired_check_out' => $range->checkOut->toDateString(),
            'desired_check_in_date' => $range->checkIn->toDateString(),
            'desired_check_out_date' => $range->checkOut->toDateString(),
            'nights_count' => $range->nightsCount,
            'calendar_days_count' => $range->calendarDaysCount,
            'guests_count' => max(1, $context->guestsCount),
            'flexible_dates' => $context->flexibleDates,
            'flexible_days' => $context->flexibleDays,
            'min_nights' => $context->minNights,
            'max_nights' => $context->maxNights,
            'max_price' => $context->maxPricePerNight,
            'max_price_per_night' => $context->maxPricePerNight,
            'max_total_price' => $context->maxTotalPrice,
            'max_deposit' => $context->maxDeposit,
            'currency' => $context->currency ?: ($place->currency ?: 'EUR'),
            'ready_to_book' => $context->readyToBookImmediately,
            'ready_to_book_immediately' => $context->readyToBookImmediately,
            'ready_to_pay_immediately' => $context->readyToPayImmediately,
            'auto_request' => $context->autoSendRequest,
            'auto_send_request' => $context->autoSendRequest,
            'auto_create_booking_draft' => $context->autoCreateBookingDraft,
            'notify_available' => $context->notifyAvailable,
            'notify_price_drop' => $context->notifyPriceDrop,
            'notify_similar_available' => $context->notifySimilarAvailable,
            'notify_offer_expiring' => $context->notifyOfferExpiring,
            'guest_message' => $context->guestMessage,
            'expires_at' => $context->expiresAt,
            'added_at' => now(),
        ];
    }
}
