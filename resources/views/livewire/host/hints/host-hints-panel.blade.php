<flux:card class="space-y-4" data-host-hints-panel>
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('host_hints.dashboard_title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host_hints.dashboard_helper') }}</flux:text>
    </div>

    @if($hints)
        <div class="space-y-3">
            @foreach(collect($hints)->groupBy('category') as $category => $items)
                <details class="group rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" @if($loop->first) open @endif>
                    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 text-sm font-medium">
                        <span>{{ __('host_hints.categories.'.$category) }}</span>
                        <span class="text-zinc-400 group-open:hidden">+</span>
                        <span class="hidden text-zinc-400 group-open:inline">-</span>
                    </summary>

                    <div class="mt-3 space-y-2">
                        @forelse($items as $hint)
                            <livewire:host.hints.host-hint-card
                                :hint="$hint"
                                context="dashboard"
                                :key="'host-dashboard-hint-'.$hint['id']"
                            />
                        @empty
                        @endforelse
                    </div>
                </details>
            @endforeach
        </div>
    @else
        <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
            {{ __('host_hints.empty') }}
        </div>
    @endif
</flux:card>
