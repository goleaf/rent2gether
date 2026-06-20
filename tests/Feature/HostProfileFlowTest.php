<?php

namespace Tests\Feature;

use App\Livewire\Host\HostOnboardingPage;
use App\Livewire\Host\HostProfileEditPage;
use App\Models\Bed;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HostProfileFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_onboarding_creates_profile_and_enables_host_mode(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_host' => false]);
        $avatar = UploadedFile::fake()->image('host-avatar.jpg', 640, 640)->size(256);

        Livewire::actingAs($user)
            ->test(HostOnboardingPage::class)
            ->set('displayName', 'Ada Host')
            ->set('avatar', $avatar)
            ->set('about', 'I keep arrivals calm and explain every house rule before booking.')
            ->set('languages', 'en, ru')
            ->set('responseStyle', 'quick')
            ->set('livesInProperty', true)
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
            ->assertHasNoErrors()
            ->assertRedirect(route('host.profile.edit', ['locale' => 'en']));

        $profile = $user->fresh()->hostProfile;

        $this->assertTrue($user->fresh()->is_host);
        $this->assertSame('Ada Host', $profile->display_name);
        $this->assertSame(['en', 'ru'], $profile->languages_json);
        $this->assertSame('quick', $profile->response_style);
        $this->assertSame(60, $profile->response_time_minutes);
        $this->assertTrue($profile->lives_in_property);
        $this->assertTrue($profile->can_help_with_check_in);
        $this->assertSame('15:00', (string) $profile->default_check_in_time);
        $this->assertSame('moderate', $profile->default_cancellation_policy);
        $this->assertSame('small', $profile->default_deposit_setting);
        $this->assertNotNull($profile->avatar_path);
        Storage::disk('public')->assertExists($profile->avatar_path);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'active_mode' => UserSetting::MODE_HOST,
            'account_role' => UserSetting::ROLE_BOTH,
        ]);
    }

    public function test_host_profile_edit_updates_profile(): void
    {
        $user = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($user)->create([
            'display_name' => 'Old Host',
            'response_style' => 'friendly',
        ]);

        Livewire::actingAs($user)
            ->test(HostProfileEditPage::class)
            ->set('displayName', 'Updated Host')
            ->set('about', 'I answer clearly and keep check-in simple.')
            ->set('languages', 'en')
            ->set('responseStyle', 'detailed')
            ->set('livesInProperty', false)
            ->set('livesNearby', true)
            ->set('canHelpWithCheckIn', true)
            ->set('emergencyContactAvailable', false)
            ->set('hostingExperience', 'experienced')
            ->set('defaultCheckInTime', '16:00')
            ->set('defaultCheckOutTime', '10:00')
            ->set('defaultCancellationPolicy', 'strict')
            ->set('defaultDepositSetting', 'standard')
            ->set('defaultHouseRules', 'Please keep shared rooms calm after 21:00.')
            ->call('save')
            ->assertHasNoErrors();

        $profile = $user->fresh()->hostProfile;

        $this->assertSame('Updated Host', $profile->display_name);
        $this->assertSame(['en'], $profile->languages_json);
        $this->assertSame('detailed', $profile->response_style);
        $this->assertSame(720, $profile->response_time_minutes);
        $this->assertTrue($profile->lives_nearby);
        $this->assertSame('strict', $profile->default_cancellation_policy);
        $this->assertSame('Please keep shared rooms calm after 21:00.', $profile->default_house_rules);
    }

    public function test_public_host_card_is_localized(): void
    {
        $host = User::factory()->create(['is_host' => true, 'name' => 'Mila Host']);
        HostProfile::factory()->for($host)->create([
            'display_name' => 'Mila Host',
            'languages_json' => ['en', 'ru'],
            'response_time_minutes' => 120,
            'rating_average' => 4.8,
            'reviews_count' => 7,
            'verified_at' => now(),
        ]);
        $property = Property::factory()->for($host, 'host')->create(['host_user_id' => $host->id]);
        $room = Room::factory()->for($property)->create();
        $bed = Bed::factory()->for($room)->create();

        $this->get(route('beds.show', ['locale' => 'en', 'bed' => $bed]))
            ->assertOk()
            ->assertSee('Mila Host')
            ->assertSee('Verified host profile')
            ->assertSee('7 reviews')
            ->assertSee('Usually responds within 2 h');

        $this->get(route('beds.show', ['locale' => 'ru', 'bed' => $bed]))
            ->assertOk()
            ->assertSee('Mila Host')
            ->assertSee('Профиль подтверждён')
            ->assertSee('7 отзывов')
            ->assertSee('Обычно отвечает в течение 2 ч');
    }
}
