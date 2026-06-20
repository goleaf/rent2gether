<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\NotificationService;
use App\Services\PricingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookingSubmit
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function handle(User $guest, SleepingPlace $sleepingPlace, array $data): Booking
    {
        $validated = Validator::make($data, [
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'guests_count' => ['required', 'integer', 'min:1'],
            'guest_message' => ['nullable', 'string', 'max:1000'],
            'rules_accepted' => ['accepted'],
            'profile_ready' => ['accepted'],
            'payment_mode' => ['nullable', 'in:pay_now,pay_later'],
        ], [], $this->validationAttributes())->validate();

        return DB::transaction(function () use ($guest, $sleepingPlace, $validated): Booking {
            $place = $this->lockSleepingPlace($sleepingPlace);
            $host = $place->property?->host;

            if (! $host instanceof User || $host->id === $guest->id) {
                throw ValidationException::withMessages([
                    'sleepingPlace' => __('booking.flow.errors.host_unavailable'),
                ]);
            }

            $checkIn = CarbonImmutable::parse($validated['check_in'])->startOfDay();
            $checkOut = CarbonImmutable::parse($validated['check_out'])->startOfDay();
            $guestsCount = (int) $validated['guests_count'];
            $nights = (int) $checkIn->diffInDays($checkOut);

            $this->ensureStayFitsPlace($place, $checkIn, $checkOut, $guestsCount, $nights);

            $availability = app(AvailabilityService::class);

            if (! $availability->isAvailable($place, $checkIn, $checkOut)) {
                throw ValidationException::withMessages([
                    'checkIn' => __('booking.flow.errors.not_available'),
                ]);
            }

            $quote = app(PricingService::class)
                ->calculate($guest, $place, $checkIn, $checkOut, $guestsCount)
                ->toArray();

            [$status, $paymentStatus, $bookingType] = $this->initialState($place, (string) ($validated['payment_mode'] ?? 'pay_now'));
            $cancellationPolicy = $this->cancellationPolicy($place);
            $holdExpiresAt = $this->holdExpiresAt($status, $quote);
            $arrivalTime = $validated['arrival_time'] ?? $validated['check_in_time'] ?? null;

            $booking = Booking::query()->create([
                'bed_id' => null,
                'guest_id' => $guest->id,
                'guest_user_id' => $guest->id,
                'host_id' => $host->id,
                'host_user_id' => $host->id,
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
                'sleeping_place_id' => $place->id,
                'booking_type' => $bookingType,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'check_in_time' => $validated['check_in_time'] ?? null,
                'check_out_time' => $validated['check_out_time'] ?? $this->defaultCheckOutTime($place),
                'arrival_time' => $arrivalTime,
                'guests_count' => $guestsCount,
                'nights' => $quote['nights_count'],
                'nights_count' => $quote['nights_count'],
                'calendar_days_count' => $quote['calendar_days_count'],
                'price_per_night' => $place->base_price_per_night,
                'subtotal' => $quote['subtotal_amount'],
                'subtotal_amount' => $quote['subtotal_amount'],
                'discount_amount' => $quote['weekly_discount_amount'] + $quote['monthly_discount_amount'],
                'cleaning_fee' => $quote['cleaning_fee_amount'],
                'cleaning_fee_amount' => $quote['cleaning_fee_amount'],
                'deposit' => $quote['deposit_amount'],
                'deposit_amount' => $quote['deposit_amount'],
                'service_fee' => $quote['service_fee_amount'],
                'service_fee_amount' => $quote['service_fee_amount'],
                'tax_amount' => 0,
                'city_fee_amount' => 0,
                'total' => $quote['total_amount'],
                'total_amount' => $quote['total_amount'],
                'refundable_amount' => $quote['refundable_amount'],
                'non_refundable_amount' => $quote['non_refundable_amount'],
                'currency' => $quote['currency'],
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_deadline_at' => $quote['payment_deadline'],
                'availability_hold_expires_at' => $holdExpiresAt,
                'requires_document_check' => false,
                'requires_phone_check' => false,
                'requires_identity_check' => false,
                'cancellation_policy' => $cancellationPolicy,
                'refund_status' => 'none',
                'guest_message' => $validated['guest_message'] ?? null,
                'rules_accepted_at' => now(),
                'free_cancel_before' => $quote['cancellation_deadline'],
                'has_dispute' => false,
                'has_complaint' => false,
                'guest_review_left' => false,
                'host_review_left' => false,
            ]);

            foreach ($quote['line_items'] as $line) {
                $booking->priceLines()->create([
                    'type' => $line['type'],
                    'label_key' => $line['label_key'],
                    'amount' => $line['amount'],
                    'currency' => $line['currency'],
                    'is_refundable' => $line['is_refundable'],
                    'metadata_json' => $line['metadata'] ?? [],
                ]);
            }

            $booking->statusHistories()->create([
                'from_status' => null,
                'to_status' => $status->value,
                'changed_by_user_id' => $guest->id,
                'note' => 'booking.flow.status_history.created',
            ]);

            $availability->blockForBooking($booking);
            app(NotificationService::class)->notifyBookingCreated($booking);

            return $booking->load(['priceLines', 'statusHistories']);
        });
    }

    private function lockSleepingPlace(SleepingPlace $sleepingPlace): SleepingPlace
    {
        return SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'status',
                'max_guests',
                'base_price_per_night',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'cleaning_fee',
                'deposit_amount',
                'currency',
                'min_nights',
                'max_nights',
                'instant_booking_enabled',
                'requires_host_approval',
            ])
            ->with([
                'room:id,property_id,status',
                'property:id,host_user_id,status',
                'property.host:id',
                'property.host.hostProfile:id,user_id,default_check_out_time,default_cancellation_policy',
            ])
            ->lockForUpdate()
            ->findOrFail($sleepingPlace->id);
    }

    private function ensureStayFitsPlace(SleepingPlace $place, CarbonImmutable $checkIn, CarbonImmutable $checkOut, int $guestsCount, int $nights): void
    {
        if ($guestsCount > (int) $place->max_guests) {
            throw ValidationException::withMessages([
                'guestsCount' => trans_choice('booking.date_selector.errors.max_guests', (int) $place->max_guests, [
                    'count' => (int) $place->max_guests,
                ]),
            ]);
        }

        [$minNights, $maxNights] = $this->stayLimits($place, $checkIn, $checkOut);

        if ($nights < $minNights) {
            throw ValidationException::withMessages([
                'checkIn' => trans_choice('booking.date_selector.errors.min_nights', $minNights, [
                    'count' => $minNights,
                ]),
            ]);
        }

        if ($maxNights !== null && $nights > $maxNights) {
            throw ValidationException::withMessages([
                'checkOut' => trans_choice('booking.date_selector.errors.max_nights', $maxNights, [
                    'count' => $maxNights,
                ]),
            ]);
        }
    }

    /**
     * @return array{0:int,1:int|null}
     */
    private function stayLimits(SleepingPlace $place, CarbonImmutable $checkIn, CarbonImmutable $checkOut): array
    {
        $minNights = max(1, (int) ($place->min_nights ?: 1));
        $maxNights = $place->max_nights === null ? null : (int) $place->max_nights;

        $place->availabilityDays()
            ->select(['id', 'sleeping_place_id', 'min_nights_override', 'max_nights_override'])
            ->whereDate('date', '>=', $checkIn->toDateString())
            ->whereDate('date', '<', $checkOut->toDateString())
            ->where(function ($query): void {
                $query->whereNotNull('min_nights_override')
                    ->orWhereNotNull('max_nights_override');
            })
            ->get()
            ->each(function ($day) use (&$minNights, &$maxNights): void {
                if ($day->min_nights_override !== null) {
                    $minNights = max($minNights, (int) $day->min_nights_override);
                }

                if ($day->max_nights_override !== null) {
                    $override = (int) $day->max_nights_override;
                    $maxNights = $maxNights === null ? $override : min($maxNights, $override);
                }
            });

        return [$minNights, $maxNights];
    }

    /**
     * @return array{0:BookingStatus,1:PaymentStatus,2:BookingType}
     */
    private function initialState(SleepingPlace $place, string $paymentMode): array
    {
        if ((bool) $place->instant_booking_enabled && ! (bool) $place->requires_host_approval) {
            if ($paymentMode === 'pay_later') {
                return [BookingStatus::Confirmed, PaymentStatus::Unpaid, BookingType::Instant];
            }

            return [BookingStatus::AwaitingPayment, PaymentStatus::AwaitingPayment, BookingType::Instant];
        }

        return [BookingStatus::AwaitingHostApproval, PaymentStatus::Unpaid, BookingType::HostApproval];
    }

    private function cancellationPolicy(SleepingPlace $place): CancellationPolicy
    {
        $policy = $place->property?->host?->hostProfile?->default_cancellation_policy;

        return is_string($policy)
            ? (CancellationPolicy::tryFrom($policy) ?? CancellationPolicy::Flexible)
            : CancellationPolicy::Flexible;
    }

    private function holdExpiresAt(BookingStatus $status, array $quote): ?CarbonImmutable
    {
        return match ($status) {
            BookingStatus::AwaitingPayment => CarbonImmutable::parse($quote['payment_deadline']),
            BookingStatus::AwaitingHostApproval => CarbonImmutable::now()->addDay(),
            default => null,
        };
    }

    private function defaultCheckOutTime(SleepingPlace $place): ?string
    {
        return $place->property?->host?->hostProfile?->default_check_out_time ?: '10:00';
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('booking.flow.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }
}
