<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('inventory.panels.stock_alerts') }}</span>
            </span>
        </flux:heading>
        @forelse ($alerts as $alert)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ __('inventory.stock_alert_types.'.$alert->alert_type) }}</flux:text>
                <flux:badge color="amber" icon="exclamation-triangle">{{ __('inventory.stock_alert_statuses.'.$alert->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.messages.panel_ready') }}</flux:text>
        @endforelse
    </flux:card>
</section>
