<flux:card class="space-y-4" data-host-before-publish-checklist>
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="wrench-screwdriver" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_hints.before_publish') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host_hints.before_publish_helper') }}</flux:text>
    </div>

    @if($hints)
        @if($critical->isNotEmpty())
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900 dark:bg-amber-950/40">
                <div class="text-sm font-medium text-amber-950 dark:text-amber-100">{{ __('host_hints.critical') }}</div>
                <div class="mt-2 space-y-2">
                    @foreach($critical as $hint)
                        <livewire:host.hints.host-hint-card
                            :hint="$hint"
                            context="before_publish"
                            :key="'host-before-publish-critical-'.$hint['id']"
                        />
                    @endforeach
                </div>
            </div>
        @endif

        @if($recommended->isNotEmpty())
            <div class="space-y-2">
                <div class="text-sm font-medium">{{ __('host_hints.recommended') }}</div>
                @foreach($recommended as $hint)
                    <livewire:host.hints.host-hint-card
                        :hint="$hint"
                        context="before_publish"
                        :key="'host-before-publish-recommended-'.$hint['id']"
                    />
                @endforeach
            </div>
        @endif
    @else
        <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
            {{ __('host_hints.no_before_publish_hints') }}
        </div>
    @endif
</flux:card>
