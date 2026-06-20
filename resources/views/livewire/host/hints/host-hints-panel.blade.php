<flux:card class="space-y-4" data-host-hints-panel>
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('host_hints.dashboard_title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host_hints.dashboard_helper') }}</flux:text>
    </div>

    @if($hints)
        <flux:accordion transition>
            @foreach(collect($hints)->groupBy('category') as $category => $items)
                <flux:accordion.item :expanded="$loop->first">
                    <flux:accordion.heading>{{ __('host_hints.categories.'.$category) }}</flux:accordion.heading>

                    <flux:accordion.content>
                        <div class="space-y-2">
                            @forelse($items as $hint)
                                <livewire:host.hints.host-hint-card
                                    :hint="$hint"
                                    context="dashboard"
                                    :key="'host-dashboard-hint-'.$hint['id']"
                                />
                            @empty
                            @endforelse
                        </div>
                    </flux:accordion.content>
                </flux:accordion.item>
            @endforeach
        </flux:accordion>
    @else
        <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
            {{ __('host_hints.empty') }}
        </div>
    @endif
</flux:card>
