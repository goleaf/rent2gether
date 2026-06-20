<?php

namespace Tests\Feature;

use App\Actions\Media\StoreMediaItemAction;
use App\Enums\RoomStatus;
use App\Livewire\Media\ManageMedia;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaUploadSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_host_can_upload_valid_image_with_mobile_variants(): void
    {
        [$host, $property] = $this->hostProperty();
        $photo = UploadedFile::fake()->image('entrance.jpg', 1200, 800)->size(500);

        Livewire::actingAs($host)
            ->test(ManageMedia::class, [
                'ownerType' => 'property',
                'ownerId' => $property->id,
                'collection' => 'gallery',
            ])
            ->set('photo', $photo)
            ->set('captionEn', 'Sunny entrance')
            ->set('captionRu', 'Светлый вход')
            ->call('savePhoto')
            ->assertHasNoErrors();

        $media = MediaItem::query()->firstOrFail();

        $this->assertSame(Property::class, $media->owner_type);
        $this->assertSame($property->id, $media->owner_id);
        $this->assertSame('gallery', $media->collection);
        $this->assertSame('entrance.jpg', $media->original_filename);
        $this->assertSame('Sunny entrance', $media->caption_en);
        $this->assertTrue($media->is_primary);
        $this->assertNotNull($media->thumb_path);
        $this->assertNotNull($media->mobile_path);
        $this->assertNotNull($media->full_path);

        Storage::disk('public')->assertExists($media->thumb_path);
        Storage::disk('public')->assertExists($media->mobile_path);
        Storage::disk('public')->assertExists($media->full_path);
    }

    public function test_media_upload_rejects_invalid_file(): void
    {
        [$host, $property] = $this->hostProperty();
        $file = UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf');

        Livewire::actingAs($host)
            ->test(ManageMedia::class, [
                'ownerType' => 'property',
                'ownerId' => $property->id,
                'collection' => 'gallery',
            ])
            ->set('photo', $file)
            ->call('savePhoto')
            ->assertHasErrors(['photo']);

        $this->assertDatabaseCount('media_items', 0);
    }

    public function test_host_can_delete_media_and_files(): void
    {
        [$host, $property] = $this->hostProperty();
        $media = $this->storeMedia($property, $host, 'delete-me.jpg');

        Livewire::actingAs($host)
            ->test(ManageMedia::class, [
                'ownerType' => 'property',
                'ownerId' => $property->id,
                'collection' => 'gallery',
            ])
            ->call('deleteMedia', $media->id)
            ->assertHasNoErrors();

        $this->assertModelMissing($media);
        Storage::disk('public')->assertMissing($media->thumb_path);
        Storage::disk('public')->assertMissing($media->mobile_path);
        Storage::disk('public')->assertMissing($media->full_path);
    }

    public function test_host_can_set_primary_media(): void
    {
        [$host, $property] = $this->hostProperty();
        $first = $this->storeMedia($property, $host, 'first.jpg');
        $second = $this->storeMedia($property, $host, 'second.jpg');

        Livewire::actingAs($host)
            ->test(ManageMedia::class, [
                'ownerType' => 'property',
                'ownerId' => $property->id,
                'collection' => 'gallery',
            ])
            ->call('setPrimary', $second->id)
            ->assertHasNoErrors();

        $this->assertFalse($first->refresh()->is_primary);
        $this->assertTrue($second->refresh()->is_primary);
    }

    public function test_listing_card_uses_primary_mobile_media(): void
    {
        [$host, $property] = $this->hostProperty(['city' => 'Vilnius', 'status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => RoomStatus::Active]);
        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'display_name' => 'Primary Media Bed',
                'status' => 'active',
            ]);

        $sleepingPlace->translations()->create([
            'locale' => 'en',
            'title' => 'Primary Media Bed',
            'summary' => 'Primary media summary',
            'description' => 'Primary media description',
        ]);

        $media = MediaItem::factory()
            ->for($property, 'mediable')
            ->for($host, 'owner')
            ->create([
                'owner_type' => Property::class,
                'owner_id' => $property->id,
                'collection' => 'gallery',
                'path' => 'properties/full.jpg',
                'thumb_path' => 'properties/thumb.jpg',
                'thumbnail_path' => 'properties/thumb.jpg',
                'mobile_path' => 'properties/mobile-primary.jpg',
                'full_path' => 'properties/full.jpg',
                'caption_en' => 'Primary property photo',
                'is_primary' => true,
                'is_cover' => true,
                'status' => 'active',
            ]);

        Storage::disk('public')->put($media->mobile_path, 'mobile-image');

        $this->get(route('search.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('Primary Media Bed')
            ->assertSee(Storage::disk('public')->url('properties/mobile-primary.jpg'), false)
            ->assertSee('Primary property photo');
    }

    /**
     * @param  array<string, mixed>  $propertyAttributes
     * @return array{0: User, 1: Property}
     */
    private function hostProperty(array $propertyAttributes = []): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'title' => 'Media test property',
                ...$propertyAttributes,
            ]);

        return [$host, $property];
    }

    private function storeMedia(Property $property, User $host, string $filename): MediaItem
    {
        return app(StoreMediaItemAction::class)->handle(
            owner: $property,
            file: UploadedFile::fake()->image($filename, 1000, 700)->size(300),
            user: $host,
            collection: 'gallery',
            captionEn: $filename,
        );
    }
}
