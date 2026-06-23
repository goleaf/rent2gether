<flux:card class="space-y-4" data-detail-section="guest-hints">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_hints.important_to_know') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_hints.helper') }}</flux:text>
    </div>

    @if($hints)
        <flux:accordion transition>
            @foreach(collect($hints)->groupBy('category') as $category => $items)
                <flux:accordion.item :expanded="$loop->first">
                    <flux:accordion.heading>{{ __('guest_hints.categories.'.$category) }}</flux:accordion.heading>

                    <flux:accordion.content>
                        <div class="space-y-2">
                            @forelse($items as $hint)
                                <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                                    <div class="min-w-0">
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $hint['text'] }}</div>
                                        @if(! empty($hint['source']))
                                            <div class="mt-1 text-xs text-zinc-500">{{ __('guest_hints.source', ['source' => __('guest_hints.sources.'.$hint['source'])]) }}</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                            @endforelse
                        </div>
                    </flux:accordion.content>
                </flux:accordion.item>
            @endforeach
        </flux:accordion>
    @else
        <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
            {{ __('guest_hints.empty') }}
        </div>
    @endif
</flux:card>
