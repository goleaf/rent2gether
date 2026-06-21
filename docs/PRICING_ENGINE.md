# Pricing Engine

The pricing engine calculates transparent quote totals for one `SleepingPlace`.
`Property` and `Room` are context only.

## Calculation Order

1. Validate dates and nights count in booking/date services.
2. Build one quote line for each night in `[check_in_date, check_out_date)`.
3. Resolve nightly price precedence: date override, holiday, weekend, weekday, base.
4. Apply discount rules and promo codes without making accommodation negative.
5. Add early check-in, late checkout, extra guest, cleaning, tax/city future lines.
6. Calculate guest service fee.
7. Add deposit only to `total_payable` when it is payable now.
8. Calculate host payout preview.
9. Split refundable and non-refundable amounts.

## Services

- `BookingPriceQuoteEngine` fully rebuilds quote lines on each recalculation.
- `PricingSettingsService` creates default settings from legacy `SleepingPlace` fields.
- `DatePriceResolverService` applies price precedence for each night.
- `DiscountCalculatorService` applies duration and guest discounts.
- `PromoCodeService` validates code restrictions before discounting.
- `BookingPriceSnapshotService` freezes final booking pricing.

Visible pricing text belongs in `lang/en/pricing.php` and `lang/ru/pricing.php`.
