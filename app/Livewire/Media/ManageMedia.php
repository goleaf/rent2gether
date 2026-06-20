<?php

namespace App\Livewire\Media;

use App\Actions\Media\DeleteMediaItemAction;
use App\Actions\Media\ReorderMediaItemsAction;
use App\Actions\Media\SetPrimaryMediaItemAction;
use App\Actions\Media\StoreMediaItemAction;
use App\Models\MediaItem;
use App\Services\Media\MediaOwnerResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageMedia extends Component
{
    use WithFileUploads;

    private MediaOwnerResolver $owners;

    public string $ownerType = '';

    public ?int $ownerId = null;

    public string $collection = 'gallery';

    public $photo = null;

    public string $captionEn = '';

    public string $captionRu = '';

    public int $maxItems = 12;

    public ?string $statusMessage = null;

    public function boot(MediaOwnerResolver $owners): void
    {
        $this->owners = $owners;
    }

    public function mount(string $ownerType, int $ownerId, string $collection = 'gallery', int $maxItems = 12): void
    {
        $this->ownerType = $ownerType;
        $this->ownerId = $ownerId;
        $this->collection = $collection;
        $this->maxItems = max(1, min(24, $maxItems));
        $this->statusMessage = session('media-status');

        $this->authorizedOwner();
    }

    public function updatedPhoto(): void
    {
        $this->statusMessage = null;
        $this->resetErrorBag('photo');
    }

    public function savePhoto(StoreMediaItemAction $store): void
    {
        $this->validate($this->rules(), attributes: $this->validationAttributes());

        $owner = $this->authorizedOwner();

        if ($owner->mediaItems()->where('collection', $this->collection)->active()->count() >= $this->maxItems) {
            $this->addError('photo', __('media.validation.max_items', ['count' => $this->maxItems]));

            return;
        }

        $store->handle(
            owner: $owner,
            file: $this->photo,
            user: auth()->user(),
            collection: $this->collection,
            captionEn: $this->blankToNull($this->captionEn),
            captionRu: $this->blankToNull($this->captionRu),
        );

        $this->reset(['photo', 'captionEn', 'captionRu']);
        unset($this->mediaItems);
        $this->statusMessage = __('media.flash.uploaded');
        session()->flash('media-status', $this->statusMessage);
    }

    public function deleteMedia(int $mediaId, DeleteMediaItemAction $delete): void
    {
        $mediaItem = $this->ownedMediaItem($mediaId);
        $delete->handle($mediaItem);
        unset($this->mediaItems);
        $this->statusMessage = __('media.flash.deleted');
        session()->flash('media-status', $this->statusMessage);
    }

    public function setPrimary(int $mediaId, SetPrimaryMediaItemAction $setPrimary): void
    {
        $mediaItem = $this->ownedMediaItem($mediaId);
        $setPrimary->handle($mediaItem);
        unset($this->mediaItems);
        $this->statusMessage = __('media.flash.primary_set');
        session()->flash('media-status', $this->statusMessage);
    }

    public function moveMedia(int $mediaId, string $direction, ReorderMediaItemsAction $reorder): void
    {
        if (! in_array($direction, ['up', 'down'], true)) {
            return;
        }

        $mediaItem = $this->ownedMediaItem($mediaId);
        $reorder->move($mediaItem, $direction);
        unset($this->mediaItems);
        $this->statusMessage = __('media.flash.reordered');
        session()->flash('media-status', $this->statusMessage);
    }

    /**
     * @return list<array{id:int,url:string,thumb_url:string,caption:string,is_primary:bool,sort_order:int,width:int|null,height:int|null}>
     */
    #[Computed]
    public function mediaItems(): array
    {
        return $this->authorizedOwner()
            ->mediaItems()
            ->select([
                'id',
                'mediable_type',
                'mediable_id',
                'collection',
                'disk',
                'path',
                'thumbnail_path',
                'thumb_path',
                'mobile_path',
                'full_path',
                'alt_text',
                'caption_en',
                'caption_ru',
                'sort_order',
                'is_primary',
                'is_cover',
                'status',
                'width',
                'height',
            ])
            ->where('collection', $this->collection)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (MediaItem $item): array => [
                'id' => $item->id,
                'url' => $item->imageUrl('mobile'),
                'thumb_url' => $item->imageUrl('thumb'),
                'caption' => $item->localizedCaption() ?: __('media.default_caption'),
                'is_primary' => (bool) ($item->is_primary || $item->is_cover),
                'sort_order' => (int) $item->sort_order,
                'width' => $item->width,
                'height' => $item->height,
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.media.manage-media');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'ownerType' => ['required', Rule::in(['property', 'room', 'sleeping_place', 'avatar', 'user', 'complaint', 'checkin', 'checkout', 'review'])],
            'ownerId' => ['required', 'integer', 'min:1'],
            'collection' => ['required', 'string', 'max:80'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=5000,max_height=5000'],
            'captionEn' => ['nullable', 'string', 'max:160'],
            'captionRu' => ['nullable', 'string', 'max:160'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('media.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }

    private function authorizedOwner(): Model
    {
        $owner = $this->owners->resolve($this->ownerType, (int) $this->ownerId);

        abort_unless(auth()->check(), 403);
        $this->owners->authorize($owner, auth()->user());

        return $owner;
    }

    private function ownedMediaItem(int $mediaId): MediaItem
    {
        $owner = $this->authorizedOwner();

        return $owner->mediaItems()
            ->where('collection', $this->collection)
            ->findOrFail($mediaId);
    }

    private function blankToNull(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
