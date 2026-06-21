<flux:card class="space-y-3">
    <flux:heading size="md">{{ $title }}</flux:heading>

    <div class="grid grid-cols-2 gap-2">
        <div>
            <flux:text size="xs" class="text-zinc-500">{{ __('stays.fields.current_occupants_count') }}</flux:text>
            <flux:text>{{ $summary['current_occupants_count'] ?? 0 }}</flux:text>
        </div>
        <div>
            <flux:text size="xs" class="text-zinc-500">{{ __('stays.fields.free_sleeping_places_count') }}</flux:text>
            <flux:text>{{ $summary['free_sleeping_places_count'] ?? 0 }}</flux:text>
        </div>
    </div>
</flux:card>
