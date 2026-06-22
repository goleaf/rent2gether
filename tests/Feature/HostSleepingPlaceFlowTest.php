<?php

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Host\SleepingPlaceForm;
use App\Livewire\Host\SleepingPlaceList;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\Rule;
use App\Models\RuleTranslation;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HostSleepingPlaceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_create_sleeping_place_from_wizard(): void
    {
        [$host, $room] = $this->hostRoom();

        Livewire::actingAs($host)
            ->test(SleepingPlaceForm::class, ['room' => $room])
            ->set('placeNumber', '1A')
            ->set('displayName', 'Lower bunk by the window')
            ->set('type', SleepingPlaceType::BunkBottom->value)
            ->set('status', SleepingPlaceStatus::Active->value)
            ->set('lengthCm', 200)
            ->set('widthCm', 90)
            ->set('hasPillow', true)
            ->set('hasBlanket', true)
            ->set('hasPowerSocket', true)
            ->set('hasHook', true)
            ->set('nearWindow', true)
            ->set('privacyLevel', 'good')
            ->set('noiseLevel', 'low')
            ->set('maxGuests', 1)
            ->set('basePricePerNight', 22.50)
            ->set('currency', 'EUR')
            ->set('minNights', 2)
            ->set('requiresHostApproval', true)
            ->set('translations.en.title', 'Lower bunk by the window')
            ->set('translations.ru.title', 'Нижняя кровать у окна')
            ->set('translations.en.description', 'A quiet lower bunk with a socket and shelf.')
            ->set('translations.ru.description', 'Тихая нижняя кровать с розеткой и полкой.')
            ->set('translations.en.special_conditions', 'Please keep the curtain open during cleaning.')
            ->set('translations.ru.special_conditions', 'Пожалуйста, оставляйте шторку открытой для уборки.')
            ->call('publish')
            ->assertHasNoErrors()
            ->assertRedirect();

        $sleepingPlace = SleepingPlace::query()->firstOrFail();

        $this->assertSame($room->id, $sleepingPlace->room_id);
        $this->assertSame($room->property_id, $sleepingPlace->property_id);
        $this->assertSame(SleepingPlaceStatus::Active, $sleepingPlace->status);
        $this->assertSame(SleepingPlaceType::BunkBottom, $sleepingPlace->type);
        $this->assertTrue($sleepingPlace->has_hook);
        $this->assertTrue($sleepingPlace->near_window);
        $this->assertSame('22.50', $sleepingPlace->base_price_per_night);
        $this->assertDatabaseHas('sleeping_place_translations', [
            'sleeping_place_id' => $sleepingPlace->id,
            'locale' => 'en',
            'title' => 'Lower bunk by the window',
            'special_conditions' => 'Please keep the curtain open during cleaning.',
        ]);
        $this->assertDatabaseHas('sleeping_place_translations', [
            'sleeping_place_id' => $sleepingPlace->id,
            'locale' => 'ru',
            'title' => 'Нижняя кровать у окна',
        ]);
    }

    public function test_host_can_duplicate_sleeping_place_as_draft(): void
    {
        [$host, $room] = $this->hostRoom();
        $rule = $this->rule();
        $sleepingPlace = SleepingPlace::factory()
            ->for($room)
            ->for($room->property)
            ->hasTranslations(1, ['locale' => 'en', 'title' => 'Original place'])
            ->create([
                'display_name' => 'Original place',
                'status' => SleepingPlaceStatus::Active,
                'has_hook' => true,
                'near_window' => true,
            ]);
        $sleepingPlace->rules()->attach($rule);

        Livewire::actingAs($host)
            ->test(SleepingPlaceList::class, ['room' => $room])
            ->call('duplicateSleepingPlace', $sleepingPlace->id)
            ->assertHasNoErrors();

        $copy = SleepingPlace::query()
            ->where('room_id', $room->id)
            ->where('id', '!=', $sleepingPlace->id)
            ->firstOrFail();

        $this->assertSame(SleepingPlaceStatus::Draft, $copy->status);
        $this->assertTrue($copy->has_hook);
        $this->assertTrue($copy->near_window);
        $this->assertTrue($copy->rules()->whereKey($rule)->exists());
        $this->assertDatabaseHas('sleeping_place_translations', [
            'sleeping_place_id' => $copy->id,
            'locale' => 'en',
        ]);
    }

    public function test_host_can_bulk_create_similar_sleeping_places(): void
    {
        [$host, $room] = $this->hostRoom();

        Livewire::actingAs($host)
            ->test(SleepingPlaceList::class, ['room' => $room])
            ->set('bulkCount', 3)
            ->set('bulkTitlePrefix', 'Capsule place')
            ->set('bulkType', SleepingPlaceType::Capsule->value)
            ->set('bulkBasePrice', 18)
            ->set('bulkCurrency', 'EUR')
            ->set('bulkMinNights', 1)
            ->set('bulkMaxGuests', 1)
            ->call('bulkCreate')
            ->assertHasNoErrors();

        $this->assertSame(3, $room->sleepingPlaces()->count());
        $this->assertDatabaseHas('sleeping_places', [
            'room_id' => $room->id,
            'type' => SleepingPlaceType::Capsule->value,
            'status' => SleepingPlaceStatus::Draft->value,
            'display_name' => 'Capsule place 1',
            'base_price_per_night' => 18,
        ]);
        $this->assertDatabaseHas('sleeping_place_translations', [
            'locale' => 'en',
            'title' => 'Capsule place 1',
        ]);
    }

    public function test_sleeping_place_wizard_validation_is_friendly_and_server_side(): void
    {
        [$host, $room] = $this->hostRoom();

        Livewire::actingAs($host)
            ->test(SleepingPlaceForm::class, ['room' => $room])
            ->set('displayName', '')
            ->set('basePricePerNight', -1)
            ->set('translations.en.title', '')
            ->set('translations.ru.title', '')
            ->call('publish')
            ->assertHasErrors([
                'displayName' => 'required',
                'basePricePerNight' => 'min',
                'translations.en.title' => 'required',
                'translations.ru.title' => 'required',
            ]);

        $this->assertDatabaseCount('sleeping_places', 0);
    }

    public function test_host_can_save_sleeping_place_photos_from_edit_form(): void
    {
        Storage::fake('public');

        [$host, $room] = $this->hostRoom();
        $sleepingPlace = SleepingPlace::factory()
            ->for($room)
            ->for($room->property)
            ->hasTranslations(1, ['locale' => 'en', 'title' => 'Editable lower bunk'])
            ->hasTranslations(1, ['locale' => 'ru', 'title' => 'Редактируемая нижняя кровать'])
            ->create([
                'display_name' => 'Editable lower bunk',
                'status' => SleepingPlaceStatus::Active,
                'base_price_per_night' => 24,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_guests' => 1,
            ]);

        Livewire::actingAs($host)
            ->test(SleepingPlaceForm::class, ['room' => $room, 'sleepingPlace' => $sleepingPlace])
            ->set('step', 7)
            ->set('exactPhoto', UploadedFile::fake()->image('exact-place.jpg', 1200, 800)->size(500))
            ->set('detailPhoto', UploadedFile::fake()->image('detail-place.jpg', 900, 700)->size(350))
            ->call('publish')
            ->assertHasNoErrors()
            ->assertRedirect();

        $mediaItems = MediaItem::query()
            ->where('mediable_type', SleepingPlace::class)
            ->where('mediable_id', $sleepingPlace->id)
            ->orderBy('collection')
            ->get();

        $this->assertCount(2, $mediaItems);
        $this->assertSame(['detail', 'exact_place'], $mediaItems->pluck('collection')->all());

        foreach ($mediaItems as $mediaItem) {
            Storage::disk('public')->assertExists($mediaItem->thumb_path);
            Storage::disk('public')->assertExists($mediaItem->mobile_path);
            Storage::disk('public')->assertExists($mediaItem->full_path);

            $this->assertDatabaseHas('sleeping_place_photos', [
                'sleeping_place_id' => $sleepingPlace->id,
                'media_item_id' => $mediaItem->id,
                'path' => $mediaItem->path,
                'thumbnail_path' => $mediaItem->thumb_path,
                'status' => 'active',
            ]);
        }
    }

    public function test_sleeping_place_list_shows_translated_card_content(): void
    {
        [$host, $room] = $this->hostRoom();
        $sleepingPlace = SleepingPlace::factory()->for($room)->for($room->property)->create([
            'display_name' => 'Fallback place',
            'base_price_per_night' => 19,
        ]);
        $sleepingPlace->translations()->create([
            'locale' => 'en',
            'title' => 'English sleeping place',
            'description' => 'English exact place description.',
            'special_conditions' => 'English special condition.',
        ]);
        $sleepingPlace->translations()->create([
            'locale' => 'ru',
            'title' => 'Русское спальное место',
            'description' => 'Русское описание точного места.',
            'special_conditions' => 'Русское особое условие.',
        ]);

        $this->actingAs($host)
            ->get(route('host.sleeping-places.index', ['locale' => 'en', 'room' => $room]))
            ->assertOk()
            ->assertSeeLivewire(SleepingPlaceList::class)
            ->assertSee('English sleeping place')
            ->assertSee('English exact place description.')
            ->assertSee('English special condition.');

        $this->actingAs($host)
            ->get(route('host.sleeping-places.index', ['locale' => 'ru', 'room' => $room]))
            ->assertOk()
            ->assertSee('Русское спальное место')
            ->assertSee('Русское описание точного места.')
            ->assertSee('Русское особое условие.');
    }

    /**
     * @return array{0: User, 1: Room}
     */
    private function hostRoom(): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'title' => 'Sleeping place property',
            ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Shared room',
        ]);

        return [$host, $room];
    }

    private function rule(): Rule
    {
        $rule = Rule::factory()->create([
            'slug' => 'quiet-place',
            'name_normalized' => 'quiet place',
        ]);

        RuleTranslation::factory()->for($rule)->create([
            'locale' => 'en',
            'name' => 'Quiet place',
        ]);

        return $rule;
    }
}
