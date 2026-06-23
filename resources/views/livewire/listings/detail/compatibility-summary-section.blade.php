<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="scale" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('compatibility.summary_title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.summary_helper') }}</flux:text>
    </div>

    @if($result)
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ __('compatibility.score', ['score' => $result['score']]) }}</div>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.fit_statuses.'.$result['fit_status']) }}</flux:text>
            </div>
            <flux:badge color="{{ $result['fit_status'] === 'not_suitable' ? 'red' : ($result['fit_status'] === 'attention' || $result['fit_status'] === 'uncomfortable' ? 'yellow' : 'green') }}" icon="exclamation-triangle">
                {{ __('compatibility.badge_short', ['score' => $result['score']]) }}
            </flux:badge>
        </div>

        @if($result['positive_reasons'] !== [])
            <div class="space-y-1">
                <flux:text size="sm" class="font-medium">{{ __('compatibility.why_fits') }}</flux:text>
                @foreach(array_slice($result['positive_reasons'], 0, 3) as $reason)
                    <p class="text-sm text-zinc-700 dark:text-zinc-300" wire:key="compat-positive-{{ $reason['key'] }}">{{ $reason['message'] }}</p>
                @endforeach
            </div>
        @endif

        @if($result['warning_reasons'] !== [] || $result['blocking_reasons'] !== [])
            <div class="space-y-1">
                <flux:text size="sm" class="font-medium">{{ __('compatibility.pay_attention') }}</flux:text>
                @foreach(array_slice(array_merge($result['blocking_reasons'], $result['warning_reasons']), 0, 3) as $reason)
                    <p class="text-sm text-amber-700 dark:text-amber-300" wire:key="compat-warning-{{ $reason['key'] }}">{{ $reason['message'] }}</p>
                @endforeach
            </div>
        @endif
    @else
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.empty_summary') }}</flux:text>
    @endif
</flux:card>
