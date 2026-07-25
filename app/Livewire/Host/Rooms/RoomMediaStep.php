<?php

namespace App\Livewire\Host\Rooms;

use App\Actions\Media\DeleteMediaItemAction;
use App\Actions\Media\StoreVideoMediaItemAction;
use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\MediaItem;
use App\Models\Room;
use App\Models\User;
use App\Services\Localization\SupportedContentLocales;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class RoomMediaStep extends Component
{
    use HandlesRoomStep;
    use WithFileUploads;

    private const MAX_VIDEOS = 3;

    public $videoFile = null;

    /**
     * @var array<string, string>
     */
    public array $videoCaptions = [];

    public ?string $statusMessage = null;

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
        $this->videoCaptions = $this->emptyCaptions();
        $this->statusMessage = session('room-media-status');
    }

    public function updatedVideoFile(): void
    {
        $this->statusMessage = null;
        $this->resetErrorBag('videoFile');
    }

    public function removeVideoFile(): void
    {
        $this->videoFile?->delete();
        $this->videoFile = null;
        $this->resetErrorBag('videoFile');
    }

    public function saveVideo(StoreVideoMediaItemAction $store): void
    {
        $validated = $this->validate([
            'videoFile' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:20480'],
            'videoCaptions' => ['array'],
            ...collect($this->contentLocales())->mapWithKeys(fn (array $locale): array => [
                'videoCaptions.'.$locale['code'] => ['nullable', 'string', 'max:160'],
            ])->all(),
        ], attributes: $this->validationAttributes());

        $room = $this->room();
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        if ($this->videoItemsQuery($room)->count() >= self::MAX_VIDEOS) {
            $this->addError('videoFile', __('room.media.max_videos', ['count' => self::MAX_VIDEOS]));

            return;
        }

        $store->handle(
            owner: $room,
            file: $validated['videoFile'],
            user: $user,
            collection: 'video',
            captions: $validated['videoCaptions'] ?? [],
        );

        $this->videoFile = null;
        $this->videoCaptions = $this->emptyCaptions();
        unset($this->videoItems);
        $this->statusMessage = __('room.media.saved_video');
        session()->flash('room-media-status', $this->statusMessage);
    }

    public function deleteVideo(int $mediaId, DeleteMediaItemAction $delete): void
    {
        $mediaItem = $this->ownedVideoItem($mediaId);

        $delete->handle($mediaItem);
        unset($this->videoItems);
        $this->statusMessage = __('room.media.deleted_video');
        session()->flash('room-media-status', $this->statusMessage);
    }

    /**
     * @return list<array{id:int,url:string,caption:string,mime_type:string|null,size:int|null}>
     */
    #[Computed]
    public function videoItems(): array
    {
        $room = $this->room();

        return $this->videoItemsQuery($room)
            ->select([
                'id',
                'owner_type',
                'owner_id',
                'mediable_type',
                'mediable_id',
                'collection',
                'disk',
                'path',
                'mime_type',
                'mime',
                'size_bytes',
                'size',
                'alt_text',
                'sort_order',
                'status',
            ])
            ->with(['translations:id,media_item_id,locale,caption'])
            ->where('collection', 'video')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(self::MAX_VIDEOS)
            ->get()
            ->map(fn (MediaItem $item): array => [
                'id' => $item->id,
                'url' => Storage::disk($item->disk ?: 'public')->url($item->path),
                'caption' => $item->localizedCaption() ?: __('room.media.default_video_caption'),
                'mime_type' => $item->mime_type ?: $item->mime,
                'size' => $item->size_bytes ?: $item->size,
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-media-step');
    }

    /**
     * @return list<array{code:string,name:string}>
     */
    public function contentLocales(): array
    {
        $locales = app(SupportedContentLocales::class);

        return collect($locales->locales())
            ->map(fn (string $locale): array => [
                'code' => $locale,
                'name' => $locales->name($locale, app()->getLocale()),
            ])
            ->all();
    }

    private function ownedVideoItem(int $mediaId): MediaItem
    {
        return $this->videoItemsQuery($this->room())
            ->findOrFail($mediaId);
    }

    private function videoItemsQuery(Room $room): Builder
    {
        return MediaItem::query()
            ->where('owner_type', Room::class)
            ->where('owner_id', $room->id)
            ->where('collection', 'video')
            ->active();
    }

    /**
     * @return array<string, string>
     */
    private function emptyCaptions(): array
    {
        return collect(app(SupportedContentLocales::class)->locales())
            ->mapWithKeys(fn (string $locale): array => [$locale => ''])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = [
            'videoFile' => __('room.validation_attributes.video_file'),
        ];

        foreach ($this->contentLocales() as $locale) {
            $attributes['videoCaptions.'.$locale['code']] = __('room.validation_attributes.video_caption', [
                'language' => $locale['name'],
            ]);
        }

        return $attributes;
    }
}
