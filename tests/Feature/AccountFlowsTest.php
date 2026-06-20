<?php

namespace Tests\Feature;

use App\Livewire\Account\AccountSettingsPage;
use App\Livewire\Account\ModeSwitcher;
use App\Livewire\Account\ProfileSetupPage;
use App\Livewire\Account\SecuritySettingsPage;
use App\Livewire\Auth\ForgotPasswordPage;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Models\User;
use App\Models\UserSetting;
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

    public function test_profile_setup_updates_profile_and_avatar_variants(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg', 640, 640)->size(256);

        Livewire::actingAs($user)
            ->test(ProfileSetupPage::class)
            ->set('displayName', 'Ada Quiet')
            ->set('phone', '+37060000000')
            ->set('country', 'Lithuania')
            ->set('city', 'Vilnius')
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
        Storage::disk('public')->assertExists($profile->avatar_path);
        Storage::disk('public')->assertExists(str_replace('-medium.jpg', '-thumb.jpg', $profile->avatar_path));
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
