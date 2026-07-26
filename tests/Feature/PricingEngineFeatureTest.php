<?php

namespace Tests\Feature;

use App\Livewire\Bookings\Pricing\PriceBreakdown;
use App\Livewire\Bookings\Pricing\PriceQuotePanel;
use App\Livewire\Bookings\Pricing\PromoCodeInput;
use App\Models\BookingPriceSnapshot;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceDatePrice;
use App\Models\SleepingPlaceDiscountRule;
use App\Models\SleepingPlacePricingSetting;
use App\Models\User;
use App\Services\Bookings\BookingPriceQuoteService;
use App\Services\Bookings\BookingQuoteConversionService;
use App\Services\Pricing\DatePriceResolverService;
use App\Services\Pricing\PricingSettingsService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PricingEngineFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_precedence_uses_base_weekday_weekend_holiday_and_date_override(): void
    {
        $place = $this->sleepingPlace(price: 20);
        $this->settings($place, [
            'base_nightly_price' => 20,
            'weekday_price' => 18,
            'weekend_price' => 25,
            'holiday_price' => 40,
            'weekend_days_json' => ['friday', 'saturday'],
        ]);

        SleepingPlaceDatePrice::factory()->create([
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-12',
            'price' => 30,
            'price_type' => SleepingPlaceDatePrice::TYPE_MANUAL_OVERRIDE,
        ]);

        $resolver = app(DatePriceResolverService::class);

        $this->assertSame(18.0, $resolver->resolveNightPrice($place, CarbonImmutable::parse('2026-07-09')));
        $this->assertSame(25.0, $resolver->resolveNightPrice($place, CarbonImmutable::parse('2026-07-10')));
        $this->assertSame(40.0, $resolver->resolveNightPrice($place, CarbonImmutable::parse('2027-01-01')));
        $this->assertSame(30.0, $resolver->resolveNightPrice($place, CarbonImmutable::parse('2026-07-12')));
    }

    public function test_quote_recalculation_prefetches_date_overrides_for_the_whole_range(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(price: 20);
        $this->settings($place, ['base_nightly_price' => 20]);

        for ($day = 0; $day < 30; $day++) {
            SleepingPlaceDatePrice::factory()->create([
                'sleeping_place_id' => $place->id,
                'date' => CarbonImmutable::parse('2026-08-01')->addDays($day)->toDateString(),
                'price' => 30 + $day,
                'price_type' => SleepingPlaceDatePrice::TYPE_MANUAL_OVERRIDE,
            ]);
        }

        $datePriceSelects = [];
        $quoteLineAggregateSelects = [];

        DB::listen(function ($query) use (&$datePriceSelects, &$quoteLineAggregateSelects): void {
            $sql = strtolower($query->sql);

            if (str_starts_with($sql, 'select') && str_contains($sql, 'sleeping_place_date_prices')) {
                $datePriceSelects[] = $query->sql;
            }

            if (str_starts_with($sql, 'select') && str_contains($sql, 'booking_quote_lines') && str_contains($sql, 'sum(')) {
                $quoteLineAggregateSelects[] = $query->sql;
            }
        });

        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-08-01',
            'check_out_date' => '2026-08-31',
            'guests_count' => 1,
        ]);

        $this->assertSame(30, $quote->lines()->where('line_type', 'date_override')->count());
        $this->assertLessThanOrEqual(1, count($datePriceSelects));
        $this->assertSame([], $quoteLineAggregateSelects);
    }

    public function test_guest_price_example_shows_daily_lines_discount_fees_deposit_and_total_due_now(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(price: 20);
        $this->settings($place, [
            'base_nightly_price' => 20,
            'cleaning_fee' => 10,
            'deposit_required' => true,
            'deposit_amount' => 50,
            'deposit_payable_now' => true,
            'deposit_refundable' => true,
            'guest_service_fee_type' => SleepingPlacePricingSetting::FEE_FIXED,
            'guest_service_fee_value' => 6,
            'host_service_fee_type' => SleepingPlacePricingSetting::FEE_NONE,
            'host_service_fee_value' => 0,
        ]);
        SleepingPlaceDatePrice::factory()->create([
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-12',
            'price' => 25,
            'price_type' => SleepingPlaceDatePrice::TYPE_MANUAL_OVERRIDE,
        ]);
        SleepingPlaceDiscountRule::factory()->create([
            'sleeping_place_id' => $place->id,
            'discount_type' => SleepingPlaceDiscountRule::TYPE_PERSONAL,
            'name' => 'Example discount',
            'value_type' => SleepingPlaceDiscountRule::VALUE_FIXED_AMOUNT,
            'value' => 5,
            'min_nights' => 1,
            'allow_stacking' => true,
            'priority' => 50,
            'active' => true,
        ]);

        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);

        $dailyLines = $quote->lines()
            ->whereIn('line_type', ['night', 'weekday_night', 'weekend_night', 'holiday_night', 'date_override'])
            ->orderBy('date')
            ->get(['date', 'amount'])
            ->mapWithKeys(fn ($line): array => [$line->date->toDateString() => (float) $line->amount])
            ->all();

        $this->assertSame([
            '2026-07-10' => 20.0,
            '2026-07-11' => 20.0,
            '2026-07-12' => 25.0,
        ], $dailyLines);
        $this->assertSame(3, $quote->chargeable_days_count);
        $this->assertSame(65.0, (float) $quote->accommodation_amount);
        $this->assertSame(5.0, (float) $quote->discount_amount);
        $this->assertSame(10.0, (float) $quote->cleaning_fee_amount);
        $this->assertSame(50.0, (float) $quote->deposit_amount);
        $this->assertSame(6.0, (float) $quote->service_fee_amount);
        $this->assertSame(126.0, (float) $quote->total_payable);
        $this->assertSame(50.0, (float) $quote->refundable_amount);
    }

    public function test_weekly_monthly_and_long_stay_discounts_use_stacking_rules(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(price: 20);
        $this->settings($place, ['base_nightly_price' => 20]);
        SleepingPlaceDiscountRule::factory()->create([
            'sleeping_place_id' => $place->id,
            'discount_type' => SleepingPlaceDiscountRule::TYPE_WEEKLY,
            'value' => 10,
            'min_nights' => 7,
            'allow_stacking' => false,
            'priority' => 10,
        ]);
        SleepingPlaceDiscountRule::factory()->monthly()->create([
            'sleeping_place_id' => $place->id,
            'allow_stacking' => false,
        ]);

        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-08-01',
            'check_out_date' => '2026-09-05',
            'guests_count' => 1,
        ]);

        $this->assertTrue($quote->lines()->where('line_type', 'monthly_discount')->exists());
        $this->assertFalse($quote->lines()->where('line_type', 'weekly_discount')->exists());
        $this->assertSame(175.0, (float) $quote->discount_amount);

        $longStayPlace = $this->sleepingPlace(price: 20);
        $this->settings($longStayPlace, ['base_nightly_price' => 20]);
        SleepingPlaceDiscountRule::factory()->longStay()->create([
            'sleeping_place_id' => $longStayPlace->id,
        ]);

        $longStayQuote = app(BookingPriceQuoteService::class)->createQuote($guest, $longStayPlace, [
            'check_in_date' => '2026-08-01',
            'check_out_date' => '2026-08-15',
            'guests_count' => 1,
        ]);

        $this->assertTrue($longStayQuote->lines()->where('line_type', 'long_stay_discount')->exists());
        $this->assertSame(42.0, (float) $longStayQuote->discount_amount);
    }

    public function test_promo_code_applies_and_invalid_limits_are_rejected(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(price: 20);
        $this->settings($place, ['base_nightly_price' => 20]);

        PromoCode::factory()->create([
            'code' => 'SAVE10',
            'value_type' => PromoCode::VALUE_PERCENT,
            'value' => 10,
            'sleeping_place_id' => $place->id,
        ]);

        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
            'promo_code' => 'SAVE10',
        ]);

        $this->assertSame('valid', $quote->promo_code_status);
        $this->assertTrue($quote->lines()->where('line_type', 'promo_discount')->exists());
        $this->assertSame(6.0, (float) $quote->discount_amount);

        $expired = PromoCode::factory()->expired()->create(['code' => 'OLD10']);

        $expiredQuote = app(BookingPriceQuoteService::class)->recalculateQuote($quote, ['promo_code' => $expired->code]);

        $this->assertSame('expired', $expiredQuote->promo_code_status);
        $this->assertTrue($expiredQuote->validationResults()->where('validation_key', 'promo_code_invalid')->exists());

        $limited = PromoCode::factory()->create(['code' => 'ONCE', 'usage_limit' => 1]);
        PromoCodeRedemption::factory()->create(['promo_code_id' => $limited->id, 'user_id' => $guest->id]);

        $limitedQuote = app(BookingPriceQuoteService::class)->recalculateQuote($quote, ['promo_code' => 'ONCE']);

        $this->assertSame('usage_limit_reached', $limitedQuote->promo_code_status);
    }

    public function test_fees_deposit_service_fee_host_payout_and_time_approval_warnings_are_calculated(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(price: 20, maxGuests: 2);
        $this->settings($place, [
            'base_nightly_price' => 20,
            'extra_guest_allowed' => true,
            'included_guests_count' => 1,
            'max_guests_count' => 2,
            'extra_guest_fee' => 5,
            'early_check_in_mode' => SleepingPlacePricingSetting::TIME_MODE_AUTO_FEE,
            'early_check_in_fee' => 7,
            'late_checkout_mode' => SleepingPlacePricingSetting::TIME_MODE_AUTO_FEE,
            'late_checkout_fee' => 8,
            'cleaning_fee' => 10,
            'deposit_required' => true,
            'deposit_amount' => 50,
            'deposit_payable_now' => true,
            'guest_service_fee_type' => SleepingPlacePricingSetting::FEE_PERCENT,
            'guest_service_fee_value' => 5,
            'host_service_fee_type' => SleepingPlacePricingSetting::FEE_FIXED,
            'host_service_fee_value' => 5,
        ]);

        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 2,
            'early_check_in_requested' => true,
            'late_check_out_requested' => true,
        ]);

        $this->assertSame(15.0, (float) $quote->lines()->where('line_type', 'extra_guest_fee')->value('amount'));
        $this->assertSame(7.0, (float) $quote->lines()->where('line_type', 'early_check_in_fee')->value('amount'));
        $this->assertSame(8.0, (float) $quote->lines()->where('line_type', 'late_checkout_fee')->value('amount'));
        $this->assertSame(4.5, (float) $quote->service_fee_amount);
        $this->assertSame(104.5, (float) $quote->total_without_deposit);
        $this->assertSame(154.5, (float) $quote->total_payable);
        $this->assertSame(50.0, (float) $quote->refundable_amount);
        $this->assertSame(95.0, (float) $quote->host_payout_preview_amount);

        $approvalPlace = $this->sleepingPlace(price: 20);
        $this->settings($approvalPlace, [
            'early_check_in_mode' => SleepingPlacePricingSetting::TIME_MODE_HOST_APPROVAL,
        ]);

        $approvalQuote = app(BookingPriceQuoteService::class)->createQuote($guest, $approvalPlace, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
            'early_check_in_requested' => true,
        ]);

        $this->assertTrue((bool) $approvalQuote->requires_host_time_approval);
        $this->assertTrue($approvalQuote->validationResults()->where('validation_key', 'early_check_in_host_approval_required')->exists());
    }

    public function test_quote_lines_are_rebuilt_when_dates_guest_count_or_promo_change(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(price: 20, maxGuests: 2);
        $this->settings($place, [
            'extra_guest_allowed' => true,
            'included_guests_count' => 1,
            'max_guests_count' => 2,
            'extra_guest_fee' => 5,
        ]);
        PromoCode::factory()->create(['code' => 'CHANGE10', 'value' => 10]);

        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-12',
            'guests_count' => 1,
        ]);

        $this->assertSame(2, $quote->lines()->whereIn('line_type', ['night', 'weekday_night', 'weekend_night', 'holiday_night', 'date_override'])->count());

        $updated = app(BookingPriceQuoteService::class)->recalculateQuote($quote, [
            'check_out_date' => '2026-07-13',
            'guests_count' => 2,
            'promo_code' => 'CHANGE10',
        ]);

        $this->assertSame(3, $updated->lines()->whereIn('line_type', ['night', 'weekday_night', 'weekend_night', 'holiday_night', 'date_override'])->count());
        $this->assertTrue($updated->lines()->where('line_type', 'extra_guest_fee')->exists());
        $this->assertTrue($updated->lines()->where('line_type', 'promo_discount')->exists());
    }

    public function test_price_snapshot_is_created_and_not_changed_by_later_host_price_updates(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(price: 20, instant: true);
        $this->settings($place, ['base_nightly_price' => 20]);
        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);

        $booking = app(BookingQuoteConversionService::class)->convertToBooking($guest, $quote);
        $snapshot = $booking->priceSnapshot;

        $this->assertInstanceOf(BookingPriceSnapshot::class, $snapshot);
        $this->assertSame(123.0, (float) $snapshot->total_payable);

        $this->settings($place, ['base_nightly_price' => 200]);

        $this->assertSame(123.0, (float) $snapshot->refresh()->total_payable);
    }

    public function test_pricing_livewire_components_render_in_english_and_russian(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(price: 20);
        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);

        app()->setLocale('en');

        Livewire::actingAs($guest)
            ->test(PriceQuotePanel::class, ['quoteId' => $quote->id])
            ->assertSee(__('pricing.title', [], 'en'))
            ->assertSee(__('pricing.fields.total_payable', [], 'en'));

        Livewire::actingAs($guest)
            ->test(PriceBreakdown::class, ['quoteId' => $quote->id])
            ->assertSee(__('pricing.sections.breakdown', [], 'en'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(PromoCodeInput::class, ['quoteId' => $quote->id])
            ->assertSee(__('pricing.fields.promo_code', [], 'ru'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function settings(SleepingPlace $place, array $overrides = []): SleepingPlacePricingSetting
    {
        $settings = app(PricingSettingsService::class)->getForSleepingPlace($place);
        $settings->forceFill($overrides)->save();

        return $settings->refresh();
    }

    private function sleepingPlace(
        ?User $host = null,
        int $maxGuests = 1,
        float $price = 20,
        float $cleaningFee = 10,
        float $deposit = 50,
        bool $instant = false,
    ): SleepingPlace {
        $host ??= User::factory()->host()->create();
        $property = Property::factory()->create(['host_user_id' => $host->id]);
        $room = Room::factory()->for($property)->create(['user_id' => $host->id]);

        return SleepingPlace::factory()->for($property)->for($room)->create([
            'user_id' => $host->id,
            'base_price_per_night' => $price,
            'base_price' => $price,
            'weekend_price' => null,
            'cleaning_fee' => $cleaningFee,
            'deposit_amount' => $deposit,
            'max_guests' => $maxGuests,
            'max_guests_count' => $maxGuests,
            'min_guest_age' => null,
            'instant_booking_enabled' => $instant,
            'requires_host_approval' => ! $instant,
        ]);
    }
}
