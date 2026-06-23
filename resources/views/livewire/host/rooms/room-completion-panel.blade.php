<flux:card class="space-y-4">
    <div>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('room.completion.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.completion.helper') }}</flux:text>
    </div>

    <div class="rounded-lg bg-zinc-50 px-3 py-3 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3 text-sm">
            <span class="font-medium">{{ __('room.completion.progress') }}</span>
            <span>{{ $completion['percentage'] }}%</span>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $completion['percentage'] }}%"></div>
        </div>
    </div>

    <div class="grid gap-2">
        @forelse($completion['items'] as $item)
            <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                <span>{{ $item['label'] }}</span>
                <flux:badge color="{{ $item['complete'] ? 'emerald' : 'amber' }}" icon="exclamation-triangle">
                    {{ $item['complete'] ? __('room.completion.complete') : __('room.completion.missing') }}
                </flux:badge>
            </div>
        @empty
        @endforelse
    </div>
</flux:card>
