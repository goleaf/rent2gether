<x-ui.page class="space-y-0 flex min-h-[calc(100vh-7rem)] flex-col">
    <section class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-start gap-3">
                <flux:button size="sm" variant="ghost" href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}" wire:navigate icon="arrow-left">
                    {{ __('messages.thread.back') }}
                </flux:button>
                <div class="min-w-0 space-y-1">
                    <flux:heading size="lg" level="1">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="chat-bubble-left-right" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $otherUser?->name ?: __('messages.inbox.unknown_user') }}</span>
                        </span>
                    </flux:heading>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:badge size="sm" icon="check-circle">{{ __('statuses.message_thread_type.'.$threadType) }}</flux:badge>
                        @if($placeTitle)
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $placeTitle }}</flux:text>
                        @endif
                    </div>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                        {{ __('messages.thread.address_note') }}
                    </flux:text>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-4 flex-1 space-y-3 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950" wire:poll.visible.10s>
        @forelse($messageCards as $card)
            <div class="{{ $card['mine'] ? 'ml-auto' : 'mr-auto' }} max-w-[88%] space-y-1">
                @if($card['message']->is_system_message || $card['message']->system_message)
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $card['message']->body }}</flux:text>
                    </div>
                @else
                    <div class="{{ $card['mine'] ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-950 dark:bg-zinc-800 dark:text-zinc-50' }} rounded-xl px-3 py-2">
                        @if($card['message']->is_important || $card['message']->important)
                            <div class="mb-1 text-xs font-medium opacity-80">{{ __('messages.thread.important') }}</div>
                        @endif
                        @if($card['message']->body !== '')
                            <p class="whitespace-pre-line text-sm">{{ $card['message']->body }}</p>
                        @endif
                        @if($card['attachments'] !== [])
                            <div class="mt-2 space-y-1">
                                @foreach($card['attachments'] as $attachment)
                                    @if(($attachment['type'] ?? null) === 'image')
                                        <a
                                            href="{{ Storage::disk('public')->url($attachment['full_path'] ?? $attachment['path']) }}"
                                            target="_blank"
                                            class="block w-32 overflow-hidden rounded-lg bg-white/20"
                                        >
                                            <img
                                                src="{{ Storage::disk('public')->url($attachment['thumbnail_path'] ?? $attachment['path']) }}"
                                                alt="{{ $attachment['original_name'] ?? __('messages.thread.attachment') }}"
                                                width="128"
                                                height="96"
                                                class="h-24 w-32 object-cover"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                            <span class="block truncate px-2 py-1 text-xs underline">
                                                {{ $attachment['original_name'] ?? __('messages.thread.attachment') }}
                                            </span>
                                        </a>
                                    @else
                                        <a
                                            href="{{ asset('storage/'.$attachment['path']) }}"
                                            target="_blank"
                                            class="block rounded-lg bg-white/20 px-2 py-1 text-xs underline"
                                        >
                                            {{ $attachment['original_name'] ?? __('messages.thread.attachment') }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <flux:text size="sm" class="{{ $card['mine'] ? 'text-right' : '' }} text-zinc-400">
                        {{ $card['message']->created_at->format('H:i') }}
                        @if($card['mine'] && $card['message']->read_at)
                            - {{ __('messages.thread.read') }}
                        @endif
                    </flux:text>
                @endif
            </div>
        @empty
            <div class="flex h-full min-h-48 items-center justify-center">
                <flux:text class="text-center text-zinc-500 dark:text-zinc-400">{{ __('messages.thread.empty') }}</flux:text>
            </div>
        @endforelse
    </section>

    <section class="mt-4 space-y-3 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95">
        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach($this->quickTemplates as $key => $template)
                <flux:button type="button" size="sm" variant="ghost" wire:click="applyTemplate('{{ $key }}')" class="shrink-0" icon="chat-bubble-left-right">
                    {{ $template }}
                </flux:button>
            @endforeach
        </div>

        @error('body')
            <flux:callout color="amber" icon="exclamation-triangle">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        <form wire:submit="send" class="space-y-3">
            <flux:field>
                <flux:label>{{ __('messages.thread.fields.body') }}</flux:label>
                <flux:textarea rows="3" wire:model="body" placeholder="{{ __('messages.thread.placeholders.body') }}" />
                <flux:error name="body" />
            </flux:field>

            <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                <div class="space-y-2">
                    <flux:file-upload
                        wire:model="uploads"
                        multiple
                        :label="__('messages.thread.fields.attachments')"
                        :description="__('messages.thread.attachments_helper')"
                        :error="$errors->first('uploads')"
                    >
                        <flux:file-upload.dropzone
                            :heading="__('messages.thread.fields.attachments')"
                            :text="__('messages.thread.attachments_helper')"
                            with-progress
                            inline
                        />
                    </flux:file-upload>
                    <flux:error name="uploads.*" />
                </div>

                <div class="flex items-end">
                    <flux:checkbox wire:model.change="important" label="{{ __('messages.thread.fields.important') }}" />
                </div>
            </div>

            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="paper-airplane">
                <span wire:loading.remove wire:target="send">{{ __('messages.thread.actions.send') }}</span>
                <span wire:loading wire:target="send">{{ __('messages.thread.actions.sending') }}</span>
            </flux:button>
        </form>
    </section>
</x-ui.page>
