<?php

namespace App\Services\Calendar;

use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

class CalendarPriceService
{
    public function __construct(
        private readonly CalendarRuleService $rules,
    ) {}

    public function getPriceForDate(SleepingPlace $place, CarbonInterface|string $date): float
    {
        $day = $this->date($date);
        $calendarDay = $place->calendarDays()->whereDate('date', $day->toDateString())->first();

        if ($calendarDay && $calendarDay->price !== null && $this->isDateSpecificPrice($calendarDay->source)) {
            return (float) $calendarDay->price;
        }

        $rule = collect($this->rules->resolveRulesForDate($place, $day))
            ->first(fn (array $rule): bool => $rule['price'] !== null);

        if ($rule) {
            return (float) $rule['price'];
        }

        $settings = $place->calendarSettings;

        if ($settings && $settings->default_price !== null) {
            return (float) $settings->default_price;
        }

        return (float) $place->base_price_per_night;
    }

    public function getTotalPrice(SleepingPlace $place, array $range): float
    {
        $total = 0.0;
        $nights = 0;

        foreach ($this->period($range) as $date) {
            $total += $this->getPriceForDate($place, $date);
            $nights++;
        }

        if ($nights >= 30) {
            return $this->applyMonthlyDiscount($place, $total, $nights);
        }

        if ($nights >= 7) {
            return $this->applyWeeklyDiscount($place, $total, $nights);
        }

        return round($total, 2);
    }

    public function applyWeeklyDiscount(SleepingPlace $place, float $total, int $nights): float
    {
        $percent = $nights >= 7 ? (float) ($place->calendarSettings?->weekly_discount_percent ?? 0) : 0;

        return round($total * (1 - ($percent / 100)), 2);
    }

    public function applyMonthlyDiscount(SleepingPlace $place, float $total, int $nights): float
    {
        $percent = $nights >= 30 ? (float) ($place->calendarSettings?->monthly_discount_percent ?? 0) : 0;

        return round($total * (1 - ($percent / 100)), 2);
    }

    /**
     * @return list<array{date:string, price:float, reason:string}>
     */
    public function explainPrice(SleepingPlace $place, array $range): array
    {
        $lines = [];

        foreach ($this->period($range) as $date) {
            $calendarDay = $place->calendarDays()->whereDate('date', $date->toDateString())->first();
            $rules = collect($this->rules->resolveRulesForDate($place, $date));
            $reason = 'default_price';

            if ($calendarDay && $calendarDay->price !== null && $this->isDateSpecificPrice($calendarDay->source)) {
                $reason = 'date_price_override';
            } elseif ($rules->contains(fn (array $rule): bool => $rule['price'] !== null)) {
                $reason = 'calendar_rule';
            }

            $lines[] = [
                'date' => $date->toDateString(),
                'price' => $this->getPriceForDate($place, $date),
                'reason' => $reason,
            ];
        }

        return $lines;
    }

    /**
     * @return CarbonPeriod<int, CarbonImmutable>
     */
    private function period(array $range): CarbonPeriod
    {
        return CarbonPeriod::create(
            CarbonImmutable::parse($range['start']),
            CarbonImmutable::parse($range['end'])->subDay(),
        );
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }

    private function isDateSpecificPrice(?string $source): bool
    {
        return in_array($source, ['host_price', 'date_price', 'manual_price'], true);
    }
}
