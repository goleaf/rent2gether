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
use App\Livewire\Host\Rooms\RoomMediaStep;
use App\Livewire\Host\Rooms\RoomRulesStep;
use App\Models\MediaItem;
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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
        $this->assertTrue(Schema::hasColumn('rooms', 'occupied_sleeping_places_count'));
        $this->assertTrue(Schema::hasColumn('rooms', 'free_sleeping_places_count'));
        $this->assertTrue(Schema::hasColumn('rooms', 'current_guests_count'));
        $this->assertTrue(Schema::hasColumn('rooms', 'can_work_at_night'));
        $this->assertTrue(Schema::hasColumn('rooms', 'can_eat'));
        $this->assertTrue(Schema::hasColumn('room_comfort_details', 'can_work_at_night'));
        $this->assertTrue(Schema::hasColumn('room_comfort_details', 'can_eat_in_room'));
        $this->assertTrue(Schema::hasColumn('room_comfort_details', 'can_store_food_in_room'));
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
            ->set('translations.en.title', 'Quiet mixed room')
            ->set('translations.ru.title', 'Тихая смешанная комната')
            ->set('roomNumber', '101')
            ->set('roomType', RoomType::Shared->value)
            ->set('livingFormat', 'long_stay')
            ->set('genderPolicy', GenderType::Mixed->value)
            ->set('sleepingPlacesCount', 4)
            ->set('occupiedSleepingPlacesCount', 2)
            ->set('freeSleepingPlacesCount', 2)
            ->set('currentGuestsCount', 2)
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
            ->set('hasFan', true)
            ->set('noiseLevel', 'quiet')
            ->set('lightLevel', 'bright')
            ->set('canWorkAtNight', true)
            ->set('canEatInRoom', true)
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
            ->set('hasChairs', true)
            ->set('hasMirror', true)
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
            ->set('translations.en.room_rules_text', 'Keep quiet after 22:00 and use personal lamps at night.')
            ->set('translations.ru.room_rules_text', 'После 22:00 соблюдайте тишину и используйте личные лампы ночью.')
            ->set('translations.en.food_rules_text', 'Food is stored in the kitchen only.')
            ->set('translations.ru.food_rules_text', 'Еду хранить только на кухне.')
            ->set('translations.en.conflict_instructions', 'Message the host if someone takes your place.')
            ->set('translations.ru.conflict_instructions', 'Напишите хозяину, если кто-то занял ваше место.')
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
        $this->assertDatabaseHas('room_comfort_details', [
            'room_id' => $room->id,
            'can_work_at_night' => true,
            'can_eat_in_room' => true,
        ]);
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'occupied_sleeping_places_count' => 2,
            'free_sleeping_places_count' => 2,
            'current_guests_count' => 2,
            'has_window' => true,
            'area_sqm' => 18.5,
            'has_room_key' => true,
            'has_lockers' => true,
            'has_chairs' => true,
            'has_fan' => true,
            'can_work_at_night' => true,
            'can_eat' => true,
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

    public function test_room_main_step_rejects_impossible_occupancy_counts(): void
    {
        [$room] = $this->roomWithDetails();
        $host = $room->property->host;

        Livewire::actingAs($host)
            ->test(RoomMainInfoStep::class, ['room' => $room])
            ->set('sleepingPlacesCount', 2)
            ->set('occupiedSleepingPlacesCount', 2)
            ->set('freeSleepingPlacesCount', 1)
            ->call('save')
            ->assertHasErrors(['freeSleepingPlacesCount']);

        Livewire::actingAs($host)
            ->test(RoomMainInfoStep::class, ['room' => $room])
            ->set('maxGuests', 2)
            ->set('currentGuestsCount', 3)
            ->call('save')
            ->assertHasErrors(['currentGuestsCount']);
    }

    public function test_room_media_step_uploads_and_deletes_room_video_with_authorization(): void
    {
        Storage::fake('public');

        [$room] = $this->roomWithDetails();
        $host = $room->property->host;
        $otherHost = User::factory()->create(['is_host' => true]);
        $video = UploadedFile::fake()->create('room-tour.mp4', 1024, 'video/mp4');

        Livewire::actingAs($host)
            ->test(RoomMediaStep::class, ['room' => $room])
            ->set('videoFile', $video)
            ->set('videoCaptions.en', 'Short room tour')
            ->set('videoCaptions.ru', 'Короткое видео комнаты')
            ->call('saveVideo')
            ->assertHasNoErrors()
            ->assertSet('statusMessage', __('room.media.saved_video'));

        $media = MediaItem::query()->where('collection', 'video')->firstOrFail();

        $this->assertSame(Room::class, $media->mediable_type);
        $this->assertSame($room->id, $media->mediable_id);
        $this->assertSame(Room::class, $media->owner_type);
        $this->assertSame($room->id, $media->owner_id);
        $this->assertSame('video/mp4', $media->mime_type);
        $this->assertSame('Short room tour', $media->translations->firstWhere('locale', 'en')?->caption);
        Storage::disk('public')->assertExists($media->path);
        $this->assertDatabaseMissing('room_photos', ['media_item_id' => $media->id]);

        Livewire::actingAs($otherHost)
            ->test(RoomMediaStep::class, ['room' => $room])
            ->assertForbidden();

        Livewire::actingAs($host)
            ->test(RoomMediaStep::class, ['room' => $room])
            ->call('deleteVideo', $media->id)
            ->assertHasNoErrors();

        $this->assertModelMissing($media);
        Storage::disk('public')->assertMissing($media->path);
    }

    public function test_room_media_step_rejects_invalid_video_file(): void
    {
        Storage::fake('public');

        [$room] = $this->roomWithDetails();
        $host = $room->property->host;
        $file = UploadedFile::fake()->create('room-notes.pdf', 20, 'application/pdf');

        Livewire::actingAs($host)
            ->test(RoomMediaStep::class, ['room' => $room])
            ->set('videoFile', $file)
            ->call('saveVideo')
            ->assertHasErrors(['videoFile']);

        $this->assertDatabaseMissing('media_items', [
            'mediable_type' => Room::class,
            'mediable_id' => $room->id,
            'collection' => 'video',
        ]);
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
