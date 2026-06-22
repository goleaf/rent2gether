<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('inventory.panels.check_panel') }}</flux:heading>
        @forelse ($checks as $check)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ __('inventory.check_types.'.$check->check_type) }}</flux:text>
                <flux:badge color="zinc">{{ __('inventory.check_statuses.'.$check->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.messages.panel_ready') }}</flux:text>
        @endforelse
    </flux:card>
</section>
