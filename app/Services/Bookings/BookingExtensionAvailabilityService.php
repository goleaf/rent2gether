<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\SleepingPlaceCalendarBlock;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BookingExtensionAvailabilityService
{
    /**
     * @return array{available:bool,blocking_dates:Collection<int, string>,reasons:Collection<int, string>}
     */
    public function checkAvailabilityAfterCurrentCheckout(Booking $booking, CarbonInterface $newCheckOut, ?BookingExtension $extension = null): array
    {
        $blockingDates = $this->getBlockingDates($booking, $newCheckOut, $extension);
        $reasons = $this->getBlockingReasons($booking, $newCheckOut, $extension);

        return [
            'available' => $blockingDates->isEmpty() && $reasons->filter(fn (string $reason): bool => $reason !== 'host_confirmation_required')->isEmpty(),
            'blocking_dates' => $blockingDates,
            'reasons' => $reasons,
        ];
    }

    /**
     * @return Collection<int, string>
     */
    public function getBlockingDates(Booking $booking, CarbonInterface $newCheckOut, ?BookingExtension $extension = null): Collection
    {
        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace) {
            return collect();
        }

        $start = $this->currentCheckout($booking);
        $end = $this->date($newCheckOut);

        if ($end->lessThanOrEqualTo($start)) {
            return collect();
        }

        $dates = collect();

        Booking::query()
            ->select(['id', 'check_in_date', 'check_out_date'])
            ->where('sleeping_place_id', $place->id)
            ->whereKeyNot($booking->id)
            ->whereIn('status', $this->blockingBookingStatuses())
            ->whereDate('check_in_date', '<', $end->toDateString())
            ->whereDate('check_out_date', '>', $start->toDateString())
            ->get()
            ->each(function (Booking $blockedBooking) use ($dates, $start, $end): void {
                $blockedStart = $this->date($blockedBooking->check_in_date)->max($start);
                $blockedEnd = $this->date($blockedBooking->check_out_date)->min($end);
                $dates->push(...$this->dateRange($blockedStart, $blockedEnd));
            });

        SleepingPlaceBookingDateLock::query()
            ->select(['id', 'date'])
            ->where('sleeping_place_id', $place->id)
            ->where('status', 'active')
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<', $end->toDateString())
            ->where(function ($query) use ($booking): void {
                $query->whereNull('booking_id')
                    ->orWhere('booking_id', '!=', $booking->id);
            })
            ->when($extension instanceof BookingExtension, function ($query) use ($extension): void {
                $query->where(function ($query) use ($extension): void {
                    $query->whereNull('booking_extension_id')
                        ->orWhere('booking_extension_id', '!=', $extension->id);
                });
            })
            ->pluck('date')
            ->map(fn ($date): string => $this->date($date)->toDateString())
            ->each(fn (string $date): Collection => $dates->push($date));

        SleepingPlaceCalendarBlock::query()
            ->select(['id', 'starts_at', 'ends_at'])
            ->where('status', 'active')
            ->where(function ($query) use ($booking, $place): void {
                $query->where('sleeping_place_id', $place->id)
                    ->orWhere('room_id', $booking->room_id)
                    ->orWhere('property_id', $booking->property_id);
            })
            ->whereDate('starts_at', '<', $end->toDateString())
            ->whereDate('ends_at', '>', $start->toDateString())
            ->get()
            ->each(function (SleepingPlaceCalendarBlock $block) use ($dates, $start, $end): void {
                $blockedStart = $this->date($block->starts_at)->max($start);
                $blockedEnd = $this->date($block->ends_at)->min($end);
                $dates->push(...$this->dateRange($blockedStart, $blockedEnd));
            });

        return $dates->unique()->sort()->values();
    }

    public function canExtendTo(Booking $booking, CarbonInterface $newCheckOut): bool
    {
        return $this->checkAvailabilityAfterCurrentCheckout($booking, $newCheckOut)['available'];
    }

    /**
     * @return Collection<int, string>
     */
    public function getAvailableExtensionDates(Booking $booking): Collection
    {
        $start = $this->currentCheckout($booking);

        return collect(range(1, 14))
            ->map(fn (int $days): string => $start->addDays($days)->toDateString())
            ->filter(fn (string $date): bool => $this->canExtendTo($booking, $this->date($date)))
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function getBlockingReasons(Booking $booking, CarbonInterface $newCheckOut, ?BookingExtension $extension = null): Collection
    {
        $place = $booking->sleepingPlace;
        $reasons = collect();

        if (! $place instanceof SleepingPlace) {
            return $reasons->push('sleeping_place_unavailable_after_checkout');
        }

        $start = $this->currentCheckout($booking);
        $end = $this->date($newCheckOut);

        if ($end->lessThanOrEqualTo($start)) {
            return $reasons;
        }

        if ($this->getBlockingDates($booking, $newCheckOut, $extension)->isNotEmpty()) {
            $reasons->push('sleeping_place_unavailable_after_checkout');
        }

        SleepingPlaceBookingDateLock::query()
            ->select(['id', 'lock_type'])
            ->where('sleeping_place_id', $place->id)
            ->where('status', 'active')
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<', $end->toDateString())
            ->where(function ($query) use ($booking): void {
                $query->whereNull('booking_id')
                    ->orWhere('booking_id', '!=', $booking->id);
            })
            ->when($extension instanceof BookingExtension, function ($query) use ($extension): void {
                $query->where(function ($query) use ($extension): void {
                    $query->whereNull('booking_extension_id')
                        ->orWhere('booking_extension_id', '!=', $extension->id);
                });
            })
            ->pluck('lock_type')
            ->each(function (string $lockType) use ($reasons): void {
                $reasons->push(match ($lockType) {
                    'payment_pending' => 'payment_pending_lock_exists',
                    'host_confirmation_pending' => 'host_confirmation_lock_exists',
                    default => 'date_locked_by_another_booking',
                });
            });

        SleepingPlaceCalendarBlock::query()
            ->select(['id', 'block_type', 'sleeping_place_id', 'room_id', 'property_id'])
            ->where('status', 'active')
            ->where(function ($query) use ($booking, $place): void {
                $query->where('sleeping_place_id', $place->id)
                    ->orWhere('room_id', $booking->room_id)
                    ->orWhere('property_id', $booking->property_id);
            })
            ->whereDate('starts_at', '<', $end->toDateString())
            ->whereDate('ends_at', '>', $start->toDateString())
            ->get()
            ->each(function (SleepingPlaceCalendarBlock $block) use ($reasons, $booking, $place): void {
                if ($block->block_type === 'repair' && (int) $block->sleeping_place_id === (int) $place->id) {
                    $reasons->push('sleeping_place_repair');

                    return;
                }

                if ($block->block_type === 'complaint') {
                    $reasons->push('complaint_block');

                    return;
                }

                if ((int) $block->room_id === (int) $booking->room_id) {
                    $reasons->push('room_blocked');

                    return;
                }

                if ((int) $block->property_id === (int) $booking->property_id) {
                    $reasons->push('property_blocked');
                }
            });

        return $reasons->unique()->values();
    }

    /**
     * @return list<string>
     */
    private function blockingBookingStatuses(): array
    {
        return [
            'created',
            'awaiting_payment',
            'waiting_payment',
            'pending_payment',
            'confirmed',
            'paid',
            'ready_for_check_in',
            'guest_checked_in',
            'checked_in',
            'in_progress',
            'stay_in_progress',
            'active_stay',
            'check_out_soon',
        ];
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
