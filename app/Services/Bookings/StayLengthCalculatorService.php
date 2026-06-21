<?php

namespace App\Services\Bookings;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class StayLengthCalculatorService
{
    /**
     * Count billable nightly stays with a half-open date range.
     */
    public function calculateNights(CarbonInterface $checkInDate, CarbonInterface $checkOutDate): int
    {
        return max(0, (int) $this->date($checkInDate)->diffInDays($this->date($checkOutDate)));
    }

    /**
     * Count chargeable stay days for the current nightly rental mode.
     */
    public function calculateChargeableDays(CarbonInterface $checkInDate, CarbonInterface $checkOutDate): int
    {
        return $this->calculateNights($checkInDate, $checkOutDate);
    }

    /**
     * Count inclusive calendar presence days for guest-facing summaries.
     */
    public function calculateCalendarPresenceDays(CarbonInterface $checkInDate, CarbonInterface $checkOutDate): int
    {
        $nights = $this->calculateNights($checkInDate, $checkOutDate);

        return $nights > 0 ? $nights + 1 : 0;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}>
     */
    public function validateBasicDateOrder(array $data): array
    {
        $mode = (string) ($data['rental_mode'] ?? 'nightly');
        $checkIn = $data['check_in_date'] ?? null;
        $checkOut = $data['check_out_date'] ?? null;

        if (! $checkIn || ! $checkOut) {
            return [$this->result('checkout_before_checkin')];
        }

        $checkIn = CarbonImmutable::parse($checkIn)->startOfDay();
        $checkOut = CarbonImmutable::parse($checkOut)->startOfDay();

        if ($checkOut->lessThan($checkIn)) {
            return [$this->result('checkout_before_checkin')];
        }

        if ($mode === 'nightly' && $checkOut->equalTo($checkIn)) {
            return [$this->result('checkout_same_day_not_allowed')];
        }

        return [];
    }

    /**
     * @return array{validation_key:string,severity:string,message_key:string,blocking:bool,visible_to_guest:bool,visible_to_host:bool,message_params_json:array<string, mixed>}
     */
    private function result(string $key): array
    {
        return [
            'validation_key' => $key,
            'severity' => 'blocking',
            'message_key' => 'booking_dates.validation.'.$key,
            'blocking' => true,
            'visible_to_guest' => true,
            'visible_to_host' => false,
            'message_params_json' => [],
        ];
    }

    private function date(CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::instance($date)->startOfDay();
    }
}
