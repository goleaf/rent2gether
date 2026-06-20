<flux:card class="space-y-4">
    <div>
        <flux:heading size="lg">{{ __('room.completion.title') }}</flux:heading>
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
                <flux:badge color="{{ $item['complete'] ? 'emerald' : 'amber' }}">
                    {{ $item['complete'] ? __('room.completion.complete') : __('room.completion.missing') }}
                </flux:badge>
            </div>
        @empty
        @endforelse
    </div>
</flux:card>
