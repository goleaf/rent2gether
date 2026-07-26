<?php

namespace Tests\Feature;

use App\Livewire\Waitlist\WaitlistManager;
use App\Models\Bed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LegacyWaitlistManagerPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_waitlist_manager_keeps_bed_model_out_of_public_state(): void
    {
        $guest = User::factory()->create();
        $bed = Bed::factory()->create([
            'title' => 'Legacy waitlist bed',
            'price_per_night' => 24,
        ]);

        $component = Livewire::actingAs($guest)
            ->test(WaitlistManager::class, ['bed' => $bed])
            ->assertSet('bedId', $bed->id)
            ->assertSet('maxPrice', 24.0)
            ->set('showForm', true)
            ->set('desiredCheckIn', now()->addDays(5)->toDateString())
            ->set('desiredCheckOut', now()->addDays(8)->toDateString())
            ->call('join')
            ->assertHasNoErrors();

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertDatabaseHas('waitlist_entries', [
            'user_id' => $guest->id,
            'bed_id' => $bed->id,
            'status' => 'waiting',
        ]);
        $this->assertStringContainsString('bedId', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\Bed', $encodedSnapshot);
        $this->assertLessThan(10_000, strlen($encodedSnapshot), 'Legacy waitlist manager snapshot should keep the full Bed model out of public state.');
    }
}
