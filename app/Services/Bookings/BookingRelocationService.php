<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingRelocation;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingRelocationService
{
    public function __construct(
        private readonly BookingRelocationNumberService $numbers,
        private readonly BookingRelocationAvailabilityService $availability,
        private readonly BookingRelocationValidationService $validation,
        private readonly BookingRelocationPriceService $pricing,
        private readonly BookingRelocationLineService $lines,
        private readonly BookingRelocationConsentService $consents,
        private readonly BookingRelocationHoldService $holds,
        private readonly BookingRelocationEventService $events,
        private readonly BookingRelocationNotificationService $notifications,
        private readonly BookingRelocationPrivacyService $privacy,
        private readonly BookingRelocationInventoryService $inventory,
        private readonly BookingRelocationRefundService $refunds,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromGuestRequest(User $guest, Booking $booking, array $data): BookingRelocation
    {
        $booking = $this->loadBooking($booking);

        if (! $this->privacy->canGuestCreate($guest, $booking)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        return $this->createRelocation($booking, $guest, 'guest', $data, null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromHostOffer(User $host, Booking $booking, SleepingPlace $newPlace, array $data): BookingRelocation
    {
        $booking = $this->loadBooking($booking);

        if ((int) $booking->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        return $this->createRelocation($booking, $host, 'host', [
            ...$data,
            'new_sleeping_place_id' => $newPlace->id,
        ], $newPlace);
    }

    public function createFromComplaint(mixed $complaint, SleepingPlace $newPlace): BookingRelocation
    {
        return $this->createFromSource($complaint, $newPlace, 'complaint');
    }

    public function createFromMaintenance(mixed $request, SleepingPlace $newPlace): BookingRelocation
    {
        return $this->createFromSource($request, $newPlace, 'maintenance');
    }

    public function createFromMismatchReport(mixed $mismatchReport, SleepingPlace $newPlace): BookingRelocation
    {
        return $this->createFromSource($mismatchReport, $newPlace, 'mismatch');
    }

    public function cancel(User $user, BookingRelocation $relocation, ?string $reason = null): BookingRelocation
    {
        if (! $this->privacy->canGuestView($user, $relocation) && ! $this->privacy->canHostView($user, $relocation)) {
            throw new AuthorizationException(__('booking_relocations.messages.not_allowed'));
        }

        $status = (int) $user->id === (int) $relocation->guest_user_id ? 'cancelled_by_guest' : 'cancelled_by_host';
        $relocation->forceFill([
            'status' => $status,
            'cancelled_at' => now(),
            'guest_comment' => (int) $user->id === (int) $relocation->guest_user_id ? $reason : $relocation->guest_comment,
            'host_comment' => (int) $user->id === (int) $relocation->host_user_id ? $reason : $relocation->host_comment,
        ])->save();

        $this->holds->releaseNewPlaceHold($relocation->refresh(), $status);
        $this->events->record($relocation, 'relocation_cancelled', ['reason' => $reason, 'user_id' => $user->id]);
        $this->notifications->notifyRelocationCancelled($relocation->refresh());

        return $relocation->refresh();
    }

    public function markExpired(BookingRelocation $relocation): BookingRelocation
    {
        $relocation->forceFill([
            'status' => 'expired',
            'closed_at' => now(),
        ])->save();

        $this->holds->releaseNewPlaceHold($relocation->refresh(), 'expired');
        $this->events->record($relocation, 'relocation_cancelled', ['reason' => 'expired']);

        return $relocation->refresh();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, BookingRelocation>
     */
    public function getForHost(User $host, array $filters): CursorPaginator
    {
        return BookingRelocation::query()
            ->with(['guest:id,name', 'currentRoom:id,title', 'newRoom:id,title', 'currentSleepingPlace:id,display_name', 'newSleepingPlace:id,display_name'])
            ->where('host_user_id', $host->id)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->cursorPaginate(15);
    }

    /**
     * @return Collection<int, BookingRelocation>
     */
    public function getForGuest(User $guest, Booking $booking): Collection
    {
        abort_unless((int) $booking->guest_user_id === (int) $guest->id, 403);

        return $booking->relocations()
            ->with(['newSleepingPlace', 'options'])
            ->latest('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createRelocation(Booking $booking, User $actor, string $requestedByType, array $data, ?SleepingPlace $newPlace): BookingRelocation
    {
        $newPlace ??= isset($data['new_sleeping_place_id'])
            ? SleepingPlace::query()->with(['property', 'room'])->find($data['new_sleeping_place_id'])
            : null;

        $relocation = $this->draftRelocation($booking, $actor, $requestedByType, $data, $newPlace);

        return DB::transaction(function () use ($relocation, $requestedByType): BookingRelocation {
            $blockingResults = $this->blockingValidationResults($relocation);
            $blockingResults->each(fn (array $result): mixed => $this->validation->createValidationResult($relocation, $result));

            if ($blockingResults->isNotEmpty()) {
                $relocation->forceFill(['status' => 'failed'])->save();
                $this->events->record($relocation->refresh(), 'availability_checked', ['blocking' => true]);

                throw ValidationException::withMessages([
                    'new_sleeping_place_id' => __($blockingResults->first()['message_key']),
                ]);
            }

            if ($relocation->new_sleeping_place_id) {
                $price = $this->pricing->priceData($relocation->refresh());
                $relocation->forceFill([
                    ...$price,
                    'payment_deadline_at' => $price['requires_payment'] ? now()->addMinutes(30) : null,
                    'hold_expires_at' => now()->addMinutes(30),
                    'expires_at' => now()->addHours(24),
                ])->save();

                $this->lines->rebuildLines($relocation->refresh());
                $this->holds->createNewPlaceHold($relocation->refresh());
                $this->inventory->prepareInventoryTransfer($relocation->refresh());
            }

            $this->createNonBlockingValidationResults($relocation->refresh());
            $this->createRequiredConsents($relocation->refresh(), $requestedByType);
            $this->createRefundIfNeeded($relocation->refresh());
            $this->events->record($relocation->refresh(), 'relocation_requested');
            $this->events->record($relocation->refresh(), 'availability_checked');
            $this->events->record($relocation->refresh(), 'price_difference_calculated');

            if ($requestedByType === 'host') {
                $this->notifications->notifyGuestRelocationOffered($relocation->refresh());
            } else {
                $this->notifications->notifyHostRelocationRequested($relocation->refresh());
            }

            return $relocation->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function draftRelocation(Booking $booking, User $actor, string $requestedByType, array $data, ?SleepingPlace $newPlace): BookingRelocation
    {
        $relocationDate = CarbonImmutable::parse($data['relocation_date'] ?? now()->toDateString())->startOfDay();
        $originalCheckIn = CarbonImmutable::parse($booking->check_in_date ?? $booking->check_in)->startOfDay();
        $originalCheckOut = CarbonImmutable::parse($booking->check_out_date ?? $booking->check_out)->startOfDay();
        $status = $this->initialStatus($requestedByType, $newPlace);

        return BookingRelocation::query()->create([
            'relocation_number' => $this->numbers->generate(),
            'original_booking_id' => $booking->id,
            'new_booking_id' => null,
            'booking_stay_id' => $booking->stay()->value('id'),
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'current_property_id' => $booking->property_id,
            'current_room_id' => $booking->room_id,
            'current_sleeping_place_id' => $booking->sleeping_place_id,
            'new_property_id' => $newPlace?->property_id,
            'new_room_id' => $newPlace?->room_id,
            'new_sleeping_place_id' => $newPlace?->id,
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'requested_by_user_id' => $actor->id,
            'requested_by_type' => $requestedByType,
            'reason' => $data['reason'] ?? 'other',
            'status' => $status,
            'relocation_date' => $relocationDate->toDateString(),
            'relocation_time' => $data['relocation_time'] ?? null,
            'check_in_date' => $relocationDate->toDateString(),
            'check_out_date' => $originalCheckOut->toDateString(),
            'original_check_in_date' => $originalCheckIn->toDateString(),
            'original_check_out_date' => $originalCheckOut->toDateString(),
            'old_period_check_in_date' => $originalCheckIn->toDateString(),
            'old_period_check_out_date' => $relocationDate->toDateString(),
            'new_period_check_in_date' => $relocationDate->toDateString(),
            'new_period_check_out_date' => $originalCheckOut->toDateString(),
            'currency' => $booking->currency ?: $newPlace?->currency ?: 'EUR',
            'requires_guest_consent' => $newPlace !== null,
            'requires_host_consent' => $requestedByType === 'guest',
            'requires_payment' => false,
            'payment_status' => 'not_required',
            'requires_refund' => false,
            'guest_comment' => $data['guest_comment'] ?? null,
            'host_comment' => $data['host_comment'] ?? null,
            'support_comment' => $data['support_comment'] ?? null,
            'hold_dates' => true,
            'hold_expires_at' => $newPlace ? now()->addMinutes(30) : null,
            'expires_at' => now()->addHours(24),
        ])->loadMissing(['originalBooking', 'currentSleepingPlace', 'newSleepingPlace']);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function blockingValidationResults(BookingRelocation $relocation): Collection
    {
        if (! $relocation->new_sleeping_place_id) {
            return collect();
        }

        return $this->validation->validateNewSleepingPlace($relocation)
            ->merge($this->availability->getBlockingReasons($relocation)->map(fn (string $reason): array => $this->validation->result($reason)));
    }

    private function createNonBlockingValidationResults(BookingRelocation $relocation): void
    {
        $results = $this->validation->validatePriceDifferenceConsent($relocation)
            ->merge($this->validation->validateHostConsent($relocation))
            ->merge($this->validation->validateGuestConsent($relocation))
            ->merge($this->validation->validateOldPlaceAfterMove($relocation));

        $results->each(fn (array $result): mixed => $this->validation->createValidationResult($relocation, $result));
    }

    private function createRequiredConsents(BookingRelocation $relocation, string $requestedByType): void
    {
        if ($relocation->requires_guest_consent) {
            $this->consents->requestGuestConsent($relocation);
            $this->events->record($relocation, 'guest_consent_requested');
            $this->notifications->notifyGuestConsentRequired($relocation);
        }

        if ($relocation->requires_host_consent) {
            $this->consents->requestHostConsent($relocation);
            $this->events->record($relocation, 'host_consent_requested');
            $this->notifications->notifyHostConsentRequired($relocation);
        }

        if ($requestedByType === 'guest') {
            $relocation->forceFill(['status' => 'waiting_host_consent'])->save();
        }

        if ($requestedByType === 'host') {
            $relocation->forceFill(['status' => 'waiting_guest_consent'])->save();
        }
    }

    private function createRefundIfNeeded(BookingRelocation $relocation): void
    {
        if ($relocation->requires_refund) {
            $this->refunds->createRefundIfNeeded($relocation);
        }
    }

    private function createFromSource(mixed $source, SleepingPlace $newPlace, string $sourceType): BookingRelocation
    {
        $booking = $source->booking ?? Booking::query()->findOrFail($source->booking_id);
        $host = $booking->host()->firstOrFail();

        return $this->createFromHostOffer($host, $booking, $newPlace, [
            'reason' => $sourceType === 'maintenance' ? 'maintenance_issue' : 'complaint_resolution',
            'relocation_date' => now()->toDateString(),
            'source_type' => $sourceType,
            'source_id' => $source->id ?? null,
        ]);
    }

    private function loadBooking(Booking $booking): Booking
    {
        return $booking->loadMissing(['guest', 'host', 'property', 'room', 'sleepingPlace', 'stay']);
    }

    private function initialStatus(string $requestedByType, ?SleepingPlace $newPlace): string
    {
        if (! $newPlace) {
            return 'options_searching';
        }

        return $requestedByType === 'host' ? 'waiting_guest_consent' : 'waiting_host_consent';
    }
}
