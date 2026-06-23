<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="cube" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('inventory.panels.replacements') }}</span>
            </span>
        </flux:heading>
        @forelse ($replacements as $replacement)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ __('inventory.replacement_reasons.'.$replacement->replacement_reason) }}</flux:text>
                <flux:badge color="zinc" icon="user">{{ __('inventory.replacement_statuses.'.$replacement->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.messages.panel_ready') }}</flux:text>
        @endforelse
    </flux:card>
</section>
