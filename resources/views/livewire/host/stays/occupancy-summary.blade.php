<flux:card class="space-y-3">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ $title }}</span>
        </span>
    </flux:heading>

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
