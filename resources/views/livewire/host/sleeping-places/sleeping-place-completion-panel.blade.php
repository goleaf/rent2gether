<flux:card class="space-y-4">
    <div>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('sleeping_place.completion.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('sleeping_place.completion.helper') }}</flux:text>
    </div>

    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3">
            <span class="text-sm font-medium">{{ __('sleeping_place.completion.progress') }}</span>
            <span class="text-sm font-semibold">{{ $percentage }}%</span>
        </div>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $percentage }}%"></div>
        </div>
    </div>

    <div class="grid gap-2">
        @forelse($items as $item)
            <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                <span>{{ $item['label'] }}</span>
                <flux:badge color="{{ $item['complete'] ? 'emerald' : 'zinc' }}" icon="check-circle">
                    {{ $item['complete'] ? __('sleeping_place.completion.complete') : __('sleeping_place.completion.missing') }}
                </flux:badge>
            </div>
        @empty
            <flux:text class="text-zinc-500">{{ __('sleeping_place.completion.empty') }}</flux:text>
        @endforelse
    </div>
</flux:card>
