<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('inventory.panels.stock_alerts') }}</flux:heading>
        @forelse ($alerts as $alert)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ __('inventory.stock_alert_types.'.$alert->alert_type) }}</flux:text>
                <flux:badge color="amber">{{ __('inventory.stock_alert_statuses.'.$alert->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.messages.panel_ready') }}</flux:text>
        @endforelse
    </flux:card>
</section>
