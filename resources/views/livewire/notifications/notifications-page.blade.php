<x-ui.page>
    <section class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-2">
                <flux:badge color="emerald" icon="check-circle">{{ __('notifications.page.eyebrow') }}</flux:badge>
                <flux:heading size="xl" level="1">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="bell" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('notifications.page.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                    {{ __('notifications.page.helper') }}
                </flux:text>
            </div>

            @if($this->unreadCount > 0)
                <flux:button size="sm" variant="ghost" wire:click="markAllAsRead" class="shrink-0 data-loading:opacity-70" icon="bell">
                    <span wire:loading.remove wire:target="markAllAsRead">{{ __('notifications.actions.mark_all_read') }}</span>
                    <span wire:loading wire:target="markAllAsRead">{{ __('notifications.actions.saving') }}</span>
                </flux:button>
            @endif
        </div>
    </section>

    <section class="space-y-3">
        @forelse($this->items as $notification)
            <flux:card @class([
                'space-y-3',
                'border-emerald-200 bg-emerald-50/60 dark:border-emerald-400/20 dark:bg-emerald-400/10' => $notification->isUnread(),
            ])>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($notification->isUnread())
                                <flux:badge size="sm" color="emerald" icon="check-circle">{{ __('notifications.status.unread') }}</flux:badge>
                            @else
                                <flux:badge size="sm" icon="check-circle">{{ __('notifications.status.read') }}</flux:badge>
                            @endif
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </flux:text>
                        </div>

                        <flux:heading size="lg">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="bell" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ $notification->title() }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                            {{ $notification->body() }}
                        </flux:text>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    @if($notification->action_url)
                        <flux:button href="{{ $notification->action_url }}" wire:navigate variant="primary" class="w-full sm:w-auto" icon="bell">
                            {{ __('notifications.actions.open') }}
                        </flux:button>
                    @endif

                    @if($notification->isUnread())
                        <flux:button type="button" wire:click="markAsRead('{{ $notification->id }}')" variant="ghost" class="w-full data-loading:opacity-70 sm:w-auto" icon="bell">
                            {{ __('notifications.actions.mark_read') }}
                        </flux:button>
                    @endif
                </div>
            </flux:card>
        @empty
            <flux:card class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <flux:icon name="bell" class="size-5" />
                    </div>
                    <div class="space-y-1">
                        <flux:heading size="lg">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="bell" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('notifications.page.empty_title') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                            {{ __('notifications.page.empty_text') }}
                        </flux:text>
                    </div>
                </div>

                <flux:button href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" wire:navigate variant="primary" class="w-full" icon="magnifying-glass">
                    {{ __('notifications.page.empty_action') }}
                </flux:button>
            </flux:card>
        @endforelse
    </section>
</x-ui.page>
