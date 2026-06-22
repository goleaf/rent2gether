<div class="mx-auto max-w-3xl space-y-4 px-4 py-4 pb-24 sm:px-6">
    <header class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <flux:heading size="xl" level="1">{{ __('notifications.title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('notifications.messages.center_helper') }}</flux:text>
            </div>

            <div class="flex shrink-0 gap-2">
                <flux:badge color="emerald">{{ $unreadCount }}</flux:badge>
                @if($urgentUnreadCount > 0)
                    <flux:badge color="red">{{ $urgentUnreadCount }}</flux:badge>
                @endif
            </div>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach(['all', 'unread', 'urgent', 'booking', 'message'] as $filter)
                <flux:button size="sm" :variant="$filter === $this->filter ? 'primary' : 'ghost'" wire:click="setFilter('{{ $filter }}')">
                    {{ __('notifications.filters.'.$filter) }}
                </flux:button>
            @endforeach
        </div>

        @if($unreadCount > 0)
            <flux:button size="sm" variant="ghost" wire:click="markAllRead" class="data-loading:opacity-70">
                {{ __('notifications.actions.mark_all_read') }}
            </flux:button>
        @endif
    </header>

    <section class="space-y-3">
        @forelse($notifications as $notification)
            <flux:card @class([
                'space-y-3',
                'border-red-200 bg-red-50/70 dark:border-red-400/20 dark:bg-red-400/10' => $notification->is_urgent || $notification->is_critical,
                'border-emerald-200 bg-emerald-50/60 dark:border-emerald-400/20 dark:bg-emerald-400/10' => $notification->isUnread() && ! $notification->is_urgent && ! $notification->is_critical,
            ])>
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:badge size="sm">{{ __('notifications.categories.'.$notification->notification_category) }}</flux:badge>
                        <flux:badge size="sm" :color="$notification->is_critical ? 'red' : ($notification->is_urgent ? 'amber' : 'zinc')">
                            {{ __('notifications.priorities.'.$notification->priority) }}
                        </flux:badge>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $notification->created_at?->diffForHumans() }}</flux:text>
                    </div>

                    <flux:heading size="lg">{{ $notification->title() }}</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $notification->body() }}</flux:text>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    @if($notification->action_url)
                        <flux:button href="{{ $notification->action_url }}" wire:navigate variant="primary" class="w-full sm:w-auto">
                            {{ __('notifications.actions.'.($notification->action_type ?: 'open')) }}
                        </flux:button>
                    @endif

                    @if($notification->isUnread())
                        <flux:button type="button" wire:click="markRead('{{ $notification->id }}')" variant="ghost" class="w-full sm:w-auto">
                            {{ __('notifications.actions.mark_read') }}
                        </flux:button>
                    @endif

                    <flux:button type="button" wire:click="dismiss('{{ $notification->id }}')" variant="ghost" class="w-full sm:w-auto">
                        {{ __('notifications.actions.dismiss') }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-center dark:border-zinc-800 dark:bg-zinc-950">
                <flux:text>{{ __('notifications.empty_states.center') }}</flux:text>
            </div>
        @endforelse
    </section>
</div>
