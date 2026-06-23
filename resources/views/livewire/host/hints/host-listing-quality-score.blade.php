<flux:card class="space-y-4" data-host-listing-quality-score>
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_hints.quality_title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('host_hints.quality_score', ['score' => $readiness['score']]) }}
        </flux:text>
    </div>

    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3">
            <span class="text-sm font-medium">{{ $readiness['ready'] ? __('host_hints.quality_ready') : __('host_hints.quality_blocked') }}</span>
            <span class="text-sm font-semibold">{{ $readiness['score'] }}%</span>
        </div>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $readiness['score'] }}%"></div>
        </div>
    </div>

    @if($readiness['required'] || $readiness['recommended'])
        <div class="space-y-3">
            @if($readiness['required'])
                <div class="space-y-2">
                    <div class="text-sm font-medium">{{ __('host_hints.required') }}</div>
                    @foreach($readiness['required'] as $item)
                        <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                            {{ __('host_hints.quality.items.'.$item) }}
                        </div>
                    @endforeach
                </div>
            @endif

            @if($readiness['recommended'])
                <div class="space-y-2">
                    <div class="text-sm font-medium">{{ __('host_hints.recommended') }}</div>
                    @foreach($readiness['recommended'] as $item)
                        <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                            {{ __('host_hints.quality.items.'.$item) }}
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</flux:card>
