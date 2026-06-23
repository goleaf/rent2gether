<div class="space-y-4">
    <flux:card class="space-y-3">
        <div class="space-y-1">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="photo" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('media.manager.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('media.manager.helper') }}</flux:text>
        </div>

        @if ($statusMessage || session('media-status'))
            <flux:callout variant="success" :text="$statusMessage ?: session('media-status')"  icon="check-circle" />
        @endif

        <flux:file-upload
            wire:model="photo"
            :label="__('media.manager.file_label')"
            :description="__('media.manager.file_helper')"
            :error="$errors->first('photo')"
        >
            <flux:file-upload.dropzone
                :heading="__('media.manager.file_label')"
                :text="__('media.manager.file_helper')"
                with-progress
                inline
            />
        </flux:file-upload>

        @if($photo && str_starts_with((string) $photo->getMimeType(), 'image/'))
            <flux:file-item
                :heading="$photo->getClientOriginalName()"
                :image="$photo->temporaryUrl()"
                :size="$photo->getSize()"
            >
                <x-slot name="actions">
                    <flux:file-item.remove
                        wire:click="removePhoto"
                        :aria-label="__('media.manager.remove_file', ['name' => $photo->getClientOriginalName()])"
                    />
                </x-slot>
            </flux:file-item>
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach($this->contentLocales() as $locale)
                <flux:field wire:key="media-caption-{{ $locale['code'] }}">
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="photo" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('media.manager.caption', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="captions.{{ $locale['code'] }}" maxlength="160" icon="language" />
                    <flux:error name="captions.{{ $locale['code'] }}" />
                </flux:field>
            @endforeach
        </div>

        <flux:button type="button" variant="primary" class="w-full" wire:click="savePhoto" wire:loading.attr="disabled" wire:target="savePhoto,photo" icon="photo">
            <span wire:loading.remove wire:target="savePhoto">{{ __('media.manager.actions.save') }}</span>
            <span wire:loading wire:target="savePhoto">{{ __('media.manager.actions.saving') }}</span>
        </flux:button>

        <flux:callout variant="warning" icon="exclamation-triangle" :text="__('media.manager.warning')" />
    </flux:card>

    <div class="space-y-3">
        @forelse($this->mediaItems as $index => $item)
            <flux:card size="sm" class="flex gap-3" wire:key="media-item-{{ $item['id'] }}">
                <img
                    src="{{ $item['thumb_url'] }}"
                    alt="{{ $item['caption'] }}"
                    loading="lazy"
                    decoding="async"
                    width="96"
                    height="96"
                    class="h-24 w-24 shrink-0 rounded-md object-cover"
                />
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item['caption'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item['width'] && $item['height'] ? __('media.manager.dimensions', ['width' => $item['width'], 'height' => $item['height']]) : __('media.manager.optimized') }}
                            </p>
                        </div>
                        @if($item['is_primary'])
                            <flux:badge size="sm" color="green" icon="check-circle">{{ __('media.manager.primary') }}</flux:badge>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <flux:button type="button" size="xs" variant="ghost" wire:click="moveMedia({{ $item['id'] }}, 'up')" :disabled="$index === 0" icon="photo">
                            {{ __('media.manager.actions.up') }}
                        </flux:button>
                        <flux:button type="button" size="xs" variant="ghost" wire:click="moveMedia({{ $item['id'] }}, 'down')" :disabled="$index === count($this->mediaItems) - 1" icon="photo">
                            {{ __('media.manager.actions.down') }}
                        </flux:button>
                        @unless($item['is_primary'])
                            <flux:button type="button" size="xs" variant="ghost" wire:click="setPrimary({{ $item['id'] }})" icon="photo">
                                {{ __('media.manager.actions.primary') }}
                            </flux:button>
                        @endunless
                        <flux:button type="button" size="xs" variant="danger" wire:click="deleteMedia({{ $item['id'] }})" wire:confirm="{{ __('media.manager.delete_confirm') }}" icon="trash">
                            {{ __('media.manager.actions.delete') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        @empty
            <flux:callout variant="secondary" :text="__('media.manager.empty')"  icon="information-circle" />
        @endforelse
    </div>
</div>
