<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Livewire\Bookings\Dates\DateSelectionPanel;
use App\Livewire\Bookings\Quotes\BookingQuoteSummary;
use App\Models\BookingQuote;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\SleepingPlaceCalendarBlock;
use App\Models\User;
use App\Services\Bookings\BookingDateValidationService;
use App\Services\Bookings\BookingPriceQuoteService;
use App\Services\Bookings\BookingQuoteConversionService;
use App\Services\Bookings\StayLengthCalculatorService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BookingDatesAndQuotesFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_stay_length_uses_half_open_nightly_range(): void
    {
        $service = app(StayLengthCalculatorService::class);
        $checkIn = CarbonImmutable::parse('2026-07-10');
        $checkOut = CarbonImmutable::parse('2026-07-13');

        $this->assertSame(3, $service->calculateNights($checkIn, $checkOut));
        $this->assertSame(3, $service->calculateChargeableDays($checkIn, $checkOut));
        $this->assertSame(4, $service->calculateCalendarPresenceDays($checkIn, $checkOut));

        $this->assertSame('checkout_before_checkin', $service->validateBasicDateOrder([
            'check_in_date' => '2026-07-13',
            'check_out_date' => '2026-07-10',
        ])[0]['validation_key']);

        $this->assertSame('checkout_same_day_not_allowed', $service->validateBasicDateOrder([
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-10',
        ])[0]['validation_key']);
    }

    public function test_available_quote_calculates_amount_lines_validation_results_and_timeline_dates(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(price: 20, cleaningFee: 10, deposit: 50, instant: true);

        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);

        $this->assertSame(BookingQuote::STATUS_VALID, $quote->status);
        $this->assertSame('available', $quote->availability_status);
        $this->assertSame(3, $quote->nights_count);
        $this->assertSame(3, $quote->chargeable_days_count);
        $this->assertSame(4, $quote->calendar_presence_days_count);
        $this->assertSame(60.0, (float) $quote->accommodation_amount);
        $this->assertSame(123.0, (float) $quote->total_payable);
        $this->assertSame(50.0, (float) $quote->refundable_amount);
        $this->assertGreaterThanOrEqual(5, $quote->lines()->count());
        $this->assertTrue($quote->timelineDates()->where('event_key', 'payment_deadline')->exists());
        $this->assertNotNull($quote->free_cancellation_until);
        $this->assertNotNull($quote->host_payout_due_at);
    }

    public function test_date_locks_blocks_repairs_complaints_min_max_weekdays_and_guest_count_can_invalidate_quote(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace();

        SleepingPlaceBookingDateLock::factory()->create([
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-11',
            'status' => 'active',
        ]);

        $lockedQuote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);

        $this->assertSame(BookingQuote::STATUS_INVALID, $lockedQuote->status);
        $this->assertTrue($lockedQuote->validationResults()->where('validation_key', 'date_locked_by_another_booking')->exists());
        $this->assertTrue($lockedQuote->suggestions()->exists());

        $repairPlace = $this->sleepingPlace();
        SleepingPlaceCalendarBlock::factory()->repair()->create([
            'sleeping_place_id' => $repairPlace->id,
            'starts_at' => '2026-07-10',
            'ends_at' => '2026-07-13',
        ]);

        $repairQuote = app(BookingPriceQuoteService::class)->createQuote($guest, $repairPlace, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);

        $this->assertTrue($repairQuote->validationResults()->where('validation_key', 'sleeping_place_repair')->exists());

        $complaintPlace = $this->sleepingPlace();
        SleepingPlaceCalendarBlock::factory()->complaint()->create([
            'sleeping_place_id' => $complaintPlace->id,
            'starts_at' => '2026-07-10',
            'ends_at' => '2026-07-13',
        ]);

        $complaintQuote = app(BookingPriceQuoteService::class)->createQuote($guest, $complaintPlace, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);

        $this->assertTrue($complaintQuote->validationResults()->where('validation_key', 'complaint_block')->exists());

        $rulesPlace = $this->sleepingPlace(maxGuests: 1);
        $rulesPlace->calendarSettings()->create([
            'min_nights' => 4,
            'max_nights' => 5,
            'check_in_weekdays_json' => [1],
            'check_out_weekdays_json' => [2],
        ]);

        $results = app(BookingDateValidationService::class)->validateDates($guest, $rulesPlace, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 2,
        ])->pluck('validation_key')->all();

        $this->assertContains('below_min_nights', $results);
        $this->assertContains('check_in_weekday_not_allowed', $results);
        $this->assertContains('check_out_weekday_not_allowed', $results);
        $this->assertContains('guests_count_too_high', $results);
    }

    public function test_expired_quote_cannot_convert_and_valid_quote_converts_with_locks_and_snapshots(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(instant: true);
        $expired = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);
        $expired->update(['expires_at' => now()->subMinute()]);

        $this->expectException(ValidationException::class);
        app(BookingQuoteConversionService::class)->convertToBooking($guest, $expired);
    }

    public function test_valid_quote_conversion_rechecks_availability_and_creates_booking_snapshots(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(instant: true);
        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);

        $booking = app(BookingQuoteConversionService::class)->convertToBooking($guest, $quote);

        $this->assertSame(BookingStatus::AwaitingPayment, $booking->status);
        $this->assertSame($place->id, $booking->sleeping_place_id);
        $this->assertSame(3, $booking->nights_count);
        $this->assertSame(4, $booking->calendar_days_count);
        $this->assertTrue($booking->priceLines()->exists());
        $this->assertTrue($booking->timelineDates()->where('event_key', 'payment_deadline')->exists());
        $this->assertSame(3, $booking->sleepingPlaceDateLocks()->where('status', 'active')->where('lock_type', 'booked')->count());
        $this->assertDatabaseHas('booking_quotes', [
            'id' => $quote->id,
            'status' => BookingQuote::STATUS_CONVERTED_TO_BOOKING,
        ]);
    }

    public function test_quote_livewire_components_render_in_english_and_russian(): void
    {
        $guest = User::factory()->create();
        $place = $this->sleepingPlace(instant: true);
        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);

        app()->setLocale('en');

        Livewire::actingAs($guest)
            ->test(DateSelectionPanel::class, ['sleepingPlace' => $place->id])
            ->set('checkInDate', '2026-07-10')
            ->set('checkOutDate', '2026-07-13')
            ->assertSee(__('booking_dates.title', [], 'en'))
            ->assertSee(__('booking_quotes.price.total_payable', [], 'en'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(BookingQuoteSummary::class, ['quoteId' => $quote->id])
            ->assertSee(__('booking_quotes.title', [], 'ru'))
            ->assertSee(__('booking_quotes.price.total_payable', [], 'ru'));
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
