<?php

namespace App\Services\HostIncome;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundRequestStatus;
use App\Models\Booking;
use App\Models\Property;
use App\Models\RefundRequest;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Localization\LocalizedModelContentResolver;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IncomeSummaryService
{
    /**
     * @return array{
     *     currency:string,
     *     today_income:float,
     *     week_income:float,
     *     month_income:float,
     *     confirmed_income:float,
     *     confirmed_gross:float,
     *     confirmed_count:int,
     *     pending_payments_amount:float,
     *     pending_payments_count:int,
     *     refunds_amount:float,
     *     refunds_count:int,
     *     deposits_held_amount:float,
     *     deposits_returned_amount:float,
     *     by_property:list<array{id:int,label:string,amount:float,count:int,currency:string}>,
     *     by_room:list<array{id:int,label:string,amount:float,count:int,currency:string}>,
     *     by_sleeping_place:list<array{id:int,label:string,amount:float,count:int,currency:string}>,
     *     receipts:list<array<string, mixed>>,
     *     payout_placeholder:array{amount:float,currency:string,label_key:string}
     * }
     */
    public function summarize(User $host, CarbonInterface $start, CarbonInterface $end): array
    {
        $rangeStart = CarbonImmutable::parse($start)->startOfDay();
        $rangeEnd = CarbonImmutable::parse($end)->startOfDay();

        $paidBookings = $this->paidBookings($host, $rangeStart, $rangeEnd)
            ->with([
                'guest:id,name',
                'property:id,title',
                'property.translations:id,property_id,locale,title',
                'room:id,title,room_number',
                'room.translations:id,room_id,locale,title',
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->get();
        $pendingBookings = $this->pendingPaymentBookings($host, $rangeStart, $rangeEnd)->get();
        $refunds = $this->refunds($host, $rangeStart, $rangeEnd)
            ->select(['id', 'booking_id', 'amount', 'currency', 'status'])
            ->get();
        $refundsByBooking = $refunds
            ->groupBy('booking_id')
            ->map(fn (Collection $items): float => $this->money($items->sum(fn (RefundRequest $refund): float => (float) $refund->amount)));

        $currency = $this->currencyFor($host, $paidBookings, $pendingBookings);
        $confirmedGross = $this->collectionTotal($paidBookings);
        $refundsAmount = $this->money($refunds->sum(fn (RefundRequest $refund): float => (float) $refund->amount));
        $confirmedIncome = $this->money(max(0.0, $confirmedGross - $refundsAmount));
        $pendingAmount = $this->collectionTotal($pendingBookings);

        return [
            'currency' => $currency,
            'today_income' => $this->netIncomeFor($host, CarbonImmutable::today(), CarbonImmutable::today()),
            'week_income' => $this->netIncomeFor($host, CarbonImmutable::today()->startOfWeek(), CarbonImmutable::today()->endOfWeek()),
            'month_income' => $this->netIncomeFor($host, CarbonImmutable::today()->startOfMonth(), CarbonImmutable::today()->endOfMonth()),
            'confirmed_income' => $confirmedIncome,
            'confirmed_gross' => $confirmedGross,
            'confirmed_count' => $paidBookings->count(),
            'pending_payments_amount' => $pendingAmount,
            'pending_payments_count' => $pendingBookings->count(),
            'refunds_amount' => $refundsAmount,
            'refunds_count' => $refunds->count(),
            'deposits_held_amount' => $this->depositAmount($host, $rangeStart, $rangeEnd, ['held', 'withheld']),
            'deposits_returned_amount' => $this->depositAmount($host, $rangeStart, $rangeEnd, ['released']),
            'by_property' => $this->groupIncome($paidBookings, $refundsByBooking, 'property'),
            'by_room' => $this->groupIncome($paidBookings, $refundsByBooking, 'room'),
            'by_sleeping_place' => $this->groupIncome($paidBookings, $refundsByBooking, 'sleeping_place'),
            'receipts' => $this->receipts($host, $rangeStart, $rangeEnd, $refundsByBooking),
            'payout_placeholder' => [
                'amount' => $confirmedIncome,
                'currency' => $currency,
                'label_key' => 'host.income.payouts.placeholder_badge',
            ],
        ];
    }

    private function netIncomeFor(User $host, CarbonInterface $start, CarbonInterface $end): float
    {
        $paid = (float) $this->paidBookings($host, $start, $end)->sum('total_amount');
        $refunds = (float) $this->refunds($host, $start, $end)->sum('amount');

        return $this->money(max(0.0, $paid - $refunds));
    }

    private function paidBookings(User $host, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $this->hostBookings($host, $start, $end)
            ->select($this->bookingColumns())
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereIn('status', $this->confirmedIncomeStatuses());
    }

    private function pendingPaymentBookings(User $host, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $this->hostBookings($host, $start, $end)
            ->select($this->bookingColumns())
            ->whereIn('payment_status', [
                PaymentStatus::Unpaid->value,
                PaymentStatus::AwaitingPayment->value,
                PaymentStatus::Pending->value,
                PaymentStatus::PartiallyPaid->value,
            ])
            ->whereNotIn('status', $this->inactiveBookingStatuses());
    }

    private function hostBookings(User $host, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $this->applyHostDateRange(Booking::query(), $host, $start, $end);
    }

    private function applyHostDateRange(Builder $query, User $host, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query
            ->where('host_user_id', $host->id)
            ->whereDate('check_in_date', '>=', CarbonImmutable::parse($start)->toDateString())
            ->whereDate('check_in_date', '<=', CarbonImmutable::parse($end)->toDateString());
    }

    private function refunds(User $host, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return RefundRequest::query()
            ->whereIn('status', [
                RefundRequestStatus::Requested->value,
                RefundRequestStatus::Approved->value,
                RefundRequestStatus::Paid->value,
            ])
            ->whereHas('booking', fn (Builder $booking): Builder => $this->applyHostDateRange($booking, $host, $start, $end));
    }

    /**
     * @param  list<string>  $statuses
     */
    private function depositAmount(User $host, CarbonInterface $start, CarbonInterface $end, array $statuses): float
    {
        return $this->money((float) $this->hostBookings($host, $start, $end)
            ->whereHas('depositRecords', fn (Builder $deposit): Builder => $deposit->whereIn('status', $statuses))
            ->with(['depositRecords:id,booking_id,amount,status'])
            ->get(['id'])
            ->flatMap->depositRecords
            ->filter(fn ($deposit): bool => in_array($deposit->status, $statuses, true))
            ->sum(fn ($deposit): float => (float) $deposit->amount));
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @param  Collection<int, float>  $refundsByBooking
     * @return list<array{id:int,label:string,amount:float,count:int,currency:string}>
     */
    private function groupIncome(Collection $bookings, Collection $refundsByBooking, string $group): array
    {
        return $bookings
            ->groupBy(fn (Booking $booking): int => (int) match ($group) {
                'property' => $booking->property_id,
                'room' => $booking->room_id,
                default => $booking->sleeping_place_id,
            })
            ->filter(fn (Collection $items, int $id): bool => $id > 0 && $items->isNotEmpty())
            ->map(function (Collection $items) use ($group, $refundsByBooking): array {
                /** @var Booking $first */
                $first = $items->first();
                $refunds = $items->sum(fn (Booking $booking): float => (float) ($refundsByBooking[$booking->id] ?? 0));

                return [
                    'id' => (int) match ($group) {
                        'property' => $first->property_id,
                        'room' => $first->room_id,
                        default => $first->sleeping_place_id,
                    },
                    'label' => $this->groupLabel($first, $group),
                    'amount' => $this->money(max(0.0, $this->collectionTotal($items) - $refunds)),
                    'count' => $items->count(),
                    'currency' => $this->bookingCurrency($first),
                ];
            })
            ->sortByDesc('amount')
            ->take(12)
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, float>  $refundsByBooking
     * @return list<array<string, mixed>>
     */
    private function receipts(User $host, CarbonInterface $start, CarbonInterface $end, Collection $refundsByBooking): array
    {
        return $this->hostBookings($host, $start, $end)
            ->select($this->bookingColumns())
            ->whereNotIn('status', $this->inactiveBookingStatuses())
            ->with([
                'guest:id,name',
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->orderByDesc('check_in_date')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->map(function (Booking $booking) use ($refundsByBooking): array {
                $refund = (float) ($refundsByBooking[$booking->id] ?? 0);
                $total = $this->bookingTotal($booking);

                return [
                    'id' => $booking->id,
                    'reference' => $booking->reference,
                    'guest' => $booking->guest?->name ?: __('host.income.guest_fallback'),
                    'place' => $this->placeLabel($booking),
                    'date' => $booking->check_in_date?->translatedFormat('d M Y'),
                    'total' => $total,
                    'refund' => $this->money($refund),
                    'net' => $this->money(max(0.0, $total - $refund)),
                    'currency' => $this->bookingCurrency($booking),
                    'payment_status' => $booking->payment_status?->label() ?? (string) $booking->payment_status,
                ];
            })
            ->all();
    }

    private function groupLabel(Booking $booking, string $group): string
    {
        return match ($group) {
            'property' => $this->propertyLabel($booking->property),
            'room' => $this->roomLabel($booking->room),
            default => $this->placeLabel($booking),
        };
    }

    private function propertyLabel(?Property $property): string
    {
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $property?->translations ?? collect(),
            app()->getLocale(),
            'en',
        );

        return $translation?->title ?: $property?->title ?: __('host.income.property_fallback');
    }

    private function roomLabel(?Room $room): string
    {
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $room?->translations ?? collect(),
            app()->getLocale(),
            'en',
        );

        return $translation?->title ?: $room?->title ?: $room?->room_number ?: __('host.income.room_fallback');
    }

    private function placeLabel(Booking $booking): string
    {
        /** @var SleepingPlace|null $place */
        $place = $booking->sleepingPlace;
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $place?->translations ?? collect(),
            app()->getLocale(),
            'en',
        );

        return $translation?->title ?: $place?->display_name ?: $place?->place_number ?: __('host.income.place_fallback');
    }

    /**
     * @param  Collection<int, Booking>  ...$bookingGroups
     */
    private function currencyFor(User $host, Collection ...$bookingGroups): string
    {
        foreach ($bookingGroups as $bookings) {
            $booking = $bookings->first();

            if ($booking instanceof Booking && $booking->currency) {
                return strtoupper($booking->currency);
            }
        }

        return strtoupper((string) ($host->setting()->value('currency') ?: 'EUR'));
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     */
    private function collectionTotal(Collection $bookings): float
    {
        return $this->money($bookings->sum(fn (Booking $booking): float => $this->bookingTotal($booking)));
    }

    private function bookingTotal(Booking $booking): float
    {
        return $this->money((float) ($booking->total_amount ?: $booking->total ?: 0));
    }

    private function bookingCurrency(Booking $booking): string
    {
        return strtoupper((string) ($booking->currency ?: 'EUR'));
    }

    /**
     * @return list<string>
     */
    private function bookingColumns(): array
    {
        return [
            'id',
            'reference',
            'guest_user_id',
            'host_user_id',
            'property_id',
            'room_id',
            'sleeping_place_id',
            'status',
            'payment_status',
            'check_in_date',
            'check_out_date',
            'total',
            'total_amount',
            'deposit',
            'deposit_amount',
            'currency',
        ];
    }

    /**
     * @return list<string>
     */
    private function confirmedIncomeStatuses(): array
    {
        return [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
            BookingStatus::LeavingSoon->value,
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::AwaitingReview->value,
            BookingStatus::Closed->value,
        ];
    }

    /**
     * @return list<string>
     */
    private function inactiveBookingStatuses(): array
    {
        return [
            BookingStatus::Draft->value,
            BookingStatus::DeclinedByHost->value,
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::Expired->value,
            BookingStatus::CancelledByGuest->value,
            BookingStatus::CancelledByHost->value,
            BookingStatus::CancelledBySystem->value,
            BookingStatus::CancelledByService->value,
            BookingStatus::NoShow->value,
            BookingStatus::HostNoShow->value,
        ];
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
