<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_loads(): void
    {
        $response = $this->get(route('search.index', ['locale' => 'en']));

        $response->assertStatus(200);
    }

    public function test_search_page_shows_active_beds(): void
    {
        $host = User::factory()->create();
        $property = Property::factory()->for($host, 'host')->create(['city' => 'Berlin', 'status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => 'active']);
        Bed::factory()->for($room)->create(['title' => 'Test Bed Alpha', 'status' => 'active']);

        $response = $this->get(route('search.index', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertSee('Test Bed Alpha');
    }

    public function test_search_filters_by_city(): void
    {
        $host = User::factory()->create();
        $propBerlin = Property::factory()->for($host, 'host')->create(['city' => 'Berlin', 'status' => 'active']);
        $propParis = Property::factory()->for($host, 'host')->create(['city' => 'Paris', 'status' => 'active']);
        $roomBerlin = Room::factory()->for($propBerlin)->create(['status' => 'active']);
        $roomParis = Room::factory()->for($propParis)->create(['status' => 'active']);
        Bed::factory()->for($roomBerlin)->create(['title' => 'Berlin Bed', 'status' => 'active']);
        Bed::factory()->for($roomParis)->create(['title' => 'Paris Bed', 'status' => 'active']);

        $response = $this->get(route('search.index', ['locale' => 'en', 'city' => 'Berlin']));

        $response->assertStatus(200);
        $response->assertSee('Berlin Bed');
        $response->assertDontSee('Paris Bed');
    }

    public function test_bed_detail_page_loads(): void
    {
        $host = User::factory()->create();
        $property = Property::factory()->for($host, 'host')->create(['status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => 'active']);
        $bed = Bed::factory()->for($room)->create(['status' => 'active', 'title' => 'Cozy Single']);

        $response = $this->get(route('beds.show', ['locale' => 'en', 'bed' => $bed]));

        $response->assertStatus(200);
        $response->assertSee('Cozy Single');
    }

    public function test_bed_detail_shows_host_name(): void
    {
        $host = User::factory()->create(['name' => 'Jane Host']);
        $property = Property::factory()->for($host, 'host')->create(['status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => 'active']);
        $bed = Bed::factory()->for($room)->create(['status' => 'active']);

        $response = $this->get(route('beds.show', ['locale' => 'en', 'bed' => $bed]));

        $response->assertStatus(200);
        $response->assertSee('Jane Host');
    }
}
