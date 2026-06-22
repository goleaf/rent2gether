<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Account\PrivacySettingsPage;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Privacy\ListingAddressVisibilityService;
use App\Services\Privacy\PrivacyPreferences;
use App\Services\Privacy\PublicProfileVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrivacySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_phone_is_hidden_before_booking(): void
    {
        [$guest, $host] = $this->privacyUsers();

        $profile = app(PublicProfileVisibility::class)->profileFor($guest, $host);

        $this->assertNull($profile['phone']);
    }

    public function test_guest_phone_is_visible_after_confirmed_booking(): void
    {
        [$guest, $host, $property, $room, $place] = $this->listingContext();

        Booking::factory()->create([
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $profile = app(PublicProfileVisibility::class)->profileFor($guest, $host);

        $this->assertSame('+37060000001', $profile['phone']);
    }

    public function test_exact_address_is_hidden_before_booking(): void
    {
        [$guest, , $property] = $this->listingContext();

        $address = app(ListingAddressVisibilityService::class)->addressFor($property, $guest);

        $this->assertFalse($address['can_see_exact']);
        $this->assertNull($address['exact_address']);
        $this->assertStringContainsString('Old Town', $address['address']);
        $this->assertStringNotContainsString('Central Avenue', $address['address']);
    }

    public function test_exact_address_is_visible_after_confirmation_or_payment(): void
    {
        [$guest, $host, $property, $room, $place] = $this->listingContext();

        Booking::factory()->create([
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $address = app(ListingAddressVisibilityService::class)->addressFor($property, $guest);

        $this->assertTrue($address['can_see_exact']);
        $this->assertStringContainsString('Central Avenue', $address['exact_address']);
        $this->assertStringContainsString('Apartment 7', $address['exact_address']);
    }

    public function test_privacy_page_is_localized(): void
    {
        $user = User::factory()->create();
        $user->setting()->create([
            'locale' => 'ru',
            'currency' => 'EUR',
            'privacy_preferences_json' => PrivacyPreferences::defaults(),
        ]);

        $this->actingAs($user)
            ->get(route('account.privacy', ['locale' => 'ru']))
            ->assertOk()
            ->assertSee('Настройки приватности')
            ->assertSee('Показывать телефон только после подтверждённого бронирования');
    }

    public function test_privacy_settings_can_be_saved(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PrivacySettingsPage::class)
            ->set('showPhoneAfterConfirmedBooking', false)
            ->set('showApproximateAreaBeforeBooking', false)
            ->call('save')
            ->assertHasNoErrors();

        $preferences = PrivacyPreferences::normalize($user->fresh()->setting->privacy_preferences_json);

        $this->assertFalse($preferences['guest']['show_phone_after_confirmed_booking']);
        $this->assertFalse($preferences['host']['show_approximate_area_before_booking']);
    }

    public function test_privacy_preferences_normalize_legacy_json_string_payloads(): void
    {
        $preferences = PrivacyPreferences::normalize(json_encode([
            'show_profile' => false,
            'show_languages' => false,
            'show_reviews' => false,
        ], JSON_THROW_ON_ERROR));

        $this->assertFalse($preferences['show_profile']);
        $this->assertFalse($preferences['guest']['show_languages']);
        $this->assertFalse($preferences['show_languages']);
        $this->assertFalse($preferences['show_reviews']);
    }

    /**
     * @return array{0:User,1:User}
     */
    private function privacyUsers(): array
    {
        $guest = User::factory()->create([
            'name' => 'Guest Full Name',
            'phone' => '+37060000001',
        ]);
        $guest->setting()->create([
            'locale' => 'en',
            'currency' => 'EUR',
            'privacy_preferences_json' => PrivacyPreferences::defaults(),
        ]);

        $host = User::factory()->create([
            'name' => 'Host Full Name',
            'is_host' => true,
            'phone' => '+37060000002',
        ]);
        $host->setting()->create([
            'locale' => 'en',
            'currency' => 'EUR',
            'privacy_preferences_json' => PrivacyPreferences::defaults(),
        ]);

        return [$guest, $host];
    }

    /**
     * @return array{0:User,1:User,2:Property,3:Room,4:SleepingPlace}
     */
    private function listingContext(): array
    {
        [$guest, $host] = $this->privacyUsers();

        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'city' => 'Vilnius',
                'district' => 'Old Town',
                'nearest_transport' => 'Town Hall stop',
                'address_line_1' => 'Central Avenue',
                'house_number' => '15',
                'apartment_number' => '7',
                'show_exact_address_before_booking' => false,
                'show_exact_address_after_payment' => true,
                'access_instructions' => 'Use the small entrance near the courtyard.',
            ]);
        $room = Room::factory()->for($property)->create();
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create();

        return [$guest, $host, $property, $room, $place];
    }
}
