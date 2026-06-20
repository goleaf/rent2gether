<?php

namespace Tests\Feature;

use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Host\PropertyShow;
use App\Livewire\Host\RoomForm;
use App\Models\Property;
use App\Models\Room;
use App\Models\Rule;
use App\Models\RuleTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HostRoomFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_create_room_draft_from_wizard(): void
    {
        [$host, $property] = $this->hostProperty();

        Livewire::actingAs($host)
            ->test(RoomForm::class, ['property' => $property])
            ->set('roomNumber', '2A')
            ->set('title', 'Quiet blue room')
            ->set('roomType', RoomType::Shared->value)
            ->set('genderPolicy', GenderType::NoRestriction->value)
            ->set('status', RoomStatus::Draft->value)
            ->set('isPrivate', false)
            ->set('isPassThrough', true)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 2);

        $this->assertDatabaseHas('rooms', [
            'property_id' => $property->id,
            'title' => 'Quiet blue room',
            'room_number' => '2A',
            'type' => RoomType::Shared->value,
            'gender_policy' => GenderType::NoRestriction->value,
            'status' => RoomStatus::Draft->value,
            'is_pass_through' => true,
        ]);
    }

    public function test_host_can_update_room_with_translations(): void
    {
        [$host, $property] = $this->hostProperty();
        $room = Room::factory()->for($property)->create(['status' => RoomStatus::Draft]);

        Livewire::actingAs($host)
            ->test(RoomForm::class, ['property' => $property, 'room' => $room])
            ->set('title', 'Updated room')
            ->set('status', RoomStatus::Active->value)
            ->set('bedsCount', 2)
            ->set('maxGuests', 2)
            ->set('translations.en.description', 'A calm shared room with two sleeping places.')
            ->set('translations.ru.description', 'Спокойная общая комната с двумя спальными местами.')
            ->set('translations.en.notes', 'Please keep the window closed at night.')
            ->set('translations.ru.notes', 'Пожалуйста, закрывайте окно ночью.')
            ->call('publish')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'title' => 'Updated room',
            'status' => RoomStatus::Active->value,
            'beds_count' => 2,
        ]);
        $this->assertDatabaseHas('room_translations', [
            'room_id' => $room->id,
            'locale' => 'en',
            'description' => 'A calm shared room with two sleeping places.',
            'notes' => 'Please keep the window closed at night.',
        ]);
        $this->assertDatabaseHas('room_translations', [
            'room_id' => $room->id,
            'locale' => 'ru',
            'description' => 'Спокойная общая комната с двумя спальными местами.',
            'notes' => 'Пожалуйста, закрывайте окно ночью.',
        ]);
    }

    public function test_host_can_duplicate_room_as_draft(): void
    {
        [$host, $property] = $this->hostProperty();
        $rule = $this->rule();
        $room = Room::factory()
            ->for($property)
            ->hasTranslations(1, ['locale' => 'en', 'title' => 'Original room'])
            ->create(['title' => 'Original room', 'status' => RoomStatus::Active]);
        $room->rules()->attach($rule);

        Livewire::actingAs($host)
            ->test(PropertyShow::class, ['property' => $property])
            ->call('duplicateRoom', $room->id)
            ->assertHasNoErrors();

        $copy = Room::query()
            ->where('property_id', $property->id)
            ->where('id', '!=', $room->id)
            ->firstOrFail();

        $this->assertSame(RoomStatus::Draft, $copy->status);
        $this->assertStringContainsString(__('host.room_wizard.copy_suffix'), $copy->title);
        $this->assertTrue($copy->rules()->whereKey($rule)->exists());
        $this->assertDatabaseHas('room_translations', [
            'room_id' => $copy->id,
            'locale' => 'en',
        ]);
    }

    public function test_host_can_delete_draft_room_only(): void
    {
        [$host, $property] = $this->hostProperty();
        $draft = Room::factory()->for($property)->create(['status' => RoomStatus::Draft]);
        $active = Room::factory()->for($property)->create(['status' => RoomStatus::Active]);

        Livewire::actingAs($host)
            ->test(PropertyShow::class, ['property' => $property])
            ->call('deleteDraftRoom', $active->id)
            ->call('deleteDraftRoom', $draft->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('rooms', ['id' => $active->id]);
        $this->assertDatabaseMissing('rooms', ['id' => $draft->id]);
    }

    public function test_beds_count_can_generate_sleeping_place_drafts(): void
    {
        [$host, $property] = $this->hostProperty();

        Livewire::actingAs($host)
            ->test(RoomForm::class, ['property' => $property])
            ->set('title', 'Generated places room')
            ->set('bedsCount', 3)
            ->set('maxGuests', 3)
            ->set('translations.en.description', 'A room prepared for generated sleeping places.')
            ->set('translations.ru.description', 'Комната подготовлена для созданных спальных мест.')
            ->set('generateSleepingPlacesAfterSave', true)
            ->call('publish')
            ->assertHasNoErrors()
            ->assertRedirect();

        $room = Room::query()->firstOrFail();

        $this->assertSame(3, $room->sleepingPlaces()->count());
        $this->assertDatabaseCount('sleeping_places', 3);
        $this->assertDatabaseHas('sleeping_places', [
            'room_id' => $room->id,
            'property_id' => $property->id,
            'status' => SleepingPlaceStatus::Draft->value,
            'place_number' => '1',
        ]);
    }

    public function test_property_room_list_shows_translated_room_content(): void
    {
        [$host, $property] = $this->hostProperty();
        $room = Room::factory()->for($property)->create(['title' => 'Fallback room']);
        $room->translations()->create([
            'locale' => 'en',
            'title' => 'English room',
            'description' => 'English room description for guests.',
            'notes' => 'English room note.',
        ]);
        $room->translations()->create([
            'locale' => 'ru',
            'title' => 'Русская комната',
            'description' => 'Русское описание комнаты для гостей.',
            'notes' => 'Русская заметка о комнате.',
        ]);

        $this->actingAs($host)
            ->get(route('host.properties.show', ['locale' => 'en', 'property' => $property]))
            ->assertOk()
            ->assertSeeLivewire(PropertyShow::class)
            ->assertSee('English room')
            ->assertSee('English room description for guests.')
            ->assertSee('English room note.');

        $this->actingAs($host)
            ->get(route('host.properties.show', ['locale' => 'ru', 'property' => $property]))
            ->assertOk()
            ->assertSee('Русская комната')
            ->assertSee('Русское описание комнаты для гостей.')
            ->assertSee('Русская заметка о комнате.');
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
                'title' => 'Host test property',
            ]);

        return [$host, $property];
    }

    private function rule(): Rule
    {
        $rule = Rule::factory()->create([
            'slug' => 'quiet-hours',
            'name_normalized' => 'quiet hours',
        ]);

        RuleTranslation::factory()->for($rule)->create([
            'locale' => 'en',
            'name' => 'Quiet hours',
        ]);

        return $rule;
    }
}
