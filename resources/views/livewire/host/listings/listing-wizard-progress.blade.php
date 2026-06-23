<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_wizard.progress.title') }}</span>
            </span>
        </flux:heading>
    </div>

    <div class="flex items-center justify-between gap-3">
        <flux:text size="sm" class="font-medium">{{ __('listing_wizard.step_counter', ['current' => $current, 'total' => $total]) }}</flux:text>
        <flux:badge size="sm" icon="home-modern">{{ $progress['percentage'] }}%</flux:badge>
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
