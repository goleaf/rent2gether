<div>
    <flux:button type="button" size="sm" variant="ghost" wire:click="open">
        {{ __('guest_hints.details') }}
    </flux:button>

    @if($open)
        <div class="fixed inset-0 z-40 bg-black/40" wire:click="close"></div>
        <section class="fixed inset-x-0 bottom-0 z-50 max-h-[80vh] overflow-y-auto rounded-t-lg bg-white p-4 shadow-xl dark:bg-zinc-950 sm:mx-auto sm:max-w-lg sm:rounded-lg">
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ $hint['text'] ?? __('guest_hints.details') }}</flux:heading>
                        @if(! empty($hint['category']))
                            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_hints.categories.'.$hint['category']) }}</flux:text>
                        @endif
                    </div>
                    <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="close" aria-label="{{ __('guest_hints.actions.close') }}" />
                </div>

                <div class="rounded-lg bg-zinc-50 px-3 py-3 text-sm text-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                    {{ __('guest_hints.details_helper') }}
                </div>

                @if(! empty($hint['source']))
                    <div class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('guest_hints.source', ['source' => __('guest_hints.sources.'.$hint['source'])]) }}
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>
