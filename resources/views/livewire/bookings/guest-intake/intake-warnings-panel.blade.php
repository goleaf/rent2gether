<div class="space-y-3">
    @if($blockingReasons !== [])
        <flux:callout color="red" icon="exclamation-triangle">
            <flux:callout.heading icon="exclamation-triangle" icon:variant="mini">{{ __('guest_intake.summary.blocking_title') }}</flux:callout.heading>
            <flux:callout.text>
                <ul class="list-inside list-disc space-y-1">
                    @foreach($blockingReasons as $reason)
                        <li>{{ $reason['message'] }}</li>
                    @endforeach
                </ul>
            </flux:callout.text>
        </flux:callout>
    @endif

    @if($warnings !== [])
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading icon="exclamation-triangle" icon:variant="mini">{{ __('guest_intake.summary.warning_title') }}</flux:callout.heading>
            <flux:callout.text>
                <ul class="list-inside list-disc space-y-1">
                    @foreach($warnings as $warning)
                        <li>{{ $warning['message'] }}</li>
                    @endforeach
                </ul>
            </flux:callout.text>
        </flux:callout>
    @endif
</div>
