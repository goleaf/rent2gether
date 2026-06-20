<?php

namespace Tests\Feature;

use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Host\Rooms\RoomAccessStorageStep;
use App\Livewire\Host\Rooms\RoomComfortStep;
use App\Livewire\Host\Rooms\RoomCompletionPanel;
use App\Livewire\Host\Rooms\RoomConditionStep;
use App\Livewire\Host\Rooms\RoomLayoutStep;
use App\Livewire\Host\Rooms\RoomMainInfoStep;
use App\Livewire\Host\Rooms\RoomRulesStep;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomAccessDetail;
use App\Models\RoomComfortDetail;
use App\Models\RoomConditionDetail;
use App\Models\RoomLayoutDetail;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Rooms\RoomCompletionService;
use App\Services\Rooms\RoomGuestSummaryService;
use App\Services\Rooms\RoomPrivacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ExtendedRoomFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_has_extended_detail_tables_relationships_indexes_and_cascade_delete(): void
    {
        $this->assertTrue(Schema::hasTable('room_layout_details'));
        $this->assertTrue(Schema::hasTable('room_comfort_details'));
        $this->assertTrue(Schema::hasTable('room_access_details'));
        $this->assertTrue(Schema::hasTable('room_condition_details'));
        $this->assertTrue(Schema::hasColumn('rooms', 'living_format'));
        $this->assertTrue(Schema::hasColumn('rooms', 'sleeping_places_count'));
        $this->assertTrue(Schema::hasColumn('room_translations', 'work_study_instructions'));
        $this->assertTrue(Schema::hasColumn('room_translations', 'food_rules_text'));
        $this->assertTrue(Schema::hasIndex('rooms', ['property_id', 'status']));
        $this->assertTrue(Schema::hasIndex('room_layout_details', ['room_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('room_comfort_details', ['noise_level']));
        $this->assertTrue(Schema::hasIndex('room_access_details', ['has_personal_lockers']));
        $this->assertTrue(Schema::hasIndex('room_condition_details', ['has_mold']));

        $room = Room::factory()->create();

        RoomLayoutDetail::factory()->for($room)->create([
            'area' => 18.5,
            'window_view' => 'Quiet side',
            'windows_count' => 2,
        ]);
        RoomComfortDetail::factory()->for($room)->create([
            'noise_level' => 'quiet',
            'light_level' => 'bright',
            'quiet_hours_enabled' => true,
        ]);
        RoomAccessDetail::factory()->for($room)->create([
            'has_lock' => true,
            'has_personal_lockers' => true,
            'personal_lockers_count' => 4,
        ]);
        RoomConditionDetail::factory()->for($room)->create([
            'cleanliness_level' => 'high',
            'has_mold' => false,
            'last_checked_at' => '2026-06-20 10:00:00',
        ]);

        $room = $room->fresh(['layoutDetails', 'comfortDetails', 'accessDetails', 'conditionDetails']);

        $this->assertSame('18.50', $room->layoutDetails->area);
        $this->assertSame('quiet', $room->comfortDetails->noise_level);
        $this->assertTrue($room->accessDetails->has_personal_lockers);
        $this->assertSame('high', $room->conditionDetails->cleanliness_level);

        $room->forceDelete();

        $this->assertDatabaseMissing('room_layout_details', ['room_id' => $room->id]);
        $this->assertDatabaseMissing('room_comfort_details', ['room_id' => $room->id]);
        $this->assertDatabaseMissing('room_access_details', ['room_id' => $room->id]);
        $this->assertDatabaseMissing('room_condition_details', ['room_id' => $room->id]);
    }

    public function test_room_services_build_privacy_safe_guest_summary(): void
    {
        [$room, $guest] = $this->roomWithDetails();

        $privacy = app(RoomPrivacyService::class);

        $this->assertFalse($privacy->canShowRoomNumber($guest, $room, null));
        $this->assertFalse($privacy->canShowOccupantDetails($guest, $room, null));

        $summary = app(RoomGuestSummaryService::class)->build($room->fresh([
            'translations',
            'layoutDetails',
            'comfortDetails',
            'accessDetails',
            'conditionDetails',
        ]), $guest);

        $encoded = json_encode($summary, JSON_THROW_ON_ERROR);

        $this->assertSame(__('room.public.title'), $summary['title']);
        $this->assertStringContainsString(__('room.sections.layout'), $encoded);
        $this->assertStringContainsString('Quiet side', $encoded);
        $this->assertStringContainsString(__('room.values.personal_lockers'), $encoded);
        $this->assertStringNotContainsString('Alex Private', $encoded);
        $this->assertStringNotContainsString('101', $encoded);
    }

    public function test_host_extended_room_steps_update_data_and_block_other_hosts(): void
    {
        [$room] = $this->roomWithDetails();
        $host = $room->property->host;
        $otherHost = User::factory()->create(['is_host' => true]);

        Livewire::actingAs($host)
            ->test(RoomMainInfoStep::class, ['room' => $room])
            ->set('titleEn', 'Quiet mixed room')
            ->set('titleRu', 'Тихая смешанная комната')
            ->set('roomNumber', '101')
            ->set('roomType', RoomType::Shared->value)
            ->set('livingFormat', 'long_stay')
            ->set('genderPolicy', GenderType::Mixed->value)
            ->set('sleepingPlacesCount', 4)
            ->set('maxGuests', 4)
            ->set('status', RoomStatus::Active->value)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee(__('room.messages.saved'));

        Livewire::actingAs($host)
            ->test(RoomLayoutStep::class, ['room' => $room])
            ->set('area', 18.5)
            ->set('windowsCount', 2)
            ->set('windowView', 'Quiet side')
            ->set('cardinalDirection', 'east')
            ->set('hasBalcony', true)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(RoomComfortStep::class, ['room' => $room])
            ->set('hasHeating', true)
            ->set('hasAirConditioning', true)
            ->set('noiseLevel', 'quiet')
            ->set('lightLevel', 'bright')
            ->set('quietHoursEnabled', true)
            ->set('quietHoursStart', '22:00')
            ->set('quietHoursEnd', '07:00')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(RoomAccessStorageStep::class, ['room' => $room])
            ->set('hasLock', true)
            ->set('hasKey', true)
            ->set('hasPersonalLockers', true)
            ->set('personalLockersCount', 4)
            ->set('hasDesk', true)
            ->set('canStoreFood', false)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(RoomConditionStep::class, ['room' => $room])
            ->set('conditionState', 'good')
            ->set('repairState', 'good')
            ->set('cleanlinessLevel', 'high')
            ->set('hasMold', false)
            ->set('hasInsects', false)
            ->set('lastCleanedAt', '2026-06-19')
            ->set('lastCheckedAt', '2026-06-20')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(RoomRulesStep::class, ['room' => $room])
            ->set('roomRulesTextEn', 'Keep quiet after 22:00 and use personal lamps at night.')
            ->set('roomRulesTextRu', 'После 22:00 соблюдайте тишину и используйте личные лампы ночью.')
            ->set('foodRulesTextEn', 'Food is stored in the kitchen only.')
            ->set('foodRulesTextRu', 'Еду хранить только на кухне.')
            ->set('conflictInstructionsEn', 'Message the host if someone takes your place.')
            ->set('conflictInstructionsRu', 'Напишите хозяину, если кто-то занял ваше место.')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(RoomCompletionPanel::class, ['room' => $room->fresh()])
            ->assertSee(__('room.completion.title'));

        $this->assertGreaterThan(0, app(RoomCompletionService::class)->percentage($room->fresh([
            'translations',
            'layoutDetails',
            'comfortDetails',
            'accessDetails',
            'conditionDetails',
        ])));

        Livewire::actingAs($otherHost)
            ->test(RoomLayoutStep::class, ['room' => $room])
            ->assertForbidden();

        $this->assertDatabaseHas('room_access_details', [
            'room_id' => $room->id,
            'has_lock' => true,
            'has_personal_lockers' => true,
        ]);
        $this->assertDatabaseHas('room_translations', [
            'room_id' => $room->id,
            'locale' => 'en',
            'room_rules_text' => 'Keep quiet after 22:00 and use personal lamps at night.',
        ]);
    }

    public function test_listing_detail_renders_public_room_sections_without_private_occupant_data(): void
    {
        [$room] = $this->roomWithDetails();
        $place = SleepingPlace::factory()->for($room)->for($room->property)->create([
            'status' => SleepingPlaceStatus::Active,
        ]);

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee(__('room.public.title'))
            ->assertSee(__('room.sections.comfort'))
            ->assertSee('Quiet side')
            ->assertSee(__('room.values.personal_lockers'))
            ->assertDontSee('Alex Private')
            ->assertDontSee('Room 101');
    }

    /**
     * @return array{0: Room, 1: User}
     */
    private function roomWithDetails(): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $guest = User::factory()->create(['name' => 'Alex Private']);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'user_id' => $host->id,
                'host_user_id' => $host->id,
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
                'city' => 'Vilnius',
                'district' => 'Old Town',
                'show_exact_address_before_booking' => false,
            ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'room_number' => '101',
            'gender_policy' => GenderType::Mixed,
            'sleeping_places_count' => 4,
            'free_sleeping_places_count' => 1,
            'occupied_sleeping_places_count' => 2,
            'current_guests_count' => 2,
            'max_guests' => 4,
        ]);

        $room->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet mixed room',
            'short_description' => 'A shared mixed room with quiet hours.',
            'full_description' => 'A calm shared room for longer stays.',
            'room_rules_text' => 'Keep quiet after 22:00.',
            'who_lives_nearby_text' => 'Two other guests may be nearby.',
            'storage_instructions' => 'Use the personal locker and shared wardrobe.',
            'work_study_instructions' => 'Use a laptop quietly at night.',
            'food_rules_text' => 'Food is stored in the kitchen only.',
            'conflict_instructions' => 'Message the host if someone takes your place.',
        ]);
        $room->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихая смешанная комната',
            'short_description' => 'Общая смешанная комната с тихими часами.',
            'room_rules_text' => 'После 22:00 соблюдайте тишину.',
        ]);

        RoomLayoutDetail::factory()->for($room)->create([
            'area' => 18.5,
            'windows_count' => 2,
            'window_view' => 'Quiet side',
            'cardinal_direction' => 'east',
            'has_balcony' => true,
        ]);
        RoomComfortDetail::factory()->for($room)->create([
            'has_heating' => true,
            'has_air_conditioning' => true,
            'light_level' => 'bright',
            'noise_level' => 'quiet',
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
        ]);
        RoomAccessDetail::factory()->for($room)->create([
            'has_door' => true,
            'has_lock' => true,
            'has_key' => true,
            'has_personal_lockers' => true,
            'personal_lockers_count' => 4,
            'lockers_have_locks' => true,
            'has_desk' => true,
            'can_store_food' => false,
        ]);
        RoomConditionDetail::factory()->for($room)->create([
            'condition_state' => 'good',
            'repair_state' => 'good',
            'cleanliness_level' => 'high',
            'has_mold' => false,
            'has_insects' => false,
            'last_checked_at' => '2026-06-20 10:00:00',
        ]);

        return [$room->fresh(), $guest];
    }
}
