<flux:callout color="sky" data-host-wizard-hints icon="information-circle">
    <flux:callout.heading icon="information-circle" icon:variant="mini">{{ __('host_hints.wizard_title') }}</flux:callout.heading>
    <flux:callout.text>{{ __('host_hints.wizard_helper') }}</flux:callout.text>

    @if($hints)
        <div class="mt-3 space-y-2">
            @forelse($hints as $hint)
                <livewire:host.hints.host-hint-card
                    :hint="$hint"
                    context="wizard"
                    :key="'host-wizard-hint-'.$step.'-'.$hint['id']"
                />
            @empty
            @endforelse
        </div>
    @else
        <flux:text size="sm" class="mt-3">{{ __('host_hints.empty') }}</flux:text>
    @endif
</flux:callout>
