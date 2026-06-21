<div>
    @if($suggestions)
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('booking_quotes.suggestions.title') }}</flux:heading>

            <div class="space-y-2">
                @foreach($suggestions as $suggestion)
                    <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                        <flux:text size="sm" class="font-medium">{{ $suggestion['message'] }}</flux:text>
                        @if($suggestion['check_in'] && $suggestion['check_out'])
                            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                                {{ __('booking_quotes.suggestions.range', [
                                    'check_in' => $suggestion['check_in'],
                                    'check_out' => $suggestion['check_out'],
                                    'nights' => $suggestion['nights'],
                                ]) }}
                            </flux:text>
                        @endif
                        @if($suggestion['price'])
                            <flux:badge size="sm">{{ $suggestion['price'] }}</flux:badge>
                        @endif
                    </div>
                @endforeach
            </div>
        </flux:card>
    @endif
</div>
