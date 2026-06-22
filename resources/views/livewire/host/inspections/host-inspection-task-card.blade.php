<section>
    <flux:card class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <flux:heading size="base">{{ __('inspections.title') }}</flux:heading>
            <flux:badge color="zinc">{{ __('inspections.statuses.'.($inspection?->status ?? 'scheduled')) }}</flux:badge>
        </div>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('inspections.types.'.($inspection?->inspection_type ?? 'manual')) }}
        </flux:text>
    </flux:card>
</section>
