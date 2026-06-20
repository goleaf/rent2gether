<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('listing_wizard.readiness.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('listing_wizard.publish_step.completion_score', ['score' => $readiness['score']]) }}
        </flux:text>
    </div>

    @if($readiness['blocking'])
        <div class="space-y-2">
            <div class="text-sm font-medium text-amber-800 dark:text-amber-200">{{ __('listing_wizard.readiness.blocking') }}</div>
            @foreach($readiness['blocking'] as $check)
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm dark:border-amber-900 dark:bg-amber-950/40">
                    {{ __($check['message_key']) }}
                </div>
            @endforeach
        </div>
    @endif

    @if($readiness['recommended'])
        <div class="space-y-2">
            <div class="text-sm font-medium">{{ __('listing_wizard.readiness.recommended') }}</div>
            @foreach($readiness['recommended'] as $check)
                <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                    {{ __($check['message_key']) }}
                </div>
            @endforeach
        </div>
    @endif

    @if(! $readiness['blocking'] && ! $readiness['recommended'])
        <div class="rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ __('listing_wizard.readiness.ready') }}
        </div>
    @endif
</flux:card>
