<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('inventory.panels.replacements') }}</flux:heading>
        @forelse ($replacements as $replacement)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ __('inventory.replacement_reasons.'.$replacement->replacement_reason) }}</flux:text>
                <flux:badge color="zinc">{{ __('inventory.replacement_statuses.'.$replacement->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.messages.panel_ready') }}</flux:text>
        @endforelse
    </flux:card>
</section>
