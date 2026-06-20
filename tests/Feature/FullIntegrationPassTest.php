<?php

namespace Tests\Feature;

use App\Actions\Bookings\BookingSubmit;
use App\Actions\Payments\ConfirmDemoPayment;
use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyRentalUnitType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\ReviewType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Account\AccountSettingsPage;
use App\Livewire\Account\ModeSwitcher;
use App\Livewire\Account\ProfileSetupPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Booking\BookingReview;
use App\Livewire\Booking\PaymentPage;
use App\Livewire\Checkin\CheckIn;
use App\Livewire\Checkin\CheckOut;
use App\Livewire\Checkin\ProblemReport;
use App\Livewire\Extensions\ExtendStay;
use App\Livewire\Extensions\ManageExtension;
use App\Livewire\Host\HostIncome;
use App\Livewire\Host\HostOnboardingPage;
use App\Livewire\Host\ManageBooking;
use App\Livewire\Host\PropertyForm;
use App\Livewire\Host\RoomForm;
use App\Livewire\Host\SleepingPlaceForm;
use App\Livewire\Media\ManageMedia;
use App\Livewire\Messages\ChatWindow;
use App\Livewire\Places\ShowSleepingPlace;
use App\Livewire\Reviews\CreateReview;
use App\Livewire\Search\SleepingPlaceSearch;
use App\Livewire\Shell\HostCalendarPage;
use App\Livewire\Shell\HostRequestsPage;
use App\Livewire\Trips\BookingDetail;
use App\Livewire\Trips\TripList;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\City;
use App\Models\MessageThread;
use App\Models\Property;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserSetting;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Composer\InstalledVersions;
use Database\Seeders\AmenityRuleSeeder;
use Database\Seeders\GeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class FullIntegrationPassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-20 10:00:00');
        CarbonImmutable::setTestNow('2026-06-20 10:00:00');
        Storage::fake('public');

        $this->seed([
            GeoSeeder::class,
            AmenityRuleSeeder::class,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_guest_can_complete_the_core_booking_stay_and_review_flow(): void
    {
        $guest = $this->registerUser('guest.full.integration@rent2gether.test', 'guest');

        Livewire::actingAs($guest)
            ->test(AccountSettingsPage::class)
            ->set('locale', 'ru')
            ->set('currency', 'EUR')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSessionHas('locale', 'ru');

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(ProfileSetupPage::class)
            ->set('displayName', 'Интеграционный гость')
            ->set('phone', '+37060000001')
            ->set('country', 'Lithuania')
            ->set('city', 'Vilnius')
            ->set('languages', 'ru, en')
            ->set('dateOfBirth', '1993-03-10')
            ->set('gender', 'female')
            ->set('about', 'Предпочитаю спокойные поездки и понятные правила.')
            ->set('occupation', 'Designer')
            ->set('travelPurpose', 'work')
            ->set('prefersQuiet', true)
            ->set('sleepSchedule', 'regular')
            ->set('socialLevel', 'balanced')
            ->call('save')
            ->assertHasNoErrors();

        [$host, $place, $city] = $this->createPublishedPlace([
            'instant_booking_enabled' => false,
            'requires_host_approval' => true,
        ]);

        $search = Livewire::actingAs($guest)
            ->test(SleepingPlaceSearch::class)
            ->call('selectCity', $city->id)
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-13')
            ->set('guestsCount', 1)
            ->assertHasNoErrors();

        $this->assertSame(3, $search->instance()->nights());
        $searchResults = $search->instance()->searchResults();
        $this->assertSame($place->id, $searchResults['cards'][0]['id']);
        $this->assertSame(3, $searchResults['cards'][0]['nights']);
        $this->assertGreaterThan(0, $searchResults['cards'][0]['total_price']);

        $this->actingAs($guest)
            ->get(route('places.show', ['locale' => 'ru', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSeeLivewire(ShowSleepingPlace::class)
            ->assertSee('Тихое нижнее место');

        Livewire::actingAs($guest)
            ->test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-13')
            ->call('toggleFavorite')
            ->assertSet('isFavorited', true);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
            'check_in' => '2026-07-10 00:00:00',
            'check_out' => '2026-07-13 00:00:00',
        ]);

        Livewire::actingAs($guest)
            ->test(BookingReview::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-13')
            ->set('guestMessage', 'Приеду спокойно, правила прочитаны.')
            ->set('profileReady', true)
            ->set('rulesAccepted', true)
            ->call('submit')
            ->assertHasNoErrors();

        $booking = Booking::query()->where('guest_user_id', $guest->id)->firstOrFail();

        $this->assertTrue($booking->status === BookingStatus::AwaitingHostApproval);
        $this->assertSame(3, $booking->nights_count);
        $this->assertGreaterThan(0, $booking->total_amount);

        Livewire::actingAs($host)
            ->test(HostRequestsPage::class)
            ->call('selectRequest', $booking->id)
            ->set('expiryAt', '2026-06-21T12:00')
            ->set('acceptMessage', 'Payment details are ready.')
            ->call('acceptSelected')
            ->assertHasNoErrors();

        $booking->refresh();
        $this->assertTrue($booking->status === BookingStatus::AwaitingPayment);
        $this->assertTrue($booking->payment_status === PaymentStatus::AwaitingPayment);

        Livewire::actingAs($guest)
            ->test(PaymentPage::class, ['booking' => $booking])
            ->call('markAsPaid')
            ->assertHasNoErrors();

        $booking->refresh();
        $this->assertTrue($booking->status === BookingStatus::Confirmed);
        $this->assertTrue($booking->payment_status === PaymentStatus::Paid);
        $this->assertSame('demo_manual', $booking->payment_method);

        $this->actingAs($guest)
            ->get(route('trips.index', ['locale' => 'ru']))
            ->assertOk()
            ->assertSeeLivewire(TripList::class);

        $this->actingAs($guest)
            ->get(route('guest.bookings.show', ['locale' => 'ru', 'booking' => $booking]))
            ->assertOk()
            ->assertSeeLivewire(BookingDetail::class);

        Livewire::actingAs($guest)
            ->test(CheckIn::class, ['booking' => $booking])
            ->set('propertyFound', true)
            ->set('keysReceived', true)
            ->set('roomSeen', true)
            ->set('sleepingPlaceShown', true)
            ->set('rulesSeen', true)
            ->set('everythingOk', true)
            ->call('submit')
            ->assertHasNoErrors();

        $booking->refresh();
        $this->assertTrue($booking->status === BookingStatus::CheckedIn);

        Livewire::actingAs($guest)
            ->test(ProblemReport::class, ['booking' => $booking])
            ->set('problemDescription', 'Код от двери сначала не подошёл, нужна помощь хозяина.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertTrue((bool) $booking->refresh()->has_complaint);

        Livewire::actingAs($guest)
            ->test(ExtendStay::class, ['booking' => $booking])
            ->set('requestedNewCheckout', '2026-07-15')
            ->set('guestMessage', 'Хочу остаться ещё на две ночи.')
            ->call('submit')
            ->assertHasNoErrors();

        $extension = BookingExtension::query()->where('booking_id', $booking->id)->firstOrFail();

        Livewire::actingAs($host)
            ->test(ManageExtension::class, ['booking' => $booking, 'extension' => $extension])
            ->set('hostResponse', 'That works.')
            ->call('approve')
            ->assertHasNoErrors();

        Livewire::actingAs($guest)
            ->test(ExtendStay::class, ['booking' => $booking->refresh()])
            ->call('payExtension')
            ->assertHasNoErrors();

        $booking->refresh();
        $this->assertSame('2026-07-15', $booking->check_out_date->toDateString());
        $this->assertSame(5, $booking->nights_count);

        Livewire::actingAs($host)
            ->test(ManageBooking::class, ['booking' => $booking])
            ->call('confirmCheckIn')
            ->assertHasNoErrors();

        $booking->refresh();
        $this->assertTrue($booking->status === BookingStatus::InProgress);

        Livewire::actingAs($guest)
            ->test(CheckOut::class, ['booking' => $booking])
            ->set('keysReturned', true)
            ->set('belongingsRemoved', true)
            ->set('lockerEmptied', true)
            ->set('placeClean', true)
            ->call('submit')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(ManageBooking::class, ['booking' => $booking->refresh()])
            ->call('confirmCheckOut')
            ->assertHasNoErrors();

        $booking->refresh();
        $this->assertTrue($booking->status === BookingStatus::Completed);

        Livewire::actingAs($guest)
            ->test(CreateReview::class, ['booking' => $booking])
            ->set('likedText', 'Тихое место, понятные правила и хороший хозяин.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'type' => ReviewType::GuestToPlace->value,
            'guest_user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
        ]);
    }

    public function test_host_can_complete_listing_request_stay_review_and_income_flow(): void
    {
        $host = $this->registerUser('host.full.integration@rent2gether.test', 'guest');

        Livewire::actingAs($host)
            ->test(ModeSwitcher::class)
            ->call('switchMode', UserSetting::MODE_HOST)
            ->assertRedirect(route('host.dashboard', ['locale' => 'en']));

        Livewire::actingAs($host)
            ->test(HostOnboardingPage::class)
            ->set('displayName', 'Integration Host')
            ->set('about', 'I explain check-in clearly and keep quiet hours predictable.')
            ->set('languages', 'en, ru')
            ->set('responseStyle', 'quick')
            ->set('livesNearby', true)
            ->set('canHelpWithCheckIn', true)
            ->set('emergencyContactAvailable', true)
            ->set('hostingExperience', 'some_experience')
            ->set('defaultCheckInTime', '15:00')
            ->set('defaultCheckOutTime', '11:00')
            ->set('defaultCancellationPolicy', 'moderate')
            ->set('defaultDepositSetting', 'small')
            ->set('defaultHouseRules', 'Quiet hours after 22:00.')
            ->call('save')
            ->assertHasNoErrors();

        [$property, $room, $place] = $this->hostCreatesListing($host);

        $this->assertSame(PropertyStatus::Active, $property->refresh()->status);
        $this->assertSame(RoomStatus::Active, $room->refresh()->status);
        $this->assertSame(SleepingPlaceStatus::Active, $place->refresh()->status);

        $this->storeListingPhoto($host, 'property', $property->id, 'gallery', 'property.jpg');
        $this->storeListingPhoto($host, 'room', $room->id, 'gallery', 'room.jpg');
        $this->storeListingPhoto($host, 'sleeping_place', $place->id, 'gallery', 'place.jpg');

        Livewire::actingAs($host)
            ->test(HostCalendarPage::class)
            ->set('selectedSleepingPlaceId', $place->id)
            ->set('rangeStart', '2026-07-10')
            ->set('rangeEnd', '2026-07-12')
            ->set('bulkStatus', AvailabilityStatus::Available->value)
            ->set('priceOverride', 34.50)
            ->call('applyRange')
            ->assertHasNoErrors();

        $guest = $this->guestUserForRequest();

        $booking = app(BookingSubmit::class)->handle($guest, $place, [
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-12',
            'check_in_time' => '15:00',
            'check_out_time' => '11:00',
            'arrival_time' => '16:00',
            'guests_count' => 1,
            'guest_message' => 'I arrive with one backpack and read the rules.',
            'rules_accepted' => true,
            'profile_ready' => true,
        ]);

        Livewire::actingAs($host)
            ->test(HostRequestsPage::class)
            ->call('selectRequest', $booking->id)
            ->set('hostMessage', 'Thanks, I can host these dates.')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->set('acceptMessage', 'Welcome, please complete payment.')
            ->call('acceptSelected')
            ->assertHasNoErrors();

        app(ConfirmDemoPayment::class)->handle($guest, $booking->refresh());

        $booking->refresh();
        $this->assertTrue($booking->status === BookingStatus::Confirmed);

        $thread = MessageThread::query()->where('booking_id', $booking->id)->firstOrFail();

        Livewire::actingAs($host)
            ->test(ChatWindow::class, ['thread' => $thread])
            ->set('body', 'Your booking is confirmed. I will send check-in details before arrival.')
            ->call('send')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('messages', [
            'thread_id' => $thread->id,
            'sender_user_id' => $host->id,
        ]);

        Livewire::actingAs($guest)
            ->test(CheckIn::class, ['booking' => $booking])
            ->set('propertyFound', true)
            ->set('keysReceived', true)
            ->set('roomSeen', true)
            ->set('sleepingPlaceShown', true)
            ->set('rulesSeen', true)
            ->set('everythingOk', true)
            ->call('submit')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(ManageBooking::class, ['booking' => $booking->refresh()])
            ->call('confirmCheckIn')
            ->assertHasNoErrors();

        Livewire::actingAs($guest)
            ->test(CheckOut::class, ['booking' => $booking->refresh()])
            ->set('keysReturned', true)
            ->set('belongingsRemoved', true)
            ->set('lockerEmptied', true)
            ->set('placeClean', true)
            ->call('submit')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(ManageBooking::class, ['booking' => $booking->refresh()])
            ->call('confirmCheckOut')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(CreateReview::class, ['booking' => $booking->refresh(), 'type' => ReviewType::HostToGuest->value])
            ->set('hostComment', 'Respectful guest, easy communication.')
            ->call('submit')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(HostIncome::class)
            ->set('datePreset', 'custom')
            ->set('customStart', '2026-07-01')
            ->set('customEnd', '2026-07-31')
            ->call('applyFilters')
            ->assertHasNoErrors()
            ->assertSet('summary.confirmed_count', 1)
            ->assertSet('summary.confirmed_income', (float) $booking->refresh()->total_amount);

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'type' => ReviewType::HostToGuest->value,
            'host_user_id' => $host->id,
        ]);
    }

    public function test_quality_guardrails_remain_enabled_for_the_integration_surface(): void
    {
        $this->assertFalse(InstalledVersions::isInstalled('livewire/volt'));
        $this->assertFalse(InstalledVersions::isInstalled('filament/filament'));
        $this->assertFalse(InstalledVersions::isInstalled('inertiajs/inertia-laravel'));

        $adminRoutes = collect(Route::getRoutes())->filter(function ($route): bool {
            $name = $route->getName();
            $uri = $route->uri();

            return Str::startsWith($uri, ['admin', 'admin/'])
                || (is_string($name) && Str::startsWith($name, 'admin.'));
        });

        $this->assertCount(0, $adminRoutes);

        foreach (File::allFiles([app_path('Livewire'), resource_path('views')]) as $file) {
            $contents = $file->getContents();

            $this->assertStringNotContainsString('@volt', $contents, $file->getPathname());
            $this->assertStringNotContainsString('Livewire\\Volt', $contents, $file->getPathname());
        }

        $packageJson = File::get(base_path('package.json'));

        foreach (['leaflet', 'mapbox', 'maplibre', 'openlayers', '@googlemaps'] as $mapPackage) {
            $this->assertStringNotContainsString($mapPackage, $packageJson);
        }
    }

    private function registerUser(string $email, string $role): User
    {
        Livewire::test(RegisterPage::class)
            ->set('displayName', Str::headline(Str::before($email, '@')))
            ->set('email', $email)
            ->set('password', 'password123')
            ->set('passwordConfirmation', 'password123')
            ->set('accountRole', $role)
            ->call('register')
            ->assertHasNoErrors();

        return User::query()->where('email', $email)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $placeOverrides
     * @return array{0: User, 1: SleepingPlace, 2: City}
     */
    private function createPublishedPlace(array $placeOverrides = []): array
    {
        $city = City::query()->where('name', 'Vilnius')->firstOrFail();
        $host = User::factory()->create(['is_host' => true, 'name' => 'Integration Host']);
        $host->setting()->create([
            'locale' => 'en',
            'currency' => 'EUR',
            'active_mode' => UserSetting::MODE_HOST,
            'account_role' => UserSetting::ROLE_HOST,
        ]);
        $host->hostProfile()->create([
            'display_name' => 'Integration Host',
            'languages_json' => ['en', 'ru'],
            'default_check_in_time' => '15:00',
            'default_check_out_time' => '11:00',
            'default_cancellation_policy' => 'moderate',
            'status' => 'active',
        ]);

        $property = Property::factory()
            ->for($host, 'host')
            ->for($city->country, 'countryModel')
            ->for($city, 'cityModel')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $city->country_id,
                'region_id' => $city->region_id,
                'city_id' => $city->id,
                'city' => 'Vilnius',
                'district' => 'Old Town',
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
                'show_exact_address_after_payment' => true,
                'address_line_1' => 'Demo Street',
                'house_number' => '12',
                'kitchens_count' => 1,
            ]);
        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet integration apartment',
            'summary' => 'A calm apartment for integration checks.',
            'description' => 'A calm apartment for integration checks.',
            'check_in_instructions' => 'Use the side entrance.',
            'house_rules_text' => 'Quiet hours after 22:00.',
        ]);
        $property->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихая интеграционная квартира',
            'summary' => 'Спокойная квартира для интеграционной проверки.',
            'description' => 'Спокойная квартира для интеграционной проверки.',
            'check_in_instructions' => 'Используйте боковой вход.',
            'house_rules_text' => 'Тихие часы после 22:00.',
        ]);
        $property->amenities()->sync([
            Amenity::query()->where('slug', 'wifi')->value('id'),
            Amenity::query()->where('slug', 'kitchen')->value('id'),
        ]);
        $property->rules()->sync([
            Rule::query()->where('slug', 'quiet_hours_after_22')->value('id'),
            Rule::query()->where('slug', 'no_smoking')->value('id'),
        ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'title' => 'Quiet shared room',
            'room_number' => 'A1',
            'beds_count' => 2,
            'max_guests' => 2,
            'available_places_count' => 1,
        ]);
        $room->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet shared room',
            'summary' => 'Small shared room.',
            'description' => 'Small shared room.',
        ]);
        $room->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихая общая комната',
            'summary' => 'Небольшая общая комната.',
            'description' => 'Небольшая общая комната.',
        ]);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::BunkBottom,
                'display_name' => 'Quiet lower bed',
                'place_number' => 'A1-1',
                'base_price_per_night' => 30,
                'weekly_price' => null,
                'monthly_price' => null,
                'weekend_price' => null,
                'cleaning_fee' => 5,
                'deposit_amount' => 25,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => 20,
                'max_guests' => 1,
                'instant_booking_enabled' => true,
                'requires_host_approval' => false,
                'extensions_allowed' => true,
                ...$placeOverrides,
            ]);
        $place->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet lower bed',
            'summary' => 'Exact lower bunk with locker.',
            'description' => 'Exact lower bunk with locker.',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихое нижнее место',
            'summary' => 'Точный нижний ярус со шкафчиком.',
            'description' => 'Точный нижний ярус со шкафчиком.',
        ]);
        $place->rules()->sync([
            Rule::query()->where('slug', 'quiet_hours_after_22')->value('id'),
        ]);

        return [$host, $place, $city];
    }

    /**
     * @return array{0: Property, 1: Room, 2: SleepingPlace}
     */
    private function hostCreatesListing(User $host): array
    {
        $city = City::query()->where('name', 'Vilnius')->firstOrFail();
        $amenityIds = Amenity::query()->whereIn('slug', ['wifi', 'kitchen'])->pluck('id')->all();
        $ruleIds = Rule::query()->whereIn('slug', ['quiet_hours_after_22', 'no_smoking'])->pluck('id')->all();

        Livewire::actingAs($host)
            ->test(PropertyForm::class)
            ->set('rentalUnitType', PropertyRentalUnitType::SeveralSleepingPlaces->value)
            ->call('nextStep')
            ->set('propertyType', PropertyType::Apartment->value)
            ->call('nextStep')
            ->set('countryQuery', 'Lithuania')
            ->set('countryId', $city->country_id)
            ->set('cityQuery', 'Vilnius')
            ->set('cityId', $city->id)
            ->set('regionName', $city->region?->name ?: 'Vilnius County')
            ->set('district', 'Old Town')
            ->set('street', 'Demo Street')
            ->set('houseNumber', '18')
            ->set('floor', 2)
            ->set('totalFloors', 4)
            ->set('hasElevator', false)
            ->call('nextStep')
            ->set('totalArea', 64)
            ->set('roomsCount', 2)
            ->set('bathroomsCount', 1)
            ->set('showersCount', 1)
            ->set('kitchensCount', 1)
            ->set('balconiesCount', 1)
            ->set('maxGuests', 4)
            ->set('repairState', 'good')
            ->set('noiseLevel', 'low')
            ->set('cleanlinessLevel', 'high')
            ->set('safetyLevel', 'good')
            ->call('nextStep')
            ->set('translations.en.title', 'Integration host apartment')
            ->set('translations.ru.title', 'Квартира интеграционного хозяина')
            ->set('translations.en.summary', 'Small shared stay with clear rules.')
            ->set('translations.ru.summary', 'Небольшое общее жильё с понятными правилами.')
            ->set('translations.en.description', 'Guests book exact sleeping places.')
            ->set('translations.ru.description', 'Гости бронируют точные спальные места.')
            ->call('nextStep')
            ->set('amenityIds', $amenityIds)
            ->call('nextStep')
            ->set('ruleIds', $ruleIds)
            ->call('nextStep')
            ->call('nextStep')
            ->call('publish')
            ->assertHasNoErrors();

        $property = Property::query()->where('host_user_id', $host->id)->firstOrFail();

        Livewire::actingAs($host)
            ->test(RoomForm::class, ['property' => $property])
            ->set('roomNumber', 'A1')
            ->set('title', 'Integration shared room')
            ->set('roomType', RoomType::Shared->value)
            ->set('status', RoomStatus::Active->value)
            ->set('bedsCount', 1)
            ->set('maxGuests', 1)
            ->set('translations.en.description', 'A quiet shared room.')
            ->set('translations.ru.description', 'Тихая общая комната.')
            ->call('publish')
            ->assertHasNoErrors();

        $room = Room::query()->where('property_id', $property->id)->firstOrFail();

        Livewire::actingAs($host)
            ->test(SleepingPlaceForm::class, ['room' => $room])
            ->set('placeNumber', 'A1-1')
            ->set('displayName', 'Integration lower bunk')
            ->set('type', SleepingPlaceType::BunkBottom->value)
            ->set('status', SleepingPlaceStatus::Active->value)
            ->set('hasPillow', true)
            ->set('hasBlanket', true)
            ->set('hasBedding', true)
            ->set('hasPowerSocket', true)
            ->set('hasLocker', true)
            ->set('maxGuests', 1)
            ->set('basePricePerNight', 30)
            ->set('cleaningFee', 5)
            ->set('depositAmount', 25)
            ->set('currency', 'EUR')
            ->set('minNights', 1)
            ->set('maxNights', 20)
            ->set('instantBookingEnabled', false)
            ->set('requiresHostApproval', true)
            ->set('translations.en.title', 'Integration lower bunk')
            ->set('translations.ru.title', 'Интеграционное нижнее место')
            ->set('translations.en.description', 'Exact lower bunk for the integration path.')
            ->set('translations.ru.description', 'Точное нижнее место для интеграционного пути.')
            ->set('ruleIds', $ruleIds)
            ->call('publish')
            ->assertHasNoErrors();

        $place = SleepingPlace::query()->where('room_id', $room->id)->firstOrFail();

        return [$property, $room, $place];
    }

    private function storeListingPhoto(User $host, string $ownerType, int $ownerId, string $collection, string $filename): void
    {
        $component = Livewire::actingAs($host)
            ->test(ManageMedia::class, [
                'ownerType' => $ownerType,
                'ownerId' => $ownerId,
                'collection' => $collection,
            ])
            ->set('photo', UploadedFile::fake()->image($filename, 1000, 700)->size(250));

        foreach (config('localization.supported_locales', []) as $locale) {
            $component->set('captions.'.$locale, 'Demo '.$filename);
        }

        $component
            ->call('savePhoto')
            ->assertHasNoErrors();
    }

    private function guestUserForRequest(): User
    {
        $guest = User::factory()->create(['name' => 'Integration Guest']);
        $guest->profile()->create([
            'display_name' => 'Integration Guest',
            'about' => 'Calm guest for host integration.',
            'languages_json' => ['en'],
            'status' => 'active',
        ]);
        $guest->guestPreference()->create([
            'needs_quiet_hours' => true,
            'wants_locker' => true,
        ]);
        $guest->setting()->create([
            'locale' => 'en',
            'currency' => 'EUR',
            'active_mode' => UserSetting::MODE_GUEST,
            'account_role' => UserSetting::ROLE_GUEST,
        ]);

        return $guest;
    }
}
