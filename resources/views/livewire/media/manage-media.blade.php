<div class="space-y-4">
    <div class="rounded-lg border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="space-y-3" x-data="{ uploading: false, progress: 0 }"
            x-on:livewire-upload-start="uploading = true"
            x-on:livewire-upload-finish="uploading = false"
            x-on:livewire-upload-cancel="uploading = false"
            x-on:livewire-upload-error="uploading = false"
            x-on:livewire-upload-progress="progress = $event.detail.progress"
        >
            <div class="space-y-1">
                <flux:heading size="sm">{{ __('media.manager.title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('media.manager.helper') }}</flux:text>
            </div>

            @if ($statusMessage || session('media-status'))
                <div class="rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-900 dark:bg-emerald-400/10 dark:text-emerald-100">
                    {{ $statusMessage ?: session('media-status') }}
                </div>
            @endif

            <flux:field>
                <flux:label>{{ __('media.manager.file_label') }}</flux:label>
                <flux:input type="file" accept="image/jpeg,image/png,image/webp" wire:model="photo" />
                <flux:description>{{ __('media.manager.file_helper') }}</flux:description>
                <flux:error name="photo" />
            </flux:field>

            <div x-cloak x-show="uploading" class="space-y-1">
                <div class="flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                    <span>{{ __('media.manager.uploading') }}</span>
                    <span x-text="progress + '%'"></span>
                </div>
                <flux:progress x-bind:value="progress" />
            </div>

            @if($photo && str_starts_with((string) $photo->getMimeType(), 'image/'))
                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <img src="{{ $photo->temporaryUrl() }}" alt="{{ __('media.manager.preview_alt') }}" class="h-40 w-full object-cover" />
                </div>
            @endif

            <div class="grid gap-3 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('media.manager.caption_en') }}</flux:label>
                    <flux:input wire:model.blur="captionEn" maxlength="160" />
                    <flux:error name="captionEn" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('media.manager.caption_ru') }}</flux:label>
                    <flux:input wire:model.blur="captionRu" maxlength="160" />
                    <flux:error name="captionRu" />
                </flux:field>
            </div>

            <flux:button type="button" variant="primary" class="w-full" wire:click="savePhoto" wire:loading.attr="disabled" wire:target="savePhoto,photo">
                <span wire:loading.remove wire:target="savePhoto">{{ __('media.manager.actions.save') }}</span>
                <span wire:loading wire:target="savePhoto">{{ __('media.manager.actions.saving') }}</span>
            </flux:button>

            <div class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-950 dark:bg-amber-400/10 dark:text-amber-100">
                {{ __('media.manager.warning') }}
            </div>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($this->mediaItems as $index => $item)
            <div class="flex gap-3 rounded-lg border border-zinc-200 bg-white p-2 dark:border-zinc-700 dark:bg-zinc-900" wire:key="media-item-{{ $item['id'] }}">
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
                            <flux:badge size="sm" color="green">{{ __('media.manager.primary') }}</flux:badge>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <flux:button type="button" size="xs" variant="ghost" wire:click="moveMedia({{ $item['id'] }}, 'up')" :disabled="$index === 0">
                            {{ __('media.manager.actions.up') }}
                        </flux:button>
                        <flux:button type="button" size="xs" variant="ghost" wire:click="moveMedia({{ $item['id'] }}, 'down')" :disabled="$index === count($this->mediaItems) - 1">
                            {{ __('media.manager.actions.down') }}
                        </flux:button>
                        @unless($item['is_primary'])
                            <flux:button type="button" size="xs" variant="ghost" wire:click="setPrimary({{ $item['id'] }})">
                                {{ __('media.manager.actions.primary') }}
                            </flux:button>
                        @endunless
                        <flux:button type="button" size="xs" variant="danger" wire:click="deleteMedia({{ $item['id'] }})" wire:confirm="{{ __('media.manager.delete_confirm') }}">
                            {{ __('media.manager.actions.delete') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-5 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('media.manager.empty') }}
            </div>
        @endforelse
    </div>
</div>
