<?php

namespace Tests\Feature;

use App\Livewire\Account\AccountSettingsPage;
use App\Livewire\Account\ModeSwitcher;
use App\Livewire\Account\ProfileSetupPage;
use App\Livewire\Account\SecuritySettingsPage;
use App\Livewire\Auth\ForgotPasswordPage;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\LogoutButton;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Profile\EditProfile;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\Privacy\PrivacyPreferences;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AccountFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_pages_render_as_livewire_pages(): void
    {
        $this->get('/auth/register')
            ->assertOk()
            ->assertSeeLivewire(RegisterPage::class)
            ->assertSee(__('auth.register.heading'));

        $this->get('/auth/login')
            ->assertOk()
            ->assertSeeLivewire(LoginPage::class)
            ->assertSee(__('auth.login.heading'));

        $this->get('/auth/login?locale=ru')
            ->assertOk()
            ->assertSee('С возвращением')
            ->assertSee(route('auth.login', ['locale' => 'en']), escape: false);

        $this->get('/auth/forgot-password')
            ->assertOk()
            ->assertSeeLivewire(ForgotPasswordPage::class)
            ->assertSee(__('auth.forgot.heading'));
    }

    public function test_user_can_register_as_guest_and_host(): void
    {
        Livewire::test(RegisterPage::class)
            ->set('displayName', 'Ada Host')
            ->set('email', 'ada@example.com')
            ->set('password', 'password123')
            ->set('passwordConfirmation', 'password123')
            ->set('accountRole', 'both')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('profile.setup', ['locale' => 'en']));

        $user = User::query()->where('email', 'ada@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->is_host);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'display_name' => 'Ada Host',
        ]);
        $this->assertDatabaseHas('guest_preferences', ['user_id' => $user->id]);
        $this->assertDatabaseHas('host_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'locale' => 'en',
            'currency' => 'EUR',
            'active_mode' => UserSetting::MODE_HOST,
            'account_role' => UserSetting::ROLE_BOTH,
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'guest@example.com',
            'password' => Hash::make('password123'),
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', 'guest@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('home', ['locale' => 'en']));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_logout_from_livewire_action(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(LogoutButton::class)
            ->call('logout')
            ->assertRedirect(route('home', ['locale' => 'en']));

        $this->assertGuest();
    }

    public function test_profile_setup_updates_profile_and_avatar_variants(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $country = Country::factory()->create(['iso2' => 'LT', 'code' => 'LT', 'name' => 'Lithuania']);
        $city = City::factory()->for($country)->create(['name' => 'Vilnius', 'ascii_name' => 'Vilnius']);
        $avatar = UploadedFile::fake()->image('avatar.jpg', 640, 640)->size(256);

        Livewire::actingAs($user)
            ->test(ProfileSetupPage::class)
            ->set('displayName', 'Ada Quiet')
            ->set('phone', '+37060000000')
            ->set('countryQuery', 'Lithuania')
            ->set('countryId', $country->id)
            ->set('cityQuery', 'Vilnius')
            ->set('cityId', $city->id)
            ->set('languages', 'en, ru')
            ->set('dateOfBirth', '1992-04-12')
            ->set('gender', 'female')
            ->set('about', 'I like calm stays and clear house rules.')
            ->set('occupation', 'Designer')
            ->set('travelPurpose', 'work')
            ->set('smokes', false)
            ->set('hasPets', true)
            ->set('allergies', 'Dust')
            ->set('prefersQuiet', true)
            ->set('sleepSchedule', 'regular')
            ->set('socialLevel', 'balanced')
            ->set('accountRole', 'both')
            ->set('avatar', $avatar)
            ->call('save')
            ->assertHasNoErrors();

        $profile = $user->fresh()->profile;

        $this->assertSame('Ada Quiet', $profile->display_name);
        $this->assertSame($country->id, $profile->country_id);
        $this->assertSame($city->id, $profile->city_id);
        $this->assertSame('I like calm stays and clear house rules.', $profile->about);
        $this->assertSame(['en', 'ru'], $profile->languages_json);
        $this->assertTrue($profile->prefers_quiet);
        $this->assertTrue($user->fresh()->is_host);
        $this->assertDatabaseHas('host_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('guest_preferences', ['user_id' => $user->id]);
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'active_mode' => UserSetting::MODE_HOST,
            'account_role' => UserSetting::ROLE_BOTH,
        ]);
        $this->assertNotNull($profile->avatar_path);
        $this->assertStringEndsWith('-medium.webp', $profile->avatar_path);
        Storage::disk('public')->assertExists($profile->avatar_path);
        Storage::disk('public')->assertExists(str_replace('-medium.webp', '-thumb.webp', $profile->avatar_path));

        Livewire::actingAs($user->fresh())
            ->test(ProfileSetupPage::class)
            ->assertSee(Storage::disk('public')->url($profile->avatar_path), false);
    }

    public function test_account_settings_store_locale_currency_notifications_and_privacy(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AccountSettingsPage::class)
            ->set('locale', 'ru')
            ->set('currency', 'USD')
            ->set('emailMessages', false)
            ->set('emailBookings', true)
            ->set('productUpdates', true)
            ->set('showProfile', true)
            ->set('showLanguages', false)
            ->set('showReviews', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSessionHas('locale', 'ru');

        $settings = $user->fresh()->setting;

        $this->assertSame('ru', $settings->locale);
        $this->assertSame('USD', $settings->currency);
        $this->assertFalse($settings->notification_preferences_json['email_messages']);
        $this->assertTrue($settings->notification_preferences_json['product_updates']);
        $this->assertFalse($settings->privacy_preferences_json['show_languages']);
    }

    public function test_account_settings_repair_legacy_string_encoded_preferences(): void
    {
        $user = User::factory()->create();
        $settings = $user->setting()->create([
            'locale' => 'en',
            'currency' => 'EUR',
        ]);

        $settings->getConnection()->table($settings->getTable())->where('id', $settings->id)->update([
            'notification_preferences_json' => json_encode(json_encode([
                'email_messages' => false,
                'email_bookings' => true,
            ], JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR),
            'privacy_preferences_json' => json_encode(json_encode(PrivacyPreferences::defaults(), JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR),
        ]);

        Livewire::actingAs($user)
            ->test(AccountSettingsPage::class)
            ->assertSet('emailMessages', false)
            ->assertSet('showProfile', true)
            ->set('productUpdates', true)
            ->call('save')
            ->assertHasNoErrors();

        $settings = $user->fresh()->setting;

        $this->assertIsArray($settings->notification_preferences_json);
        $this->assertIsArray($settings->privacy_preferences_json);
        $this->assertTrue($settings->notification_preferences_json['product_updates']);
        $this->assertTrue($settings->privacy_preferences_json['show_profile']);
    }

    public function test_profile_edit_renders_and_saves_host_fields(): void
    {
        $user = User::factory()->create([
            'is_host' => false,
        ]);
        $country = Country::factory()->create(['iso2' => 'LT', 'code' => 'LT', 'name' => 'Lithuania']);
        $city = City::factory()->for($country)->create(['name' => 'Vilnius', 'ascii_name' => 'Vilnius']);

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->assertSee(__('app.profile.is_host'))
            ->assertSee(__('geo.helpers.city_disabled'))
            ->set('name', 'Ada Host')
            ->set('cityQuery', 'Vi')
            ->assertSet('cityQuery', '')
            ->call('selectCountry', $country->id)
            ->assertSet('countryId', $country->id)
            ->call('selectCity', $city->id)
            ->assertSet('cityId', $city->id)
            ->set('isHost', true)
            ->set('hostDescription', 'I host quiet shared stays.')
            ->set('hostExperienceStartedYear', now()->subYears(3)->year)
            ->assertSet('hostExperienceYears', 3)
            ->set('hostLivesOnSite', true)
            ->call('save')
            ->assertHasNoErrors();

        $user = $user->fresh();

        $this->assertTrue($user->is_host);
        $this->assertSame('Lithuania', $user->country);
        $this->assertSame('Vilnius', $user->city);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'country_id' => $country->id,
            'city_id' => $city->id,
            'public_city_name' => 'Vilnius',
        ]);
        $this->assertSame('I host quiet shared stays.', $user->host_description);
        $this->assertSame(now()->subYears(3)->year, $user->host_experience_started_year);
        $this->assertSame(3, $user->host_experience_years);
        $this->assertTrue($user->host_lives_on_site);
    }

    public function test_profile_index_renders_profile_edit_page(): void
    {
        $user = User::factory()->create();

        foreach (['en', 'ru'] as $locale) {
            $this->actingAs($user)
                ->get(route('profile.index', ['locale' => $locale]))
                ->assertOk()
                ->assertSeeLivewire(EditProfile::class);

            $this->actingAs($user)
                ->get(route('profile.edit', ['locale' => $locale]))
                ->assertOk()
                ->assertSeeLivewire(EditProfile::class);
        }
    }

    public function test_security_settings_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        Livewire::actingAs($user)
            ->test(SecuritySettingsPage::class)
            ->set('currentPassword', 'old-password')
            ->set('password', 'new-password')
            ->set('passwordConfirmation', 'new-password')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_avatar_upload_must_be_an_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->create('avatar.pdf', 100, 'application/pdf');

        Livewire::actingAs($user)
            ->test(ProfileSetupPage::class)
            ->set('displayName', 'Ada Guest')
            ->set('avatar', $avatar)
            ->call('save')
            ->assertHasErrors(['avatar' => 'image']);
    }

    public function test_user_can_switch_between_guest_and_host_modes(): void
    {
        $user = User::factory()->create(['is_host' => false]);

        Livewire::actingAs($user)
            ->test(ModeSwitcher::class)
            ->call('switchMode', UserSetting::MODE_HOST)
            ->assertRedirect(route('host.dashboard', ['locale' => 'en']));

        $this->assertTrue($user->fresh()->is_host);
        $this->assertDatabaseHas('host_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'active_mode' => UserSetting::MODE_HOST,
            'account_role' => UserSetting::ROLE_BOTH,
        ]);

        Livewire::actingAs($user->fresh())
            ->test(ModeSwitcher::class)
            ->call('switchMode', UserSetting::MODE_GUEST)
            ->assertRedirect(route('home', ['locale' => 'en']));

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'active_mode' => UserSetting::MODE_GUEST,
            'account_role' => UserSetting::ROLE_BOTH,
        ]);
    }
}
