<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\CancellationPolicy;
use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Enums\DiscountRuleType;
use App\Enums\GenderType;
use App\Enums\MessageThreadType;
use App\Enums\PaymentRecordStatus;
use App\Enums\PaymentStatus;
use App\Enums\PriceRuleType;
use App\Enums\PropertyRentalUnitType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Enums\UserNotificationType;
use App\Models\Amenity;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\BookingPriceLine;
use App\Models\BookingStatusHistory;
use App\Models\City;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\Conversation;
use App\Models\DepositRecord;
use App\Models\DiscountRule;
use App\Models\Favorite;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Notification;
use App\Models\PaymentRecord;
use App\Models\PriceRule;
use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Models\RefundRequest;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomTranslation;
use App\Models\Rule;
use App\Models\SavedSearch;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserSetting;
use App\Models\WaitlistItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class MarketplaceDemoSeeder extends Seeder
{
    private const DEMO_GUEST_EMAILS = [
        'demo.guest.anna@rent2gether.test',
        'demo.guest.maksim@rent2gether.test',
        'demo.guest.sofia@rent2gether.test',
    ];

    private const DEMO_HOST_EMAILS = [
        'demo.host.elena@rent2gether.test',
        'demo.host.martin@rent2gether.test',
        'demo.host.inga@rent2gether.test',
    ];

    private const LEGACY_DEMO_EMAILS = [
        'guest@example.com',
        'host@example.com',
    ];

    private const DEMO_CITY_IDS = [593116, 598316, 598098, 2950159, 2867714];

    public function run(): void
    {
        $this->ensurePrerequisites();
        $this->clearExistingDemoData();

        $cities = $this->demoCities();
        $catalog = $this->catalog();
        $guests = $this->seedGuests($cities);
        $hosts = $this->seedHosts($cities);
        $properties = $this->seedProperties($hosts, $cities, $catalog);
        $rooms = $this->seedRooms($properties, $catalog);
        $places = $this->seedSleepingPlaces($rooms, $catalog);

        $this->seedAvailability($places);
        $this->seedPriceAndDiscountRules($places);

        $bookings = $this->seedBookings($places, $guests);

        $this->seedMessages($bookings);
        $this->seedReviews($bookings);
        $this->seedDecisionTools($guests, $places, $cities);
        $this->seedNotifications($guests, $hosts, $bookings);
        $this->seedComplaints($bookings);
        $this->refreshDemoCounters($properties, $rooms, $hosts, $guests);
    }

    private function ensurePrerequisites(): void
    {
        if (City::query()->whereIn('geoname_id', self::DEMO_CITY_IDS)->count() < count(self::DEMO_CITY_IDS)) {
            $this->call(GeoSeeder::class);
        }

        if (Amenity::query()->count() === 0 || Rule::query()->count() === 0) {
            $this->call(AmenityRuleSeeder::class);
        }
    }

    private function clearExistingDemoData(): void
    {
        $demoEmails = [
            ...self::DEMO_GUEST_EMAILS,
            ...self::DEMO_HOST_EMAILS,
            ...self::LEGACY_DEMO_EMAILS,
        ];
        $demoUserIds = User::query()
            ->whereIn('email', $demoEmails)
            ->pluck('id');

        MediaItem::query()
            ->where('path', 'like', 'demo-media/%')
            ->when($demoUserIds->isNotEmpty(), fn ($query) => $query->orWhereIn('owner_user_id', $demoUserIds))
            ->delete();

        User::query()
            ->whereIn('id', $demoUserIds)
            ->get()
            ->each
            ->delete();
    }

    /**
     * @return Collection<string, City>
     */
    private function demoCities(): Collection
    {
        $cities = City::query()
            ->with(['country', 'region'])
            ->whereIn('geoname_id', self::DEMO_CITY_IDS)
            ->get()
            ->keyBy('name_normalized');

        if ($cities->count() !== count(self::DEMO_CITY_IDS)) {
            throw new RuntimeException(__('app.demo_seed.missing_geo'));
        }

        return $cities;
    }

    /**
     * @return array{amenities:Collection<string,Amenity>,rules:Collection<string,Rule>}
     */
    private function catalog(): array
    {
        return [
            'amenities' => Amenity::query()->get()->keyBy('slug'),
            'rules' => Rule::query()->get()->keyBy('slug'),
        ];
    }

    /**
     * @param  Collection<string, City>  $cities
     * @return Collection<int, User>
     */
    private function seedGuests(Collection $cities): Collection
    {
        $profiles = [
            [
                'email' => self::DEMO_GUEST_EMAILS[0],
                'name' => 'Anna Demo',
                'display' => 'Anna',
                'city' => 'vilnius',
                'locale' => 'en',
                'occupation' => 'Product designer',
                'about' => 'Travels light, works remotely, and prefers quiet shared rooms.',
                'budget_max' => 42,
                'wants_lower_bunk' => true,
            ],
            [
                'email' => self::DEMO_GUEST_EMAILS[1],
                'name' => 'Maksim Demo',
                'display' => 'Maksim',
                'city' => 'kaunas',
                'locale' => 'ru',
                'occupation' => 'Language student',
                'about' => 'Ищет спокойное место на несколько недель рядом с транспортом.',
                'budget_max' => 35,
                'wants_lower_bunk' => false,
            ],
            [
                'email' => self::DEMO_GUEST_EMAILS[2],
                'name' => 'Sofia Demo',
                'display' => 'Sofia',
                'city' => 'berlin',
                'locale' => 'en',
                'occupation' => 'Nurse',
                'about' => 'Needs clean, safe rooms with late check-in and a personal locker.',
                'budget_max' => 55,
                'wants_lower_bunk' => true,
            ],
        ];

        return collect($profiles)->map(function (array $profile) use ($cities): User {
            $city = $cities->get($profile['city']);
            $country = $city->country;

            $user = User::factory()->create([
                'name' => $profile['name'],
                'email' => $profile['email'],
                'country' => $country->name_en,
                'city' => $city->name,
                'languages' => ['en', 'ru'],
                'bio' => $profile['about'],
                'occupation' => $profile['occupation'],
                'prefers_quiet' => true,
                'identity_verified' => true,
                'identity_verified_at' => now(),
                'is_host' => false,
                'rating_as_guest' => 4.80,
                'completed_stays_count' => 2,
            ]);

            UserProfile::factory()
                ->for($user)
                ->for($country)
                ->for($city)
                ->create([
                    'display_name' => $profile['display'],
                    'about' => $profile['about'],
                    'occupation' => $profile['occupation'],
                    'prefers_quiet' => true,
                    'rating_average' => 4.80,
                    'reviews_count' => 2,
                ]);

            GuestPreference::factory()
                ->for($user)
                ->create([
                    'preferred_budget_min' => 18,
                    'preferred_budget_max' => $profile['budget_max'],
                    'preferred_city_id' => $city->id,
                    'wants_lower_bunk' => $profile['wants_lower_bunk'],
                    'needs_late_check_in' => $profile['locale'] === 'en',
                    'needs_workspace' => $profile['occupation'] === 'Product designer',
                    'max_people_in_room' => 6,
                ]);

            UserSetting::factory()
                ->for($user)
                ->create([
                    'locale' => $profile['locale'],
                    'currency' => 'EUR',
                    'active_mode' => UserSetting::MODE_GUEST,
                    'account_role' => UserSetting::ROLE_GUEST,
                ]);

            $this->createMediaPlaceholder($user, $user, 'avatar', 'avatar-'.$user->id, 0, true);

            return $user;
        })->values();
    }

    /**
     * @param  Collection<string, City>  $cities
     * @return Collection<int, User>
     */
    private function seedHosts(Collection $cities): Collection
    {
        $profiles = [
            [
                'email' => self::DEMO_HOST_EMAILS[0],
                'name' => 'Elena Host',
                'display' => 'Elena',
                'city' => 'vilnius',
                'locale' => 'ru',
                'about' => 'Хозяйка небольшой квартиры с понятными правилами и спокойным общением.',
            ],
            [
                'email' => self::DEMO_HOST_EMAILS[1],
                'name' => 'Martin Host',
                'display' => 'Martin',
                'city' => 'berlin',
                'locale' => 'en',
                'about' => 'Hosts clean shared rooms for students and short work stays.',
            ],
            [
                'email' => self::DEMO_HOST_EMAILS[2],
                'name' => 'Inga Host',
                'display' => 'Inga',
                'city' => 'kaunas',
                'locale' => 'en',
                'about' => 'Keeps a warm house with quiet hours, lockers, and flexible arrival.',
            ],
        ];

        return collect($profiles)->map(function (array $profile) use ($cities): User {
            $city = $cities->get($profile['city']);
            $country = $city->country;

            $user = User::factory()->create([
                'name' => $profile['name'],
                'email' => $profile['email'],
                'country' => $country->name_en,
                'city' => $city->name,
                'languages' => ['en', 'ru'],
                'bio' => $profile['about'],
                'is_host' => true,
                'host_description' => $profile['about'],
                'host_experience_years' => 2,
                'host_lives_on_site' => false,
                'identity_verified' => true,
                'identity_verified_at' => now(),
                'rating_as_host' => 4.85,
                'hosted_stays_count' => 8,
            ]);

            UserProfile::factory()
                ->for($user)
                ->for($country)
                ->for($city)
                ->create([
                    'display_name' => $profile['display'],
                    'about' => $profile['about'],
                    'rating_average' => 4.85,
                    'reviews_count' => 4,
                ]);

            HostProfile::factory()
                ->for($user)
                ->create([
                    'display_name' => $profile['display'],
                    'about' => $profile['about'],
                    'response_time_minutes' => 45,
                    'response_rate' => 98,
                    'lives_nearby' => true,
                    'default_house_rules' => 'Quiet hours after 22:00. Keep shared areas clean.',
                    'rating_average' => 4.85,
                    'reviews_count' => 4,
                ]);

            UserSetting::factory()
                ->for($user)
                ->create([
                    'locale' => $profile['locale'],
                    'currency' => 'EUR',
                    'active_mode' => UserSetting::MODE_HOST,
                    'account_role' => UserSetting::ROLE_HOST,
                ]);

            $this->createMediaPlaceholder($user, $user, 'avatar', 'host-avatar-'.$user->id, 0, true);

            return $user;
        })->values();
    }

    /**
     * @param  Collection<int, User>  $hosts
     * @param  Collection<string, City>  $cities
     * @param  array{amenities:Collection<string,Amenity>,rules:Collection<string,Rule>}  $catalog
     * @return Collection<int, Property>
     */
    private function seedProperties(Collection $hosts, Collection $cities, array $catalog): Collection
    {
        $properties = [
            [
                'host' => 0,
                'city' => 'vilnius',
                'type' => PropertyType::Apartment,
                'district' => 'Naujamiestis',
                'title_en' => 'Quiet shared flat near the station',
                'title_ru' => 'Тихая общая квартира рядом с вокзалом',
                'summary_en' => 'A calm apartment with two shared rooms, lockers, and easy transport.',
                'summary_ru' => 'Спокойная квартира с двумя общими комнатами, шкафчиками и удобным транспортом.',
                'description_en' => 'Best for guests who want a simple bed, kitchen access, quiet hours, and a clear check-in.',
                'description_ru' => 'Подходит гостям, которым нужно простое место, кухня, тихие часы и понятное заселение.',
            ],
            [
                'host' => 1,
                'city' => 'berlin',
                'type' => PropertyType::Hostel,
                'district' => 'Moabit',
                'title_en' => 'Small hostel room with work corners',
                'title_ru' => 'Небольшой хостел с рабочими уголками',
                'summary_en' => 'Compact shared rooms with fast Wi-Fi and reliable check-in.',
                'summary_ru' => 'Компактные общие комнаты с быстрым Wi-Fi и надёжным заселением.',
                'description_en' => 'Good for students and remote workers who need clean basics and transit nearby.',
                'description_ru' => 'Подходит студентам и удалённым работникам, которым важны чистота и транспорт рядом.',
            ],
            [
                'host' => 2,
                'city' => 'kaunas',
                'type' => PropertyType::House,
                'district' => 'Zaliakalnis',
                'title_en' => 'Friendly house with garden access',
                'title_ru' => 'Уютный дом с выходом в сад',
                'summary_en' => 'A warm shared house with quiet rooms, storage, and a simple kitchen.',
                'summary_ru' => 'Тёплый общий дом с тихими комнатами, хранением вещей и простой кухней.',
                'description_en' => 'Good for longer stays, careful guests, and people who value calm evenings.',
                'description_ru' => 'Хорошо для долгого проживания, аккуратных гостей и спокойных вечеров.',
            ],
        ];

        return collect($properties)->map(function (array $data, int $index) use ($hosts, $cities, $catalog): Property {
            $host = $hosts[$data['host']];
            $city = $cities->get($data['city']);
            $country = $city->country;
            $region = $city->region;

            $property = Property::factory()
                ->for($host, 'host')
                ->for($country)
                ->for($region)
                ->for($city)
                ->create([
                    'rental_unit_type' => PropertyRentalUnitType::SleepingPlace->value,
                    'title' => $data['title_en'],
                    'type' => $data['type']->value,
                    'description' => $data['description_en'],
                    'country' => $country->name_en,
                    'city' => $city->name,
                    'district' => $data['district'],
                    'address_line_1' => ($index + 12).' Demo Street',
                    'house_number' => (string) ($index + 12),
                    'floor' => $index + 1,
                    'total_floors' => $index + 4,
                    'has_elevator' => $index !== 2,
                    'latitude' => $city->latitude,
                    'longitude' => $city->longitude,
                    'approximate_latitude' => $city->latitude,
                    'approximate_longitude' => $city->longitude,
                    'nearest_transport' => 'Main transit stop',
                    'distance_to_transport_meters' => 350 + ($index * 120),
                    'distance_to_center_meters' => 1200 + ($index * 900),
                    'rooms_count' => 2,
                    'bathrooms_count' => 1,
                    'showers_count' => 1,
                    'kitchens_count' => 1,
                    'balconies_count' => $index === 0 ? 1 : 0,
                    'max_guests' => 6,
                    'noise_level' => $index === 1 ? 'lively' : 'quiet',
                    'cleanliness_level' => 'good',
                    'safety_level' => 'good',
                    'repair_state' => 'good',
                    'has_heating' => true,
                    'has_hot_water' => true,
                    'has_parking' => $index === 2,
                    'has_security' => true,
                    'status' => PropertyStatus::Active->value,
                ]);

            PropertyTranslation::factory()->for($property)->create([
                'locale' => 'en',
                'title' => $data['title_en'],
                'summary' => $data['summary_en'],
                'description' => $data['description_en'],
                'getting_there' => 'Use public transport to the nearest main stop and walk a few minutes.',
                'what_to_know' => 'Rules are visible before booking and quiet hours are taken seriously.',
                'suitable_for' => 'Solo guests, students, remote workers, and careful travelers.',
                'not_suitable_for' => 'Parties or loud overnight calls.',
                'check_in_instructions' => 'Message the host before arrival. Keys are shared after confirmation.',
                'check_out_instructions' => 'Return keys and leave the sleeping place clean.',
                'house_rules_text' => 'Quiet hours after 22:00. Clean shared spaces after use.',
                'safety_notes' => 'Common areas are well lit and emergency contact is available.',
            ]);
            PropertyTranslation::factory()->for($property)->create([
                'locale' => 'ru',
                'title' => $data['title_ru'],
                'summary' => $data['summary_ru'],
                'description' => $data['description_ru'],
                'getting_there' => 'Доедьте до ближайшей остановки и пройдите несколько минут пешком.',
                'what_to_know' => 'Правила видны до бронирования, тихие часы действительно важны.',
                'suitable_for' => 'Одному гостю, студентам, удалённым работникам и аккуратным путешественникам.',
                'not_suitable_for' => 'Вечеринкам и громким ночным звонкам.',
                'check_in_instructions' => 'Напишите хозяину перед приездом. Ключи передаются после подтверждения.',
                'check_out_instructions' => 'Верните ключи и оставьте спальное место чистым.',
                'house_rules_text' => 'Тихие часы после 22:00. Убирайте общие зоны после использования.',
                'safety_notes' => 'Общие зоны освещены, экстренный контакт доступен.',
            ]);

            $this->syncCatalog($property, $catalog, ['wifi', 'kitchen', 'washing_machine', 'hot_water', 'heating'], ['no_smoking', 'quiet_hours_after_22', 'clean_dishes_after_use']);
            $this->createMediaPlaceholder($property, $host, 'property', 'property-'.$property->id, 0, true);

            return $property;
        })->values();
    }

    /**
     * @param  Collection<int, Property>  $properties
     * @param  array{amenities:Collection<string,Amenity>,rules:Collection<string,Rule>}  $catalog
     * @return Collection<int, Room>
     */
    private function seedRooms(Collection $properties, array $catalog): Collection
    {
        return $properties->flatMap(function (Property $property, int $propertyIndex) use ($catalog): array {
            return collect([1, 2])->map(function (int $roomNumber) use ($property, $propertyIndex, $catalog): Room {
                $isQuiet = $roomNumber === 1;
                $room = Room::factory()
                    ->for($property)
                    ->create([
                        'title' => 'Demo room '.$property->id.'-'.$roomNumber,
                        'type' => $isQuiet ? RoomType::Shared->value : RoomType::Dormitory->value,
                        'status' => RoomStatus::Active->value,
                        'room_number' => (string) ($propertyIndex + 1).$roomNumber,
                        'floor' => $property->floor,
                        'beds_count' => 3,
                        'capacity' => 3,
                        'max_guests' => 3,
                        'available_places_count' => 3,
                        'gender_type' => $roomNumber === 2 ? GenderType::NoRestriction->value : GenderType::Mixed->value,
                        'gender_policy' => $roomNumber === 2 ? GenderType::NoRestriction->value : GenderType::Mixed->value,
                        'has_lock' => true,
                        'has_window' => true,
                        'has_wardrobe' => true,
                        'has_desk' => $isQuiet,
                        'has_chair' => true,
                        'has_mirror' => true,
                        'has_heating' => true,
                        'has_air_conditioning' => false,
                        'has_curtains' => true,
                        'has_blackout_curtains' => $isQuiet,
                        'noise_level' => $isQuiet ? 'quiet' : 'moderate',
                        'light_level' => 'good',
                        'ventilation_level' => 'good',
                        'can_work_at_night' => $isQuiet,
                    ]);

                RoomTranslation::factory()->for($room)->create([
                    'locale' => 'en',
                    'title' => $isQuiet ? 'Quiet shared room' : 'Flexible shared room',
                    'summary' => 'Three sleeping places with lockers and simple storage.',
                    'description' => $isQuiet
                        ? 'A calmer room for guests who need predictable evenings.'
                        : 'A practical room for flexible stays and short trips.',
                    'privacy_notes' => 'Guests see only privacy-safe roommate summaries before booking.',
                ]);
                RoomTranslation::factory()->for($room)->create([
                    'locale' => 'ru',
                    'title' => $isQuiet ? 'Тихая общая комната' : 'Гибкая общая комната',
                    'summary' => 'Три спальных места со шкафчиками и простым хранением.',
                    'description' => $isQuiet
                        ? 'Более спокойная комната для предсказуемых вечеров.'
                        : 'Практичная комната для коротких и гибких поездок.',
                    'privacy_notes' => 'До бронирования гости видят только безопасную сводку о соседях.',
                ]);

                $this->syncCatalog($room, $catalog, ['personal_locker', 'desk', 'chair', 'wardrobe'], ['quiet_hours_after_22', 'do_not_use_other_guests_things']);
                $this->createMediaPlaceholder($room, $property->host, 'room', 'room-'.$room->id, 0, true);

                return $room;
            })->all();
        })->values();
    }

    /**
     * @param  Collection<int, Room>  $rooms
     * @param  array{amenities:Collection<string,Amenity>,rules:Collection<string,Rule>}  $catalog
     * @return Collection<int, SleepingPlace>
     */
    private function seedSleepingPlaces(Collection $rooms, array $catalog): Collection
    {
        return $rooms->flatMap(function (Room $room): array {
            return collect([
                [SleepingPlaceType::BunkBottom, 'bottom', 24, true],
                [SleepingPlaceType::BunkTop, 'top', 19, false],
                [SleepingPlaceType::Single, null, 29, true],
            ])->map(function (array $data, int $index) use ($room): SleepingPlace {
                [$type, $bunkLevel, $price, $instant] = $data;
                $placeNumber = $room->room_number.'-'.($index + 1);

                $place = SleepingPlace::factory()
                    ->for($room)
                    ->for($room->property)
                    ->create([
                        'type' => $type->value,
                        'status' => SleepingPlaceStatus::Active->value,
                        'place_number' => $placeNumber,
                        'display_name' => 'Demo place '.$placeNumber,
                        'bunk_level' => $bunkLevel,
                        'base_price_per_night' => $price + (($room->property_id % 3) * 3),
                        'weekly_price' => ($price + (($room->property_id % 3) * 3)) * 6.4,
                        'monthly_price' => ($price + (($room->property_id % 3) * 3)) * 24,
                        'weekend_price' => $price + 5,
                        'cleaning_fee' => 6,
                        'deposit_amount' => $instant ? 25 : 35,
                        'min_nights' => $instant ? 1 : 2,
                        'max_nights' => 30,
                        'instant_booking_enabled' => $instant,
                        'requires_host_approval' => ! $instant,
                        'has_towel' => $index !== 1,
                        'has_curtain' => $type !== SleepingPlaceType::Single,
                        'has_usb' => $index === 2,
                        'near_window' => $index === 2,
                        'privacy_level' => $type === SleepingPlaceType::Single ? 'high' : 'moderate',
                        'noise_level' => $room->noise_level,
                    ]);

                SleepingPlaceTranslation::factory()->for($place)->create([
                    'locale' => 'en',
                    'title' => $this->englishPlaceTitle($type, $placeNumber),
                    'summary' => 'Exact sleeping place with bedding, socket, and personal storage.',
                    'description' => 'This demo place shows how guests book a specific bed, not only a room.',
                    'privacy_notes' => 'The card keeps roommate details private until booking rules allow more.',
                    'accessibility_notes' => $type === SleepingPlaceType::BunkBottom ? 'Lower bunk is easier to access.' : null,
                ]);
                SleepingPlaceTranslation::factory()->for($place)->create([
                    'locale' => 'ru',
                    'title' => $this->russianPlaceTitle($type, $placeNumber),
                    'summary' => 'Точное спальное место с постелью, розеткой и личным хранением.',
                    'description' => 'Это демо показывает бронирование конкретного места, а не только комнаты.',
                    'privacy_notes' => 'Карточка скрывает личные детали соседей до подходящего этапа бронирования.',
                    'accessibility_notes' => $type === SleepingPlaceType::BunkBottom ? 'Нижний ярус проще для доступа.' : null,
                ]);

                return $place;
            })->all();
        })->values()
            ->tap(function (Collection $places) use ($catalog): void {
                $places->each(function (SleepingPlace $place) use ($catalog): void {
                    $amenities = ['bedding', 'pillow', 'blanket', 'personal_lamp', 'power_socket_near_bed', 'personal_locker'];

                    if ($place->has_towel) {
                        $amenities[] = 'towel';
                    }

                    if ($place->has_curtain) {
                        $amenities[] = 'curtain_for_bed';
                    }

                    $this->syncCatalog($place, $catalog, $amenities, ['quiet_hours_after_22', 'return_keys_on_checkout']);
                    $this->createMediaPlaceholder($place, $place->property->host, 'sleeping_place', 'place-'.$place->id, 0, true);
                });
            });
    }

    /**
     * @param  Collection<int, SleepingPlace>  $places
     */
    private function seedAvailability(Collection $places): void
    {
        $today = CarbonImmutable::today();

        $places->each(function (SleepingPlace $place) use ($today): void {
            for ($day = 0; $day < 90; $day++) {
                $date = $today->addDays($day);
                $isCleaning = $day > 0 && $day % 31 === 0;

                AvailabilityDay::factory()->create([
                    'sleeping_place_id' => $place->id,
                    'date' => $date->startOfDay(),
                    'status' => $isCleaning ? AvailabilityStatus::Cleaning->value : AvailabilityStatus::Available->value,
                    'price_override' => $date->isWeekend() ? ((float) $place->base_price_per_night + 4) : null,
                    'check_in_allowed' => ! $isCleaning,
                    'check_out_allowed' => true,
                    'note' => $isCleaning ? 'Demo cleaning day' : null,
                ]);
            }
        });
    }

    /**
     * @param  Collection<int, SleepingPlace>  $places
     */
    private function seedPriceAndDiscountRules(Collection $places): void
    {
        $places->each(function (SleepingPlace $place, int $index): void {
            PriceRule::factory()
                ->for($place)
                ->create([
                    'type' => $index % 3 === 0 ? PriceRuleType::Seasonal->value : PriceRuleType::Weekend->value,
                    'starts_on' => now()->addDays(7)->toDateString(),
                    'ends_on' => now()->addDays(45)->toDateString(),
                    'price' => (float) $place->base_price_per_night + 5,
                    'days_of_week_json' => [5, 6],
                    'priority' => 10,
                ]);

            DiscountRule::factory()
                ->for($place)
                ->create([
                    'type' => $index % 4 === 0 ? DiscountRuleType::Monthly->value : DiscountRuleType::Weekly->value,
                    'min_nights' => $index % 4 === 0 ? 28 : 7,
                    'percent' => $index % 4 === 0 ? 18 : 10,
                ]);
        });
    }

    /**
     * @param  Collection<int, SleepingPlace>  $places
     * @param  Collection<int, User>  $guests
     * @return Collection<int, Booking>
     */
    private function seedBookings(Collection $places, Collection $guests): Collection
    {
        $today = CarbonImmutable::today();
        $scenarios = [
            [BookingStatus::AwaitingHostApproval, PaymentStatus::Unpaid, 5, 3],
            [BookingStatus::AwaitingPayment, PaymentStatus::AwaitingPayment, 10, 4],
            [BookingStatus::Confirmed, PaymentStatus::Paid, 15, 3],
            [BookingStatus::CheckedIn, PaymentStatus::Paid, -1, 4],
            [BookingStatus::InProgress, PaymentStatus::Paid, -3, 5],
            [BookingStatus::CheckedOut, PaymentStatus::Paid, -10, 3],
            [BookingStatus::Completed, PaymentStatus::Paid, -25, 5],
            [BookingStatus::CancelledByGuestFlow, PaymentStatus::RefundedPartial, 20, 3],
            [BookingStatus::DeclinedByHost, PaymentStatus::Unpaid, 30, 3],
            [BookingStatus::ProblemReported, PaymentStatus::Paid, -2, 5],
        ];

        return collect($scenarios)->map(function (array $scenario, int $index) use ($places, $guests, $today): Booking {
            [$status, $paymentStatus, $offset, $nights] = $scenario;
            $place = $places[$index];
            $room = $place->room;
            $property = $place->property;
            $guest = $guests[$index % $guests->count()];
            $host = $property->host;
            $checkIn = $today->addDays($offset);
            $checkOut = $checkIn->addDays($nights);
            $quote = $this->quote($place, $nights);
            $paid = in_array($paymentStatus, [PaymentStatus::Paid, PaymentStatus::RefundedPartial, PaymentStatus::RefundedFull], true);

            $booking = Booking::factory()->create([
                'reference' => sprintf('RTG-DEMO-%03d', $index + 1),
                'bed_id' => null,
                'guest_id' => $guest->id,
                'guest_user_id' => $guest->id,
                'host_id' => $host->id,
                'host_user_id' => $host->id,
                'property_id' => $property->id,
                'room_id' => $room->id,
                'sleeping_place_id' => $place->id,
                'booking_type' => $place->instant_booking_enabled ? BookingType::Instant->value : BookingType::HostApproval->value,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'check_in_time' => '15:00',
                'check_out_time' => '11:00',
                'arrival_time' => '16:30',
                'guests_count' => 1,
                'nights' => $nights,
                'nights_count' => $nights,
                'calendar_days_count' => $nights + 1,
                'price_per_night' => $quote['nightly'],
                'subtotal' => $quote['subtotal'],
                'subtotal_amount' => $quote['subtotal'],
                'discount_amount' => 0,
                'cleaning_fee' => $quote['cleaning'],
                'cleaning_fee_amount' => $quote['cleaning'],
                'deposit' => $quote['deposit'],
                'deposit_amount' => $quote['deposit'],
                'service_fee' => $quote['service'],
                'service_fee_amount' => $quote['service'],
                'total' => $quote['total'],
                'total_amount' => $quote['total'],
                'refundable_amount' => $quote['deposit'],
                'non_refundable_amount' => $quote['service'],
                'currency' => 'EUR',
                'status' => $status->value,
                'payment_status' => $paymentStatus->value,
                'payment_paid_at' => $paid ? now()->subDays(max(0, abs($offset))) : null,
                'payment_deadline_at' => $paymentStatus === PaymentStatus::AwaitingPayment ? now()->addHours(24) : null,
                'availability_hold_expires_at' => $status === BookingStatus::AwaitingHostApproval ? now()->addHours(18) : null,
                'cancellation_policy' => $index % 2 === 0 ? CancellationPolicy::Flexible->value : CancellationPolicy::Moderate->value,
                'guest_message' => 'Demo request: arrival time is flexible and rules are accepted.',
                'host_response' => $status === BookingStatus::DeclinedByHost ? 'The dates no longer work for this room.' : 'Thanks, the details are clear.',
                'rules_accepted_at' => now()->subHours(2),
                'cancelled_by' => $status === BookingStatus::CancelledByGuestFlow ? 'guest' : null,
                'cancelled_at' => $status === BookingStatus::CancelledByGuestFlow ? now()->subDay() : null,
                'cancellation_reason' => $status === BookingStatus::CancelledByGuestFlow ? 'plans_changed' : null,
                'checked_in_at' => in_array($status, [BookingStatus::CheckedIn, BookingStatus::InProgress, BookingStatus::ProblemReported], true) ? now()->subDay() : null,
                'checked_out_at' => in_array($status, [BookingStatus::CheckedOut, BookingStatus::Completed], true) ? now()->subDays(2) : null,
                'has_complaint' => $status === BookingStatus::ProblemReported,
                'guest_review_left' => $status === BookingStatus::Completed,
                'host_review_left' => $status === BookingStatus::Completed,
                'review_deadline_at' => $status === BookingStatus::Completed ? now()->addDays(10) : null,
            ]);

            $this->seedBookingLines($booking, $quote);
            $this->seedBookingMoney($booking, $paymentStatus);
            $this->blockAvailabilityForBooking($booking);

            BookingGuest::factory()->for($booking)->create([
                'user_id' => $guest->id,
                'full_name' => $guest->profile?->display_name ?: $guest->name,
                'age' => 28 + ($index % 12),
            ]);

            BookingStatusHistory::factory()->for($booking)->create([
                'from_status' => BookingStatus::Draft->value,
                'to_status' => $status->value,
                'changed_by_user_id' => $status === BookingStatus::DeclinedByHost ? $host->id : $guest->id,
                'note' => 'Demo status history entry',
            ]);

            return $booking;
        })->values();
    }

    /**
     * @param  array{nightly:float,subtotal:float,cleaning:float,deposit:float,service:float,total:float}  $quote
     */
    private function seedBookingLines(Booking $booking, array $quote): void
    {
        foreach ([
            ['nightly_base', 'booking.price_lines.nightly_base', $quote['subtotal'], false, ['nights' => $booking->nights_count]],
            ['cleaning_fee', 'booking.price_lines.cleaning_fee', $quote['cleaning'], false, []],
            ['deposit', 'booking.price_lines.deposit', $quote['deposit'], true, []],
            ['service_fee', 'booking.price_lines.service_fee', $quote['service'], false, []],
        ] as [$type, $labelKey, $amount, $refundable, $metadata]) {
            BookingPriceLine::factory()->for($booking)->create([
                'type' => $type,
                'label_key' => $labelKey,
                'amount' => $amount,
                'currency' => 'EUR',
                'is_refundable' => $refundable,
                'metadata_json' => $metadata,
            ]);
        }
    }

    private function seedBookingMoney(Booking $booking, PaymentStatus $paymentStatus): void
    {
        if ($paymentStatus !== PaymentStatus::Unpaid) {
            PaymentRecord::factory()->for($booking)->create([
                'payer_user_id' => $booking->guest_user_id,
                'provider' => 'demo_manual',
                'provider_reference' => 'demo-'.$booking->reference,
                'amount' => $booking->total_amount,
                'currency' => $booking->currency,
                'status' => match ($paymentStatus) {
                    PaymentStatus::Paid => PaymentRecordStatus::Paid->value,
                    PaymentStatus::RefundedPartial => PaymentRecordStatus::RefundedPartial->value,
                    PaymentStatus::RefundedFull => PaymentRecordStatus::RefundedFull->value,
                    PaymentStatus::Failed => PaymentRecordStatus::Failed->value,
                    default => PaymentRecordStatus::AwaitingPayment->value,
                },
                'paid_at' => $paymentStatus === PaymentStatus::Paid ? now()->subDays(2) : null,
                'metadata_json' => ['demo' => true],
            ]);
        }

        DepositRecord::factory()->for($booking)->create([
            'amount' => $booking->deposit_amount,
            'currency' => $booking->currency,
            'status' => match ($booking->status) {
                BookingStatus::Completed => 'released',
                BookingStatus::ProblemReported => 'review',
                default => 'held',
            },
            'released_at' => $booking->status === BookingStatus::Completed ? now()->subDay() : null,
            'withheld_amount' => $booking->status === BookingStatus::ProblemReported ? 10 : 0,
        ]);

        if (in_array($booking->status, [BookingStatus::CancelledByGuestFlow, BookingStatus::ProblemReported], true)) {
            RefundRequest::factory()->for($booking)->create([
                'requested_by_user_id' => $booking->guest_user_id,
                'amount' => $booking->status === BookingStatus::CancelledByGuestFlow ? 20 : 15,
                'currency' => $booking->currency,
                'reason' => $booking->status === BookingStatus::CancelledByGuestFlow ? 'plans_changed' : 'place_issue',
            ]);
        }
    }

    private function blockAvailabilityForBooking(Booking $booking): void
    {
        $status = match ($booking->status) {
            BookingStatus::AwaitingHostApproval => AvailabilityStatus::PendingApproval,
            BookingStatus::AwaitingPayment => AvailabilityStatus::PendingPayment,
            BookingStatus::Confirmed,
            BookingStatus::CheckedIn,
            BookingStatus::InProgress,
            BookingStatus::ProblemReported => AvailabilityStatus::Booked,
            default => null,
        };

        if (! $status || ! $booking->sleeping_place_id) {
            return;
        }

        $today = CarbonImmutable::today();
        $lastDemoDay = $today->addDays(89);
        $date = CarbonImmutable::parse($booking->check_in_date)->max($today);
        $end = CarbonImmutable::parse($booking->check_out_date);

        while ($date->lessThan($end) && $date->lessThanOrEqualTo($lastDemoDay)) {
            AvailabilityDay::query()->updateOrCreate(
                ['sleeping_place_id' => $booking->sleeping_place_id, 'date' => $date->startOfDay()],
                [
                    'booking_id' => $booking->id,
                    'status' => $status->value,
                    'check_in_allowed' => false,
                    'check_out_allowed' => true,
                    'note' => 'Demo booking hold',
                ],
            );

            $date = $date->addDay();
        }
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     */
    private function seedMessages(Collection $bookings): void
    {
        $bookings->each(function (Booking $booking, int $index): void {
            $type = $booking->status === BookingStatus::ProblemReported
                ? MessageThreadType::ComplaintRelated
                : MessageThreadType::Booking;

            $thread = MessageThread::factory()->create([
                'type' => $type->value,
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'last_message_at' => now()->subMinutes($index * 7),
            ]);

            $conversation = Conversation::factory()->create([
                'participant_one_id' => $booking->guest_user_id,
                'participant_two_id' => $booking->host_user_id,
                'booking_id' => $booking->id,
                'last_message_at' => $thread->last_message_at,
            ]);

            Message::factory()->create([
                'conversation_id' => $conversation->id,
                'thread_id' => $thread->id,
                'sender_id' => $booking->guest_user_id,
                'sender_user_id' => $booking->guest_user_id,
                'recipient_user_id' => $booking->host_user_id,
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'body' => 'Hello, I will arrive with one small bag and I have read the rules.',
                'locale' => 'en',
                'read_at' => now()->subMinutes(5),
            ]);

            Message::factory()->create([
                'conversation_id' => $conversation->id,
                'thread_id' => $thread->id,
                'sender_id' => $booking->host_user_id,
                'sender_user_id' => $booking->host_user_id,
                'recipient_user_id' => $booking->guest_user_id,
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'body' => 'Thanks, I will keep the check-in details ready after confirmation.',
                'locale' => 'en',
                'read_at' => $index % 3 === 0 ? null : now()->subMinutes(2),
            ]);
        });
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     */
    private function seedReviews(Collection $bookings): void
    {
        $reviewable = $bookings
            ->filter(fn (Booking $booking): bool => in_array($booking->status, [BookingStatus::CheckedOut, BookingStatus::Completed], true))
            ->take(2);

        $reviewable->each(function (Booking $booking): void {
            Review::factory()->for($booking)->create([
                'reviewer_id' => $booking->guest_user_id,
                'reviewee_id' => $booking->host_user_id,
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'type' => ReviewType::GuestToPlace->value,
                'bed_id' => null,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'room_id' => $booking->room_id,
                'property_id' => $booking->property_id,
                'liked_text' => 'The place was clean, quiet, and matched the description.',
                'improvement_text' => 'A second shelf near the bed would help longer stays.',
                'status' => ReviewStatus::Published->value,
            ]);

            Review::factory()->for($booking)->create([
                'reviewer_id' => $booking->host_user_id,
                'reviewee_id' => $booking->guest_user_id,
                'guest_user_id' => $booking->guest_user_id,
                'host_user_id' => $booking->host_user_id,
                'type' => ReviewType::HostToGuest->value,
                'bed_id' => null,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'room_id' => $booking->room_id,
                'property_id' => $booking->property_id,
                'comment' => 'Clear communication and careful use of shared spaces.',
                'recommend_guest' => true,
                'status' => ReviewStatus::Published->value,
            ]);

            $booking->forceFill([
                'guest_review_left' => true,
                'host_review_left' => true,
            ])->save();
        });
    }

    /**
     * @param  Collection<int, User>  $guests
     * @param  Collection<int, SleepingPlace>  $places
     * @param  Collection<string, City>  $cities
     */
    private function seedDecisionTools(Collection $guests, Collection $places, Collection $cities): void
    {
        for ($index = 0; $index < 6; $index++) {
            $guest = $guests[$index % $guests->count()];
            $place = $places[$index + 3];

            Favorite::factory()->create([
                'user_id' => $guest->id,
                'bed_id' => null,
                'sleeping_place_id' => $place->id,
                'note' => 'Demo favorite for comparing exact sleeping places.',
                'priority' => $index,
                'price_at_save' => $place->base_price_per_night,
                'check_in' => now()->addDays(12 + $index)->toDateString(),
                'check_out' => now()->addDays(15 + $index)->toDateString(),
                'notify_available' => true,
                'notify_price_drop' => $index % 2 === 0,
            ]);
        }

        collect(['vilnius', 'berlin', 'munich'])->values()->each(function (string $cityKey, int $index) use ($guests, $cities): void {
            $city = $cities->get($cityKey);

            SavedSearch::factory()->create([
                'user_id' => $guests[$index]->id,
                'city_id' => $city->id,
                'locale' => $index === 1 ? 'ru' : 'en',
                'name' => 'Demo '.$city->name.' beds',
                'city' => $city->name,
                'check_in' => now()->addDays(14)->toDateString(),
                'check_out' => now()->addDays(19)->toDateString(),
                'price_min' => 15,
                'price_max' => 55,
                'filters_json' => ['wifi' => true, 'locker' => true, 'quiet_hours' => true],
            ]);
        });

        for ($index = 0; $index < 3; $index++) {
            $place = $places[$index + 10];

            WaitlistItem::factory()->create([
                'user_id' => $guests[$index]->id,
                'sleeping_place_id' => $place->id,
                'desired_check_in' => now()->addDays(24 + $index)->toDateString(),
                'desired_check_out' => now()->addDays(29 + $index)->toDateString(),
                'max_price' => (float) $place->base_price_per_night + 3,
                'price_at_join' => (float) $place->base_price_per_night + 8,
            ]);
        }
    }

    /**
     * @param  Collection<int, User>  $guests
     * @param  Collection<int, User>  $hosts
     * @param  Collection<int, Booking>  $bookings
     */
    private function seedNotifications(Collection $guests, Collection $hosts, Collection $bookings): void
    {
        $events = [
            [UserNotificationType::BookingRequestSent, $guests[0], $bookings[0]],
            [UserNotificationType::NewBookingRequest, $hosts[0], $bookings[0]],
            [UserNotificationType::PaymentRequired, $guests[1], $bookings[1]],
            [UserNotificationType::BookingConfirmed, $guests[2], $bookings[2]],
            [UserNotificationType::PaymentReceived, $guests[0], $bookings[3]],
            [UserNotificationType::BookingPaymentReceived, $hosts[1], $bookings[3]],
            [UserNotificationType::TomorrowCheckIn, $guests[2], $bookings[2]],
            [UserNotificationType::CheckoutTomorrow, $guests[1], $bookings[4]],
            [UserNotificationType::ReviewReminder, $guests[0], $bookings[6]],
            [UserNotificationType::GuestReportsProblem, $hosts[0], $bookings[9]],
            [UserNotificationType::FavoritePriceDrop, $guests[1], $bookings[2]],
            [UserNotificationType::WaitlistAvailable, $guests[2], $bookings[2]],
        ];

        collect($events)->each(function (array $event, int $index): void {
            [$type, $user, $booking] = $event;
            $key = 'notifications.'.$type->value;
            $place = $booking->sleepingPlace?->translations()->where('locale', 'en')->value('title') ?: 'demo place';

            Notification::factory()->create([
                'type' => $type->value,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'user_id' => $user->id,
                'data' => [
                    'params' => [
                        'reference' => $booking->reference,
                        'place' => $place,
                        'guest' => $booking->guest?->profile?->display_name ?: $booking->guest?->name,
                        'refund' => '20 EUR',
                    ],
                ],
                'title_key' => $key.'.title',
                'body_key' => $key.'.body',
                'action_url' => '/en/bookings/'.$booking->id,
                'read_at' => $index % 4 === 0 ? now()->subHours(3) : null,
                'status' => $index % 4 === 0 ? 'read' : 'unread',
            ]);
        });
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     */
    private function seedComplaints(Collection $bookings): void
    {
        collect([
            [$bookings[9], ComplaintType::PlaceNotAsDescribed, ComplaintStatus::WaitingForOtherSide, true, false],
            [$bookings[4], ComplaintType::DirtyRoom, ComplaintStatus::NeedsMoreInfo, false, false],
            [$bookings[7], ComplaintType::WantsRefund, ComplaintStatus::Created, true, false],
        ])->each(function (array $data, int $index): void {
            [$booking, $type, $status, $refundRequested, $depositHoldRequested] = $data;

            $complaint = Complaint::factory()->create([
                'reference' => sprintf('CMP-DEMO-%03d', $index + 1),
                'complaint_number' => sprintf('CMP-DEMO-%03d', $index + 1),
                'reporter_id' => $booking->guest_user_id,
                'reporter_user_id' => $booking->guest_user_id,
                'reported_user_id' => $booking->host_user_id,
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'bed_id' => null,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'type' => $type->value,
                'status' => $status->value,
                'description' => 'Demo complaint with a short, non-technical description.',
                'desired_resolution' => $refundRequested ? 'refund_review' : 'host_reply',
                'refund_requested' => $refundRequested,
                'deposit_hold_requested' => $depositHoldRequested,
                'media' => ['demo-media/complaints/complaint-'.$index.'.webp'],
                'photos' => ['demo-media/complaints/complaint-'.$index.'.webp'],
            ]);

            ComplaintStatusHistory::factory()->for($complaint)->create([
                'actor_user_id' => $booking->guest_user_id,
                'status' => $status->value,
                'note_key' => 'booking.complaint.timeline.created',
            ]);

            $this->createMediaPlaceholder($complaint, $booking->guest, 'complaint', 'complaint-'.$complaint->id, 0, true);
        });
    }

    /**
     * @param  Collection<int, Property>  $properties
     * @param  Collection<int, Room>  $rooms
     * @param  Collection<int, User>  $hosts
     * @param  Collection<int, User>  $guests
     */
    private function refreshDemoCounters(Collection $properties, Collection $rooms, Collection $hosts, Collection $guests): void
    {
        $rooms->each(function (Room $room): void {
            $activeBookings = $room->sleepingPlaces()
                ->whereHas('bookings', fn ($query) => $query->whereIn('status', [
                    BookingStatus::CheckedIn->value,
                    BookingStatus::InProgress->value,
                    BookingStatus::ProblemReported->value,
                ]))
                ->count();

            $room->forceFill([
                'occupied_places_count' => $activeBookings,
                'available_places_count' => max(0, 3 - $activeBookings),
            ])->save();
        });

        $properties->each(function (Property $property): void {
            $property->forceFill([
                'current_guests_count' => $property->rooms()->sum('occupied_places_count'),
            ])->save();
        });

        $hosts->each(fn (User $host) => $host->hostProfile?->forceFill([
            'rating_average' => 4.85,
            'reviews_count' => 4,
        ])->save());

        $guests->each(fn (User $guest) => $guest->profile?->forceFill([
            'rating_average' => 4.75,
            'reviews_count' => 2,
        ])->save());
    }

    /**
     * @param  array{amenities:Collection<string,Amenity>,rules:Collection<string,Rule>}  $catalog
     * @param  list<string>  $amenitySlugs
     * @param  list<string>  $ruleSlugs
     */
    private function syncCatalog(Model $model, array $catalog, array $amenitySlugs, array $ruleSlugs): void
    {
        if (method_exists($model, 'amenities')) {
            $model->amenities()->syncWithoutDetaching(
                $catalog['amenities']->only($amenitySlugs)->pluck('id')->all(),
            );
        }

        if (method_exists($model, 'rules')) {
            $model->rules()->syncWithoutDetaching(
                $catalog['rules']->only($ruleSlugs)->pluck('id')->all(),
            );
        }
    }

    private function createMediaPlaceholder(Model $model, User $owner, string $collection, string $slug, int $sortOrder, bool $primary): void
    {
        $safeSlug = Str::slug($slug);
        $path = 'demo-media/'.$collection.'/'.$safeSlug.'.webp';

        MediaItem::factory()->create([
            'owner_type' => User::class,
            'owner_id' => $owner->id,
            'mediable_type' => $model::class,
            'mediable_id' => $model->getKey(),
            'owner_user_id' => $owner->id,
            'collection' => $collection,
            'path' => $path,
            'thumbnail_path' => 'demo-media/'.$collection.'/'.$safeSlug.'-thumb.webp',
            'thumb_path' => 'demo-media/'.$collection.'/'.$safeSlug.'-thumb.webp',
            'mobile_path' => 'demo-media/'.$collection.'/'.$safeSlug.'-mobile.webp',
            'full_path' => 'demo-media/'.$collection.'/'.$safeSlug.'-full.webp',
            'original_filename' => $safeSlug.'.webp',
            'mime' => 'image/webp',
            'mime_type' => 'image/webp',
            'size' => 72_000,
            'size_bytes' => 72_000,
            'width' => 1200,
            'height' => 800,
            'caption_en' => 'Demo image placeholder',
            'caption_ru' => 'Демо-заполнитель изображения',
            'sort_order' => $sortOrder,
            'is_primary' => $primary,
            'is_cover' => $primary,
        ]);
    }

    /**
     * @return array{nightly:float,subtotal:float,cleaning:float,deposit:float,service:float,total:float}
     */
    private function quote(SleepingPlace $place, int $nights): array
    {
        $nightly = (float) $place->base_price_per_night;
        $subtotal = round($nightly * $nights, 2);
        $cleaning = (float) $place->cleaning_fee;
        $deposit = (float) $place->deposit_amount;
        $service = round($subtotal * 0.08, 2);

        return [
            'nightly' => $nightly,
            'subtotal' => $subtotal,
            'cleaning' => $cleaning,
            'deposit' => $deposit,
            'service' => $service,
            'total' => round($subtotal + $cleaning + $deposit + $service, 2),
        ];
    }

    private function englishPlaceTitle(SleepingPlaceType $type, string $placeNumber): string
    {
        return match ($type) {
            SleepingPlaceType::BunkBottom => 'Lower bunk '.$placeNumber,
            SleepingPlaceType::BunkTop => 'Upper bunk '.$placeNumber,
            default => 'Single bed '.$placeNumber,
        };
    }

    private function russianPlaceTitle(SleepingPlaceType $type, string $placeNumber): string
    {
        return match ($type) {
            SleepingPlaceType::BunkBottom => 'Нижнее место '.$placeNumber,
            SleepingPlaceType::BunkTop => 'Верхнее место '.$placeNumber,
            default => 'Односпальное место '.$placeNumber,
        };
    }
}
