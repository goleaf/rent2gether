<flux:card class="space-y-4">
    <div>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('property.completion.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('property.completion.helper') }}
        </flux:text>
    </div>

    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3">
            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('property.completion.progress') }}</span>
            <span class="text-lg font-semibold">{{ $completion['percentage'] }}%</span>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $completion['percentage'] }}%"></div>
        </div>
    </div>

    @if($completion['missing'])
        <div class="space-y-2">
            <flux:text size="sm" class="font-medium">{{ __('property.completion.missing_title') }}</flux:text>
            <ul class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                @forelse($completion['missing'] as $item)
                    <li>{{ $item }}</li>
                @empty
                @endforelse
            </ul>
        </div>
    @else
        <flux:callout color="emerald" icon="information-circle">
            <flux:callout.text>{{ __('property.completion.ready') }}</flux:callout.text>
        </flux:callout>
    @endif
</flux:card>
