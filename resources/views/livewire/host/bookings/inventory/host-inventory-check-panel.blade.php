<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('inventory.panels.check_panel') }}</span>
            </span>
        </flux:heading>
        @forelse ($checks as $check)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ __('inventory.check_types.'.$check->check_type) }}</flux:text>
                <flux:badge color="zinc" icon="calendar-days">{{ __('inventory.check_statuses.'.$check->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.messages.panel_ready') }}</flux:text>
        @endforelse
    </flux:card>
</section>
