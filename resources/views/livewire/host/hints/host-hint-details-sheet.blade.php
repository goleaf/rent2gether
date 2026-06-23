<div data-host-hint-details>
    <flux:button size="sm" variant="ghost" wire:click="open" icon="eye">{{ __('host_hints.details') }}</flux:button>

    @if($open)
        <div class="fixed inset-0 z-50 flex items-end bg-black/40 p-3">
            <div class="w-full rounded-t-xl bg-white p-4 shadow-xl dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <flux:heading size="lg">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_hints.details') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $hint['text'] ?? __('host_hints.details_helper') }}</flux:text>
                    </div>
                    <flux:button size="sm" variant="ghost" wire:click="close" icon="x-mark">{{ __('host_hints.actions.close') }}</flux:button>
                </div>

                @if(! empty($hint['category']))
                    <div class="mt-4 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                        {{ __('host_hints.categories.'.$hint['category']) }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
