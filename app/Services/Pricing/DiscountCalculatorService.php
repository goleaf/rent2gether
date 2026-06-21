<?php

namespace App\Services\Pricing;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\SleepingPlaceDiscountRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DiscountCalculatorService
{
    /**
     * @return Collection<int, array{type:string,line_type:string,label_key:string,amount:float,priority:int,allow_stacking:bool,rule_id:int|null}>
     */
    public function getApplicableDiscounts(BookingQuote $quote): Collection
    {
        $quote->loadMissing(['sleepingPlace', 'guest']);
        $discounts = collect();

        foreach ($this->activeRules($quote) as $rule) {
            if (! $this->ruleMatchesQuote($rule, $quote)) {
                continue;
            }

            $amount = $this->calculateRuleAmount($quote, $rule);

            if ($amount <= 0) {
                continue;
            }

            $discounts->push([
                'type' => $rule->discount_type,
                'line_type' => $rule->discount_type.'_discount',
                'label_key' => 'pricing.line_types.'.$rule->discount_type.'_discount',
                'amount' => $amount,
                'priority' => (int) $rule->priority,
                'allow_stacking' => (bool) $rule->allow_stacking,
                'rule_id' => $rule->id,
            ]);
        }

        return $this->applyDiscountStackingRules($discounts)
            ->sortByDesc('priority')
            ->values();
    }

    public function calculateWeeklyDiscount(BookingQuote $quote): float
    {
        return $this->discountAmountForType($quote, SleepingPlaceDiscountRule::TYPE_WEEKLY);
    }

    public function calculateMonthlyDiscount(BookingQuote $quote): float
    {
        return $this->discountAmountForType($quote, SleepingPlaceDiscountRule::TYPE_MONTHLY);
    }

    public function calculateLongStayDiscount(BookingQuote $quote): float
    {
        return $this->discountAmountForType($quote, SleepingPlaceDiscountRule::TYPE_LONG_STAY);
    }

    public function calculateEarlyBookingDiscount(BookingQuote $quote): float
    {
        return $this->discountAmountForType($quote, SleepingPlaceDiscountRule::TYPE_EARLY_BOOKING);
    }

    public function calculateLastMinuteDiscount(BookingQuote $quote): float
    {
        return $this->discountAmountForType($quote, SleepingPlaceDiscountRule::TYPE_LAST_MINUTE);
    }

    public function calculateNewGuestDiscount(BookingQuote $quote): float
    {
        return $this->discountAmountForType($quote, SleepingPlaceDiscountRule::TYPE_NEW_GUEST);
    }

    public function calculatePersonalDiscount(BookingQuote $quote): float
    {
        return $this->discountAmountForType($quote, SleepingPlaceDiscountRule::TYPE_PERSONAL);
    }

    /**
     * @param  Collection<int, array{type:string,line_type:string,label_key:string,amount:float,priority:int,allow_stacking:bool,rule_id:int|null}>  $discounts
     * @return Collection<int, array{type:string,line_type:string,label_key:string,amount:float,priority:int,allow_stacking:bool,rule_id:int|null}>
     */
    public function applyDiscountStackingRules(Collection $discounts): Collection
    {
        $monthly = $discounts->firstWhere('type', SleepingPlaceDiscountRule::TYPE_MONTHLY);
        $weekly = $discounts->firstWhere('type', SleepingPlaceDiscountRule::TYPE_WEEKLY);

        if ($monthly !== null && $weekly !== null && ! $monthly['allow_stacking'] && ! $weekly['allow_stacking']) {
            return $discounts
                ->reject(fn (array $discount): bool => $discount['type'] === SleepingPlaceDiscountRule::TYPE_WEEKLY)
                ->values();
        }

        return $discounts->values();
    }

    private function discountAmountForType(BookingQuote $quote, string $type): float
    {
        return $this->getApplicableDiscounts($quote)
            ->where('type', $type)
            ->sum('amount');
    }

    /**
     * @return Collection<int, SleepingPlaceDiscountRule>
     */
    private function activeRules(BookingQuote $quote): Collection
    {
        return $quote->sleepingPlace
            ->pricingDiscountRules()
            ->where('active', true)
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderByDesc('priority')
            ->get();
    }

    private function ruleMatchesQuote(SleepingPlaceDiscountRule $rule, BookingQuote $quote): bool
    {
        $nights = (int) $quote->nights_count;

        if ($rule->min_nights !== null && $nights < (int) $rule->min_nights) {
            return false;
        }

        if ($rule->max_nights !== null && $nights > (int) $rule->max_nights) {
            return false;
        }

        $daysBeforeCheckIn = max(0, CarbonImmutable::now()->startOfDay()->diffInDays(CarbonImmutable::instance($quote->check_in_date), false));

        if ($rule->min_days_before_check_in !== null && $daysBeforeCheckIn < (int) $rule->min_days_before_check_in) {
            return false;
        }

        if ($rule->max_days_before_check_in !== null && $daysBeforeCheckIn > (int) $rule->max_days_before_check_in) {
            return false;
        }

        if ($rule->new_guest_only && ! $this->guestIsNew((int) $quote->user_id)) {
            return false;
        }

        return true;
    }

    private function calculateRuleAmount(BookingQuote $quote, SleepingPlaceDiscountRule $rule): float
    {
        $beforeDiscount = $this->accommodationBeforeDiscount($quote);

        return match ($rule->value_type) {
            SleepingPlaceDiscountRule::VALUE_PERCENT => $this->money($beforeDiscount * ((float) $rule->value / 100)),
            SleepingPlaceDiscountRule::VALUE_FIXED_AMOUNT => $this->money((float) $rule->value),
            SleepingPlaceDiscountRule::VALUE_FIXED_PRICE => $this->money(max(0, $beforeDiscount - (float) $rule->value)),
            default => 0.0,
        };
    }

    private function accommodationBeforeDiscount(BookingQuote $quote): float
    {
        return $this->money((float) $quote->accommodation_amount ?: $quote->lines()
            ->whereIn('line_type', ['night', 'weekday_night', 'weekend_night', 'holiday_night', 'date_override'])
            ->sum('amount'));
    }

    private function guestIsNew(int $guestId): bool
    {
        return ! Booking::query()
            ->where('guest_user_id', $guestId)
            ->where('status', BookingStatus::Completed->value)
            ->exists();
    }

    private function money(mixed $amount): float
    {
        return round(max(0, (float) $amount), 2);
    }
}
