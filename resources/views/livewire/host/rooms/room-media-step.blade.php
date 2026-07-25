<div class="space-y-5">
    <div>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="photo" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('room.steps.media.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.media.helper') }}</flux:text>
    </div>

    @if($statusMessage || session('room-media-status'))
        <flux:callout variant="success" :text="$statusMessage ?: session('room-media-status')" icon="check-circle" />
    @endif

    <livewire:media.manage-media
        owner-type="room"
        :owner-id="$roomId"
        collection="gallery"
        :max-items="12"
        :key="'room-gallery-'.$roomId"
    />

    <flux:card class="space-y-4">
        <div>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="video-camera" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('room.media.videos_title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.media.videos_helper') }}</flux:text>
        </div>

        <form wire:submit="saveVideo" class="space-y-4">
            <flux:file-upload
                wire:model="videoFile"
                :label="__('room.media.video_file_label')"
                :description="__('room.media.video_file_helper')"
                :error="$errors->first('videoFile')"
                :disabled="count($this->videoItems) >= 3"
            >
                <flux:file-upload.dropzone
                    :heading="__('room.media.video_file_label')"
                    :text="__('room.media.video_file_helper')"
                    icon="video-camera"
                    with-progress
                    inline
                />
            </flux:file-upload>

            @if($videoFile)
                <flux:file-item
                    :heading="$videoFile->getClientOriginalName()"
                    :size="$videoFile->getSize()"
                    icon="video-camera"
                >
                    <x-slot name="actions">
                        <flux:file-item.remove
                            wire:click="removeVideoFile"
                            :aria-label="__('room.media.remove_video', ['name' => $videoFile->getClientOriginalName()])"
                        />
                    </x-slot>
                </flux:file-item>
            @endif

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($this->contentLocales() as $locale)
                    <flux:field wire:key="room-video-caption-{{ $locale['code'] }}">
                        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('room.media.video_caption', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                        <flux:input wire:model.blur="videoCaptions.{{ $locale['code'] }}" maxlength="160" icon="language" />
                        <flux:error name="videoCaptions.{{ $locale['code'] }}" />
                    </flux:field>
                @endforeach
            </div>

            <flux:button type="submit" variant="primary" class="w-full sm:w-auto" icon="video-camera">
                <span wire:loading.remove wire:target="saveVideo">{{ __('room.media.actions.save_video') }}</span>
                <span wire:loading wire:target="saveVideo">{{ __('room.media.actions.saving_video') }}</span>
            </flux:button>
        </form>

        <flux:callout variant="warning" icon="exclamation-triangle" :text="__('room.media.video_warning')" />

        <div class="space-y-3">
            @forelse($this->videoItems as $video)
                <div class="space-y-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="room-video-{{ $video['id'] }}">
                    <video controls preload="metadata" class="aspect-video w-full rounded-md bg-black">
                        <source src="{{ $video['url'] }}" type="{{ $video['mime_type'] }}">
                        {{ __('room.media.browser_not_supported') }}
                    </video>

                    <div class="flex items-start justify-between gap-3">
                        <flux:text size="sm" class="min-w-0 text-zinc-700 dark:text-zinc-300">{{ $video['caption'] }}</flux:text>
                        <flux:button
                            type="button"
                            size="xs"
                            variant="danger"
                            wire:click="deleteVideo({{ $video['id'] }})"
                            wire:confirm="{{ __('room.media.delete_video_confirm') }}"
                            icon="trash"
                        >
                            {{ __('room.media.actions.delete_video') }}
                        </flux:button>
                    </div>
                </div>
            @empty
                <flux:callout variant="secondary" icon="information-circle" :text="__('room.media.empty_videos')" />
            @endforelse
        </div>
    </flux:card>
</div>
