<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('inventory.panels.movement_timeline') }}</flux:heading>
        @forelse ($movements as $movement)
            <div class="space-y-1">
                <flux:text>{{ __('inventory.movement_types.'.$movement->movement_type) }}</flux:text>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $movement->moved_at?->toDateTimeString() }}</flux:text>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.messages.panel_ready') }}</flux:text>
        @endforelse
    </flux:card>
</section>
