<flux:card class="space-y-4" data-detail-section="guest-hints">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('guest_hints.important_to_know') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_hints.helper') }}</flux:text>
    </div>

    @if($hints)
        <div class="space-y-2">
            @foreach(collect($hints)->groupBy('category') as $category => $items)
                <details class="group rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" @if($loop->first) open @endif>
                    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 text-sm font-medium">
                        <span>{{ __('guest_hints.categories.'.$category) }}</span>
                        <span class="text-zinc-400 group-open:hidden">+</span>
                        <span class="hidden text-zinc-400 group-open:inline">-</span>
                    </summary>

                    <div class="mt-3 space-y-2">
                        @forelse($items as $hint)
                            <div class="flex items-start justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                                <div class="min-w-0">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $hint['text'] }}</div>
                                    @if(! empty($hint['source']))
                                        <div class="mt-1 text-xs text-zinc-500">{{ __('guest_hints.source', ['source' => __('guest_hints.sources.'.$hint['source'])]) }}</div>
                                    @endif
                                </div>

                                @if($hint['dismissible'])
                                    <livewire:hints.dismiss-hint-button
                                        :hint-key="$hint['key']"
                                        :sleeping-place-id="$sleepingPlaceId"
                                        context="detail"
                                        :critical="$hint['critical_before_booking']"
                                        :key="'hint-dismiss-detail-'.$hint['key'].'-'.$sleepingPlaceId"
                                    />
                                @endif
                            </div>
                        @empty
                        @endforelse
                    </div>
                </details>
            @endforeach
        </div>
    @else
        <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
            {{ __('guest_hints.empty') }}
        </div>
    @endif
</flux:card>
