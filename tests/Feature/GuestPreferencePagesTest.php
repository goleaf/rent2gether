<?php

namespace Tests\Feature;

use App\Livewire\Account\GuestPreferenceEditPage;
use App\Livewire\Account\GuestPreferenceWizardPage;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuestPreferencePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_preference_wizard_renders_and_saves(): void
    {
        $user = User::factory()->create();
        City::factory()->create([
            'name' => 'Vilnius',
            'ascii_name' => 'Vilnius',
            'name_normalized' => 'vilnius',
        ]);

        Livewire::actingAs($user)
            ->test(GuestPreferenceWizardPage::class)
            ->set('preferredBudgetMin', '20')
            ->set('preferredBudgetMax', '45')
            ->set('preferredCurrency', 'EUR')
            ->set('preferredCity', 'Vilnius')
            ->set('preferredRoomType', 'shared')
            ->set('preferredSleepingPlaceType', 'bunk_bottom')
            ->set('wantsWifi', true)
            ->set('wantsKitchen', true)
            ->set('wantsLocker', true)
            ->set('wantsLowerBunk', true)
            ->set('wantsWorkspace', true)
            ->set('wantsQuietHours', true)
            ->set('avoidsSmoking', true)
            ->set('avoidsPets', true)
            ->set('avoidsMixedRoom', true)
            ->set('needsLateCheckIn', true)
            ->set('needsEarlyCheckOut', true)
            ->set('needsAccessibility', true)
            ->set('maxPeopleInRoom', '4')
            ->set('maxWalkingDistanceToTransportMeters', '700')
            ->set('sleepSchedule', 'early_bird')
            ->set('socialLevel', 'quiet')
            ->set('allergies', 'Cats')
            ->set('baggageSize', 'large')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('profile.preferences.edit', ['locale' => 'en']));

        $this->assertDatabaseHas('guest_preferences', [
            'user_id' => $user->id,
            'preferred_budget_min' => 20,
            'preferred_budget_max' => 45,
            'preferred_room_type' => 'shared',
            'preferred_sleeping_place_type' => 'bunk_bottom',
            'wants_locker' => true,
            'needs_workspace' => true,
            'needs_quiet_hours' => true,
            'needs_early_check_out' => true,
            'needs_accessibility' => true,
            'max_people_in_room' => 4,
            'max_walking_distance_to_transport_meters' => 700,
            'sleep_schedule' => 'early_bird',
            'social_level' => 'quiet',
            'allergies' => 'Cats',
            'baggage_size' => 'large',
        ]);
    }

    public function test_compact_preference_edit_updates_existing_preferences(): void
    {
        $user = User::factory()->create();
        $user->guestPreference()->create(['preferred_currency' => 'EUR']);

        Livewire::actingAs($user)
            ->test(GuestPreferenceEditPage::class)
            ->set('preferredBudgetMax', '60')
            ->set('preferredCurrency', 'USD')
            ->set('wantsWifi', false)
            ->set('wantsWashingMachine', true)
            ->set('maxPeopleInRoom', '3')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('guest_preferences', [
            'user_id' => $user->id,
            'preferred_budget_max' => 60,
            'preferred_currency' => 'USD',
            'wants_wifi' => false,
            'wants_washing_machine' => true,
            'max_people_in_room' => 3,
        ]);
    }
}
