<section class="space-y-4">
    <flux:card class="space-y-2">
        <flux:badge color="zinc" icon="user">{{ __('host_bulk.title') }}</flux:badge>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_bulk.sections.'.$section) }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('host_bulk.helpers.'.$section) }}
        </flux:text>
    </flux:card>

    <flux:heading size="base">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('host_bulk.choose_action') }}</span>
        </span>
    </flux:heading>

    <div class="grid gap-2 sm:grid-cols-2">
        @foreach (['change_price', 'open_dates', 'close_dates', 'message_guests'] as $action)
            <flux:card class="space-y-1 p-4">
                <flux:text size="sm" class="font-medium">{{ __('host_bulk.actions.'.$action) }}</flux:text>
                <flux:text size="xs" class="text-zinc-500">{{ __('host_bulk.messages.preview_before_apply') }}</flux:text>
            </flux:card>
        @endforeach
    </div>

    <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white/95 p-4 dark:border-zinc-800 dark:bg-zinc-950/95">
        <div class="flex gap-2">
            <flux:button variant="ghost" class="flex-1" wire:loading.attr="disabled" icon="check">
                {{ __('host_bulk.confirm') }}
            </flux:button>
            <flux:button variant="primary" class="flex-1" wire:loading.attr="disabled" icon="check">
                {{ __('host_bulk.actions.apply') }}
            </flux:button>
        </div>
    </div>
</section>
