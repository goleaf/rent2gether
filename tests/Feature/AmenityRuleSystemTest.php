<?php

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Catalog\AmenityPicker;
use App\Livewire\Catalog\RulePicker;
use App\Models\Amenity;
use App\Models\Property;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Catalog\AmenityRuleCatalog;
use Database\Seeders\AmenityRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class AmenityRuleSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_seeded_catalog_contains_required_amenities_and_rules_with_translations(): void
    {
        $this->seed(AmenityRuleSeeder::class);

        $this->assertSame(count(AmenityRuleCatalog::amenities()), Amenity::query()->count());
        $this->assertSame(count(AmenityRuleCatalog::rules()), Rule::query()->count());
        $this->assertDatabaseHas('amenities', [
            'slug' => 'fast_wifi',
            'category' => 'work_study',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('amenity_translations', [
            'locale' => 'ru',
            'name' => 'Быстрый Wi-Fi',
        ]);
        $this->assertDatabaseHas('rules', [
            'slug' => 'quiet_hours_after_22',
            'category' => 'quiet_hours',
            'requires_confirmation' => true,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('rule_translations', [
            'locale' => 'ru',
            'name' => 'Тихие часы после 22:00',
        ]);
        $this->assertDatabaseHas('rules', [
            'slug' => 'smoking_allowed',
            'category' => 'smoking',
            'requires_confirmation' => true,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('rule_translations', [
            'locale' => 'ru',
            'name' => 'Можно курить',
        ]);
        $this->assertDatabaseHas('rules', [
            'slug' => 'no_sleeping_place_changes_without_permission',
            'category' => 'shared_room_behavior',
            'requires_confirmation' => true,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('rule_translations', [
            'locale' => 'ru',
            'name' => 'Нельзя менять спальное место без разрешения',
        ]);
    }

    public function test_amenity_picker_attaches_and_detaches_amenities(): void
    {
        $this->seed(AmenityRuleSeeder::class);
        [$host, $property] = $this->hostProperty();
        $amenity = Amenity::query()->where('slug', 'wifi')->firstOrFail();

        Livewire::actingAs($host)
            ->test(AmenityPicker::class, [
                'ownerType' => 'property',
                'ownerId' => $property->id,
                'context' => 'property',
            ])
            ->call('toggle', $amenity->id)
            ->assertSet('selectedIds', [$amenity->id]);

        $this->assertDatabaseHas('property_amenity', [
            'property_id' => $property->id,
            'amenity_id' => $amenity->id,
        ]);

        Livewire::actingAs($host)
            ->test(AmenityPicker::class, [
                'ownerType' => 'property',
                'ownerId' => $property->id,
                'context' => 'property',
            ])
            ->call('toggle', $amenity->id)
            ->assertSet('selectedIds', []);

        $this->assertDatabaseMissing('property_amenity', [
            'property_id' => $property->id,
            'amenity_id' => $amenity->id,
        ]);
    }

    public function test_rule_picker_attaches_and_detaches_rules(): void
    {
        $this->seed(AmenityRuleSeeder::class);
        [$host, $property] = $this->hostProperty();
        $room = Room::factory()->for($property)->create(['status' => RoomStatus::Active]);
        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create(['status' => SleepingPlaceStatus::Draft]);
        $rule = Rule::query()->where('slug', 'quiet_hours_after_22')->firstOrFail();

        Livewire::actingAs($host)
            ->test(RulePicker::class, [
                'ownerType' => 'sleeping_place',
                'ownerId' => $sleepingPlace->id,
                'context' => 'sleeping_place',
            ])
            ->call('toggle', $rule->id)
            ->assertSet('selectedIds', [$rule->id]);

        $this->assertDatabaseHas('sleeping_place_rule', [
            'sleeping_place_id' => $sleepingPlace->id,
            'rule_id' => $rule->id,
        ]);

        Livewire::actingAs($host)
            ->test(RulePicker::class, [
                'ownerType' => 'sleeping_place',
                'ownerId' => $sleepingPlace->id,
                'context' => 'sleeping_place',
            ])
            ->call('toggle', $rule->id)
            ->assertSet('selectedIds', []);

        $this->assertDatabaseMissing('sleeping_place_rule', [
            'sleeping_place_id' => $sleepingPlace->id,
            'rule_id' => $rule->id,
        ]);
    }

    public function test_pickers_display_localized_labels_and_search_results(): void
    {
        $this->seed(AmenityRuleSeeder::class);
        app()->setLocale('ru');

        Livewire::test(AmenityPicker::class, ['context' => 'property'])
            ->set('search', 'кух')
            ->assertSee('Кухня')
            ->assertDontSee('Wi-Fi');

        Livewire::test(RulePicker::class, ['context' => 'property'])
            ->set('search', '22')
            ->assertSee('Тихие часы после 22:00')
            ->assertDontSee('Без животных');
    }

    /**
     * @return array{0: User, 1: Property}
     */
    private function hostProperty(): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'title' => 'Catalog test property',
            ]);

        return [$host, $property];
    }
}
