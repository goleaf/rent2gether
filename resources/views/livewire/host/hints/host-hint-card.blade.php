<flux:card size="sm" class="flex items-start justify-between gap-3 text-sm">
    <div class="min-w-0 space-y-1">
        <div class="flex flex-wrap items-center gap-2">
            <flux:badge
                size="sm"
                color="{{ ($hint['importance'] ?? '') === 'critical' ? 'red' : (($hint['importance'] ?? '') === 'high' ? 'amber' : 'zinc') }}"
            >
                {{ __('host_hints.importance.'.($hint['importance'] ?? 'medium')) }}
            </flux:badge>
            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $hint['text'] ?? '' }}</span>
        </div>

        @if(! empty($hint['category']))
            <div class="text-xs text-zinc-500">{{ __('host_hints.categories.'.$hint['category']) }}</div>
        @endif
    </div>

    @if(! ($hint['critical_before_publish'] ?? false) || $context !== 'before_publish')
        <livewire:host.hints.dismiss-host-hint-button
            :hint-id="$hint['id']"
            :context="$context"
            :key="'host-dismiss-hint-'.$context.'-'.$hint['id']"
        />
    @endif
</flux:card>
