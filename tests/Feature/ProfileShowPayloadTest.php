<?php

namespace Tests\Feature;

use App\Livewire\Profile\ShowProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileShowPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_keeps_user_model_and_email_out_of_public_state(): void
    {
        $viewer = User::factory()->create();
        $subject = User::factory()->create([
            'name' => 'Payload Host',
            'email' => 'private-host@example.com',
            'is_host' => true,
            'bio' => 'Calm profile text.',
            'rating_as_guest' => 4.6,
            'rating_as_host' => 4.8,
            'completed_stays_count' => 3,
            'hosted_stays_count' => 7,
        ]);

        $component = Livewire::actingAs($viewer)
            ->test(ShowProfile::class, ['user' => $subject])
            ->assertSet('userId', $subject->id)
            ->assertViewHas('user', fn (User $viewUser): bool => $viewUser->is($subject))
            ->assertSee('Payload Host')
            ->assertSee(__('app.profile.host'));

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('userId', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\User', $encodedSnapshot);
        $this->assertStringNotContainsString('private-host@example.com', $encodedSnapshot);
        $this->assertLessThan(11_000, strlen($encodedSnapshot), 'Public profile snapshot should keep full User models out of public state.');
    }
}
