<flux:card class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:text size="sm" class="font-medium">{{ __('listing_wizard.step_counter', ['current' => $current, 'total' => $total]) }}</flux:text>
        <flux:badge size="sm">{{ $progress['percentage'] }}%</flux:badge>
    </div>

    <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $progress['percentage'] }}%"></div>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach($progress['steps'] as $step)
            <span class="shrink-0 rounded-full border px-3 py-1.5 text-xs {{ $progress['current'] === $step ? 'border-emerald-500 bg-emerald-50 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200' : 'border-zinc-200 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400' }}">
                {{ __('listing_wizard.steps.'.$step) }}
            </span>
        @endforeach
    </div>
</flux:card>
