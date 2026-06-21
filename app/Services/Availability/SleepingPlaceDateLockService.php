<?php

namespace App\Services\Availability;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SleepingPlaceDateLockService
{
    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function createLocksForQuote(Model $quote): Collection
    {
        return $this->createLocksForGenericHold($quote, 'booking_quote_id', 'payment_pending', $quote->getAttribute('expires_at') ?? now()->addMinutes(20));
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function convertQuoteLocksToBooking(Model $quote, Booking $booking): Collection
    {
        SleepingPlaceBookingDateLock::query()
            ->where('booking_quote_id', $quote->getKey())
            ->where('status', 'active')
            ->update([
                'booking_id' => $booking->id,
                'lock_type' => 'booked',
                'expires_at' => null,
                'updated_at' => now(),
            ]);

        return $booking->sleepingPlaceDateLocks()
            ->where('booking_quote_id', $quote->getKey())
            ->orderBy('date')
            ->get();
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function createLocksForBooking(Booking $booking, string $lockType = 'booked'): Collection
    {
        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace) {
            throw ValidationException::withMessages([
                'sleeping_place_id' => __('availability.messages.missing_sleeping_place'),
            ]);
        }

        $start = $this->date($booking->check_in_date ?? $booking->check_in);
        $end = $this->date($booking->check_out_date ?? $booking->check_out);

        return $this->createLockRows($place, $start, $end, [
            'booking_id' => $booking->id,
            'lock_type' => $lockType,
            'expires_at' => $this->expiresAtForLockType($lockType, $booking->availability_hold_expires_at),
        ]);
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function createLocksForRequest(Model $request): Collection
    {
        return $this->createLocksForGenericHold($request, 'booking_request_id', 'host_confirmation_pending');
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function createLocksForExtension(BookingExtension $extension): Collection
    {
        $extension->loadMissing('booking.sleepingPlace');
        $booking = $extension->booking;
        $place = $booking?->sleepingPlace;

        if (! $booking instanceof Booking || ! $place instanceof SleepingPlace) {
            throw ValidationException::withMessages([
                'booking_extension_id' => __('availability.messages.missing_sleeping_place'),
            ]);
        }

        $start = $this->date($extension->current_checkout_date ?? $extension->original_check_out ?? $booking->check_out_date);
        $end = $this->date($extension->requested_new_checkout_date ?? $extension->new_check_out);

        return $this->createLockRows($place, $start, $end, [
            'booking_id' => $booking->id,
            'booking_extension_id' => $extension->id,
            'lock_type' => 'extension_pending',
            'expires_at' => $extension->payment_deadline_at,
        ]);
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    public function createLocksForRelocation(Model $relocation): Collection
    {
        return $this->createLocksForGenericHold($relocation, 'booking_relocation_id', 'relocation_pending');
    }

    public function releaseLocksForBooking(Booking $booking, ?string $reason = null): int
    {
        return $this->releaseActiveLocks('booking_id', $booking->id, 'released');
    }

    public function releaseLocksForQuote(Model $quote, ?string $reason = null): int
    {
        return $this->releaseActiveLocks('booking_quote_id', $quote->getKey(), 'released');
    }

    public function releaseLocksForRequest(Model $request, ?string $reason = null): int
    {
        return $this->releaseActiveLocks('booking_request_id', $request->getKey(), 'released');
    }

    public function expireOldLocks(): int
    {
        return SleepingPlaceBookingDateLock::query()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
                'released_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function assertNoOverlap(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): void
    {
        $dates = $this->dateRange($checkIn, $checkOut);

        if ($dates === []) {
            throw ValidationException::withMessages([
                'check_out_date' => __('availability.messages.invalid_date_range'),
            ]);
        }

        $this->expireOldLocks();

        $exists = $place->bookingDateLocks()
            ->whereIn('date', $dates)
            ->where('status', 'active')
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'check_in_date' => __('availability.messages.range_overlaps_existing_booking'),
            ]);
        }
    }

    /**
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    private function createLocksForGenericHold(Model $model, string $foreignKey, string $lockType, CarbonInterface|string|null $expiresAt = null): Collection
    {
        $place = SleepingPlace::query()->find($model->getAttribute('sleeping_place_id'));

        if (! $place instanceof SleepingPlace) {
            throw ValidationException::withMessages([
                'sleeping_place_id' => __('availability.messages.missing_sleeping_place'),
            ]);
        }

        return $this->createLockRows($place, $this->date($model->getAttribute('check_in_date') ?? $model->getAttribute('check_in')), $this->date($model->getAttribute('check_out_date') ?? $model->getAttribute('check_out')), [
            $foreignKey => $model->getKey(),
            'lock_type' => $lockType,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return Collection<int, SleepingPlaceBookingDateLock>
     */
    private function createLockRows(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut, array $attributes): Collection
    {
        $dates = $this->dateRange($checkIn, $checkOut);

        if ($dates === []) {
            throw ValidationException::withMessages([
                'check_out_date' => __('availability.messages.invalid_date_range'),
            ]);
        }

        return DB::transaction(function () use ($place, $dates, $attributes): Collection {
            $locks = collect();

            foreach ($dates as $date) {
                $existing = $place->bookingDateLocks()
                    ->whereDate('date', $date)
                    ->where('status', 'active')
                    ->first();

                if ($existing instanceof SleepingPlaceBookingDateLock) {
                    if (($attributes['booking_id'] ?? null) && (int) $existing->booking_id === (int) $attributes['booking_id']) {
                        $locks->push($existing);

                        continue;
                    }

                    throw ValidationException::withMessages([
                        'check_in_date' => __('availability.messages.range_overlaps_existing_booking'),
                    ]);
                }

                try {
                    $locks->push(SleepingPlaceBookingDateLock::query()->create([
                        'sleeping_place_id' => $place->id,
                        'date' => $date,
                        'status' => 'active',
                        ...$attributes,
                    ]));
                } catch (UniqueConstraintViolationException) {
                    throw ValidationException::withMessages([
                        'check_in_date' => __('availability.messages.range_overlaps_existing_booking'),
                    ]);
                }
            }

            return $locks;
        });
    }

    private function releaseActiveLocks(string $column, int|string|null $id, string $status): int
    {
        if (! $id) {
            return 0;
        }

        return SleepingPlaceBookingDateLock::query()
            ->where($column, $id)
            ->where('status', 'active')
            ->update([
                'status' => $status,
                'released_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function expiresAtForLockType(string $lockType, mixed $fallback): mixed
    {
        if ($lockType === 'payment_pending') {
            return $fallback ?: now()->addMinutes(20);
        }

        return null;
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }

    /**
     * @return list<string>
     */
    private function dateRange(CarbonInterface|string $checkIn, CarbonInterface|string $checkOut): array
    {
        $dates = [];
        $cursor = $this->date($checkIn);
        $end = $this->date($checkOut);

        while ($cursor->lessThan($end)) {
            $dates[] = $cursor->toDateString();
            $cursor = $cursor->addDay();
        }

        return $dates;
    }
}
