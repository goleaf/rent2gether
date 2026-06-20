<div class="rounded-lg border border-sky-200 bg-sky-50 p-3 dark:border-sky-900 dark:bg-sky-950/40" data-host-wizard-hints>
    <div class="space-y-1">
        <div class="text-sm font-medium text-sky-950 dark:text-sky-100">{{ __('host_hints.wizard_title') }}</div>
        <div class="text-xs text-sky-800 dark:text-sky-200">{{ __('host_hints.wizard_helper') }}</div>
    </div>

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
        <div class="mt-3 text-sm text-sky-800 dark:text-sky-200">{{ __('host_hints.empty') }}</div>
    @endif
</div>
