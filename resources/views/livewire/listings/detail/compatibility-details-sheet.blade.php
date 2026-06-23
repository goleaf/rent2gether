<div class="space-y-3">
    <flux:button type="button" variant="ghost" icon="eye" wire:click="show">
        {{ __('compatibility.actions.open_details') }}
    </flux:button>

    @if ($open)
        <flux:modal wire:model="open" class="w-full max-w-[32rem]">
            <div class="max-h-[78vh] space-y-4 overflow-y-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="scale" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('compatibility.details_title') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.details_helper') }}</flux:text>
                    </div>

                    <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="close" aria-label="{{ __('compatibility.actions.close_details') }}" />
                </div>

                @if($result)
                    <div class="space-y-4">
                        @foreach(['blocking_reasons', 'warning_reasons', 'positive_reasons'] as $group)
                            @if($result[$group] !== [])
                                <div class="space-y-2">
                                    <flux:text size="sm" class="font-medium">{{ __('compatibility.groups.'.$group) }}</flux:text>
                                    @foreach($result[$group] as $reason)
                                        <p class="rounded-lg bg-zinc-50 p-3 text-sm text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300" wire:key="details-{{ $group }}-{{ $reason['key'] }}">
                                            {{ $reason['message'] }}
                                        </p>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach

                        <div class="grid gap-2">
                            <flux:button variant="primary" icon="funnel">{{ __('compatibility.suggestions.change_filter') }}</flux:button>
                            <flux:button variant="ghost" icon="check">{{ __('compatibility.suggestions.ask_host') }}</flux:button>
                        </div>
                    </div>
                @else
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.empty_summary') }}</flux:text>
                @endif
            </div>
        </flux:modal>
    @endif
</div>
