<section class="space-y-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('compatibility.before_booking.title') }}</flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.before_booking.helper') }}</flux:text>
    </div>

    @error('compatibility')
        <flux:callout color="red" icon="exclamation-triangle">{{ $message }}</flux:callout>
    @enderror

    @if($result)
        <div class="flex items-center justify-between gap-3">
            <flux:text class="font-medium">{{ __('compatibility.fit_statuses.'.$result['fit_status']) }}</flux:text>
            <flux:badge>{{ __('compatibility.score', ['score' => $result['score']]) }}</flux:badge>
        </div>

        @foreach(array_slice(array_merge($result['blocking_reasons'], $result['warning_reasons']), 0, 3) as $reason)
            <p class="text-sm text-amber-700 dark:text-amber-300" wire:key="booking-compat-{{ $reason['key'] }}">{{ $reason['message'] }}</p>
        @endforeach

        <div class="grid gap-2">
            <flux:button type="button" variant="primary" icon="check" wire:click="continueAnyway">
                {{ __('compatibility.before_booking.continue_anyway') }}
            </flux:button>
            <flux:button type="button" variant="ghost" icon="magnifying-glass">
                {{ __('compatibility.before_booking.choose_another') }}
            </flux:button>
        </div>
    @else
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.empty_summary') }}</flux:text>
    @endif
</section>
