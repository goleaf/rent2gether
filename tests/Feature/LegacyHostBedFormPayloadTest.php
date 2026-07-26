<?php

namespace Tests\Feature;

use App\Enums\BedStatus;
use App\Enums\BedType;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Livewire\Host\BedForm;
use App\Models\Bed;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LegacyHostBedFormPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_host_bed_form_keeps_room_and_bed_models_out_of_public_state(): void
    {
        ['host' => $host, 'room' => $room, 'bed' => $bed] = $this->legacyHostBedFixture();

        $component = Livewire::actingAs($host)
            ->test(BedForm::class, ['room' => $room, 'bed' => $bed])
            ->assertSet('roomId', $room->id)
            ->assertSet('bedId', $bed->id)
            ->assertSet('title', 'Legacy host bed')
            ->assertViewHas('room', fn (Room $viewRoom): bool => $viewRoom->is($room))
            ->assertViewHas('bed', fn (?Bed $viewBed): bool => $viewBed?->is($bed) === true)
            ->assertSee('Legacy host room');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('roomId', $encodedSnapshot);
        $this->assertStringContainsString('bedId', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\Room', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\Bed', $encodedSnapshot);
        $this->assertLessThan(14_000, strlen($encodedSnapshot), 'Legacy host bed form snapshot should keep full models out of public state.');
    }

    public function test_legacy_host_bed_form_rejects_room_owned_by_another_host(): void
    {
        ['room' => $room] = $this->legacyHostBedFixture();
        $otherHost = User::factory()->create(['is_host' => true]);

        $this->actingAs($otherHost)
            ->get(route('host.beds.create', ['locale' => 'en', 'room' => $room]))
            ->assertForbidden();
    }

    public function test_legacy_host_bed_form_rejects_bed_from_another_room(): void
    {
        ['host' => $host, 'room' => $room] = $this->legacyHostBedFixture();
        ['bed' => $otherBed] = $this->legacyHostBedFixture($host);

        $this->actingAs($host)
            ->get(route('host.beds.edit', ['locale' => 'en', 'room' => $room, 'bed' => $otherBed]))
            ->assertNotFound();
    }

    /**
     * @return array{host:User,property:Property,room:Room,bed:Bed}
     */
    private function legacyHostBedFixture(?User $host = null): array
    {
        $host ??= User::factory()->create(['is_host' => true]);
        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
            'status' => PropertyStatus::Active,
            'title' => 'Legacy host property',
        ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Legacy host room',
        ]);
        $bed = Bed::factory()->for($room)->create([
            'title' => 'Legacy host bed',
            'type' => BedType::Single,
            'status' => BedStatus::Active,
            'price_per_night' => 20,
            'min_nights' => 1,
        ]);

        return [
            'host' => $host,
            'property' => $property,
            'room' => $room,
            'bed' => $bed,
        ];
    }
}
