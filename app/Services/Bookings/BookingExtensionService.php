<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingExtension;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingExtensionService
{
    public function __construct(
        private readonly BookingExtensionNumberService $numbers,
        private readonly BookingExtensionValidationService $validation,
        private readonly BookingExtensionPriceService $pricing,
        private readonly BookingExtensionLineService $lines,
        private readonly BookingExtensionHoldService $holds,
        private readonly BookingExtensionEventService $events,
        private readonly BookingExtensionNotificationService $notifications,
        private readonly BookingExtensionPrivacyService $privacy,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRequest(User $guest, Booking $booking, array $data): BookingExtension
    {
        $booking = $this->loadBooking($booking);

        if (! $this->privacy->canGuestCreate($guest, $booking)) {
            throw new AuthorizationException(__('booking_extensions.messages.not_allowed'));
        }

        $extension = $this->createDraftExtension($guest, $booking, $data);
        $newCheckOut = $this->newCheckoutFromData($data);
        $results = $this->validationResults($booking, $newCheckOut, $data, $extension);

        $results->each(fn (array $result): mixed => $this->validation->createValidationResult($extension, $result));
        $blocking = $results->filter(fn (array $result): bool => (bool) $result['blocking']);

        if ($blocking->isNotEmpty()) {
            $extension->forceFill(['status' => 'availability_check_failed'])->save();
            $this->events->record($extension->refresh(), 'availability_checked', ['blocking' => true]);

            throw ValidationException::withMessages([
                'new_check_out_date' => __($blocking->first()['message_key'], $blocking->first()['message_params_json'] ?? []),
            ]);
        }

        return DB::transaction(function () use ($extension): BookingExtension {
            $extension->refresh();
            $price = $this->pricing->priceData($extension);
            unset($price['date_prices']);
            $requiresHost = $this->requiresHostConfirmation($extension);
            $requiresPayment = $price['total_payable'] > 0;
            $nextStatus = $requiresHost
                ? 'waiting_host_confirmation'
                : ($requiresPayment ? 'approved_waiting_payment' : 'approved');

            $extension->forceFill([
                ...$price,
                'additional_amount' => $price['accommodation_amount'],
                'extra_amount' => $price['accommodation_amount'],
                'total_extra' => $price['total_payable'],
                'new_total' => $this->money((float) ($extension->booking?->total_amount ?: $extension->booking?->total ?: 0) + $price['total_payable']),
                'extension_type' => $requiresHost ? 'host_approval_extension' : 'instant_extension',
                'requires_host_confirmation' => $requiresHost,
                'requires_host_approval' => $requiresHost,
                'requires_payment' => $requiresPayment,
                'payment_required' => $requiresPayment,
                'payment_status' => $requiresPayment ? 'unpaid' : 'not_required',
                'payment_deadline_at' => $requiresPayment ? now()->addMinutes(30) : null,
                'hold_expires_at' => now()->addMinutes(30),
                'expires_at' => now()->addHours(24),
                'status' => $nextStatus,
            ])->save();

            $this->lines->rebuildLines($extension->refresh());
            $this->validation->createValidationResult($extension, $this->validation->result('host_confirmation_required', blocking: false, severity: 'warning'));

            if ($requiresPayment) {
                $this->validation->createValidationResult($extension, $this->validation->result('payment_required', blocking: false, severity: 'info'));
            }

            $this->holds->createTemporaryHold($extension->refresh());
            $this->events->record($extension, 'extension_requested');
            $this->events->record($extension, 'availability_checked');
            $this->events->record($extension, 'quote_created');

            if ($requiresHost) {
                $this->events->record($extension, 'host_confirmation_requested');
                $this->notifications->notifyHostExtensionRequested($extension->refresh());
            } elseif ($requiresPayment) {
                app(BookingExtensionPaymentService::class)->createPaymentIfRequired($extension->refresh());
                $this->notifications->notifyGuestPaymentRequired($extension->refresh());
            }

            return $extension->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromCheckout(User $guest, BookingCheckOut $checkOut, array $data): BookingExtension
    {
        return $this->createRequest($guest, $checkOut->booking()->firstOrFail(), $data);
    }

    /**
     * @return Collection<int, BookingExtension>
     */
    public function getForGuest(User $guest, Booking $booking): Collection
    {
        abort_unless((int) $booking->guest_user_id === (int) $guest->id, 403);

        return $booking->extensions()
            ->with(['lines', 'validationResults'])
            ->latest('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, BookingExtension>
     */
    public function getForHost(User $host, array $filters): CursorPaginator
    {
        return BookingExtension::query()
            ->with(['booking:id,booking_number,guest_user_id,host_user_id', 'guest:id,name', 'sleepingPlace:id,display_name'])
            ->where('host_user_id', $host->id)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->cursorPaginate(15);
    }

    public function cancelByGuest(User $guest, BookingExtension $extension): BookingExtension
    {
        $extension->loadMissing('booking');

        if (! $this->privacy->canGuestView($guest, $extension)) {
            throw new AuthorizationException(__('booking_extensions.messages.not_allowed'));
        }

        $extension->forceFill([
            'status' => 'cancelled_by_guest',
            'cancelled_at' => now(),
        ])->save();

        $this->holds->releaseHold($extension, 'cancelled_by_guest');
        $this->events->record($extension->refresh(), 'extension_cancelled', ['user_id' => $guest->id]);
        $this->notifications->notifyExtensionCancelled($extension->refresh());

        return $extension->refresh();
    }

    public function cancelByHost(User $host, BookingExtension $extension): BookingExtension
    {
        if (! $this->privacy->canHostRespond($host, $extension)) {
            throw new AuthorizationException(__('booking_extensions.messages.not_allowed'));
        }

        $extension->forceFill([
            'status' => 'cancelled_by_host',
            'cancelled_at' => now(),
        ])->save();

        $this->holds->releaseHold($extension, 'cancelled_by_host');
        $this->events->record($extension->refresh(), 'extension_cancelled', ['user_id' => $host->id]);
        $this->notifications->notifyExtensionCancelled($extension->refresh());

        return $extension->refresh();
    }

    public function markExpired(BookingExtension $extension): BookingExtension
    {
        $extension->forceFill([
            'status' => 'expired',
            'closed_at' => now(),
        ])->save();

        $this->holds->releaseHold($extension, 'expired');
        $this->events->record($extension->refresh(), 'extension_expired');
        $this->notifications->notifyExtensionExpired($extension->refresh());

        return $extension->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createDraftExtension(User $guest, Booking $booking, array $data): BookingExtension
    {
        $place = $booking->sleepingPlace;
        $stay = $booking->stay()->first();
        $current = $this->currentCheckout($booking);
        $new = $this->newCheckoutFromData($data);
        $currentTime = $this->timeValue($booking->check_out_time);
        $newTime = $this->timeValue($data['new_check_out_time'] ?? $booking->check_out_time);

        return BookingExtension::query()->create([
            'extension_number' => $this->numbers->generate(),
            'booking_id' => $booking->id,
            'booking_stay_id' => $stay?->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'current_checkout_date' => $current->toDateString(),
            'requested_new_checkout_date' => $new->toDateString(),
            'current_check_out_date' => $current->toDateString(),
            'current_check_out_time' => $currentTime,
            'new_check_out_date' => $new->toDateString(),
            'new_check_out_time' => $newTime,
            'additional_nights' => max(0, (int) $current->diffInDays($new)),
            'additional_nights_count' => max(0, (int) $current->diffInDays($new)),
            'additional_chargeable_days_count' => max(0, (int) $current->diffInDays($new)),
            'additional_calendar_presence_days_count' => max(0, (int) $current->diffInDays($new)) + 1,
            'original_check_out' => $current->toDateString(),
            'new_check_out' => $new->toDateString(),
            'extra_nights' => max(0, (int) $current->diffInDays($new)),
            'extension_type' => $this->extensionType($booking, $new),
            'status' => 'draft',
            'requires_host_confirmation' => (bool) ($place?->requires_host_approval ?? true),
            'requires_host_approval' => (bool) ($place?->requires_host_approval ?? true),
            'requires_payment' => true,
            'payment_required' => true,
            'payment_status' => 'unpaid',
            'currency' => $booking->currency ?: $place?->currency ?: 'EUR',
            'guest_message' => $data['guest_message'] ?? null,
            'hold_dates' => true,
            'extra_amount' => 0,
            'additional_amount' => 0,
            'accommodation_amount' => 0,
            'discount_amount' => 0,
            'service_fee_amount' => 0,
            'cleaning_fee_amount' => 0,
            'additional_deposit_amount' => 0,
            'total_payable' => 0,
            'total_extra' => 0,
            'new_total' => $booking->total_amount ?: $booking->total ?: 0,
            'host_payout_amount' => 0,
            'refundable_amount' => 0,
            'non_refundable_amount' => 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, array<string, mixed>>
     */
    private function validationResults(Booking $booking, CarbonInterface $newCheckOut, array $data, BookingExtension $extension): Collection
    {
        return collect()
            ->merge($this->validation->validateNewCheckoutDate($booking, $data))
            ->merge($this->validation->validateMaxStay($booking, $newCheckOut))
            ->merge($this->validation->validateGuestEligibility($booking))
            ->merge($this->validation->validateGuestCount($booking))
            ->merge($this->validation->validateBlocksAndRepairs($booking, $newCheckOut, $extension))
            ->merge($this->validation->validateDisputesAndComplaints($booking))
            ->unique('validation_key')
            ->values();
    }

    private function loadBooking(Booking $booking): Booking
    {
        return Booking::query()
            ->with([
                'guest:id,name',
                'host:id,name',
                'sleepingPlace:id,room_id,property_id,display_name,status,max_nights,max_guests,max_guests_count,base_price_per_night,weekly_price,monthly_price,weekend_price,cleaning_fee,deposit_amount,currency,instant_booking_enabled,requires_host_approval,extensions_allowed,can_extend',
                'stay:id,booking_id,planned_check_out_date,status,extension_requested,checkout_soon,nights_count,nights_remaining',
                'checkOut:id,booking_id,booking_stay_id,check_out_date,planned_check_out_time,status',
            ])
            ->findOrFail($booking->id);
    }

    private function requiresHostConfirmation(BookingExtension $extension): bool
    {
        $place = $extension->sleepingPlace;

        return (bool) ($place?->requires_host_approval ?? true)
            || ! (bool) ($place?->instant_booking_enabled ?? false)
            || $extension->extension_type === 'same_day_extension'
            || (bool) $extension->booking?->has_complaint
            || (bool) $extension->booking?->has_dispute;
    }

    private function extensionType(Booking $booking, CarbonInterface $newCheckOut): string
    {
        if ($this->currentCheckout($booking)->isSameDay(now())) {
            return 'same_day_extension';
        }

        $checkIn = CarbonImmutable::parse($booking->check_in_date ?? $booking->check_in)->startOfDay();

        if ($checkIn->diffInDays($this->date($newCheckOut)) >= 30) {
            return 'long_stay_extension';
        }

        return 'host_approval_extension';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function newCheckoutFromData(array $data): CarbonImmutable
    {
        return $this->date($data['new_check_out_date'] ?? $data['requested_new_checkout_date'] ?? $data['new_checkout_date']);
    }

    private function currentCheckout(Booking $booking): CarbonImmutable
    {
        return $this->date($booking->check_out_date ?? $booking->check_out);
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }

    private function timeValue(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('H:i');
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
