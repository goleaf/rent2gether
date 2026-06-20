@php
    $page = [
        'eyebrow' => __('shell.pages.guest.messages.eyebrow'),
        'title' => __('shell.pages.guest.messages.title'),
        'helper' => __('shell.pages.guest.messages.helper'),
        'action' => __('shell.pages.guest.messages.action'),
        'empty_title' => __('shell.pages.guest.messages.empty_title'),
        'empty_text' => __('shell.pages.guest.messages.empty_text'),
    ];
@endphp

<div class="mx-auto max-w-3xl space-y-5 px-4 py-4 pb-24 sm:px-6">
    <section class="space-y-2">
        <flux:badge color="emerald">{{ $page['eyebrow'] }}</flux:badge>
        <flux:heading size="xl" level="1">{{ $page['title'] }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">
            {{ $page['helper'] }}
        </flux:text>
    </section>

    <div class="space-y-3">
        @forelse($this->threads as $thread)
            @php
                $other = (int) $thread->guest_user_id === (int) auth()->id() ? $thread->host : $thread->guest;
                $lastMessage = $thread->messages->first();
                $threadType = $thread->type?->value ?? 'pre_booking';
                $placeTitle = $thread->sleepingPlace?->translations?->firstWhere('locale', app()->getLocale())?->title
                    ?: $thread->sleepingPlace?->translations?->firstWhere('locale', config('app.fallback_locale', 'en'))?->title
                    ?: $thread->sleepingPlace?->display_name;
            @endphp

            <a href="{{ route('messages.show', ['locale' => app()->getLocale(), 'thread' => $thread]) }}" wire:navigate class="block">
                <flux:card class="space-y-3 transition hover:bg-zinc-50 dark:hover:bg-zinc-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="user" class="size-5 text-zinc-500" />
                            </div>
                            <div class="min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:text class="font-medium text-zinc-950 dark:text-zinc-50">{{ $other?->name ?: __('messages.inbox.unknown_user') }}</flux:text>
                                    <flux:badge size="sm">{{ __('statuses.message_thread_type.'.$threadType) }}</flux:badge>
                                </div>
                                @if($placeTitle)
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $placeTitle }}</flux:text>
                                @endif
                                <flux:text size="sm" class="line-clamp-2 text-zinc-600 dark:text-zinc-300">
                                    {{ $lastMessage ? \Illuminate\Support\Str::limit($lastMessage->body, 90) : __('messages.inbox.no_messages') }}
                                </flux:text>
                            </div>
                        </div>

                        <div class="shrink-0 space-y-2 text-right">
                            @if($thread->unread_count > 0)
                                <flux:badge color="red" size="sm">{{ $thread->unread_count }}</flux:badge>
                            @endif
                            @if($lastMessage)
                                <flux:text size="sm" class="text-zinc-400">{{ $lastMessage->created_at->diffForHumans() }}</flux:text>
                            @endif
                        </div>
                    </div>
                </flux:card>
            </a>
        @empty
            <flux:card class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <flux:icon name="chat-bubble-left-right" class="size-5" />
                    </div>
                    <div class="space-y-1">
                        <flux:heading size="lg">{{ $page['empty_title'] }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                            {{ $page['empty_text'] }}
                        </flux:text>
                    </div>
                </div>

                <flux:button href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" wire:navigate variant="primary" class="w-full">
                    {{ $page['action'] }}
                </flux:button>
            </flux:card>
        @endforelse
    </div>
</div>
