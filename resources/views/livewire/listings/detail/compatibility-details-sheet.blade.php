<div class="space-y-3">
    <flux:button type="button" variant="ghost" icon="information-circle" wire:click="show">
        {{ __('compatibility.actions.open_details') }}
    </flux:button>

    @if($open)
        <div class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] overflow-y-auto rounded-t-lg border border-zinc-200 bg-white p-4 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">{{ __('compatibility.details_title') }}</flux:heading>
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
                        <flux:button variant="primary" icon="adjustments-horizontal">{{ __('compatibility.suggestions.change_filter') }}</flux:button>
                        <flux:button variant="ghost" icon="chat-bubble-left-right">{{ __('compatibility.suggestions.ask_host') }}</flux:button>
                    </div>
                </div>
            @else
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.empty_summary') }}</flux:text>
            @endif
        </div>
    @endif
</div>
