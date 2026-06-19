<div class="max-w-4xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('search.waitlist.title') }}</flux:heading>

    <div class="space-y-4">
        @forelse($this->entries as $entry)
            <flux:card class="flex items-center justify-between">
                <div class="space-y-1">
                    <a href="{{ route('beds.show', ['locale' => app()->getLocale(), 'bed' => $entry->bed]) }}">
                        <flux:text class="font-medium hover:text-blue-600 transition">{{ $entry->bed->title }}</flux:text>
                    </a>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $entry->bed->room->property->city }} &middot;
                        {{ $entry->desired_check_in }} - {{ $entry->desired_check_out }}
                        @if($entry->max_price) &middot; {{ __('search.waitlist.maximum') }}: &euro;{{ number_format($entry->max_price, 2) }}@endif
                    </flux:text>
                    @if($entry->auto_request)
                        <flux:badge size="sm" color="blue">{{ __('search.waitlist.auto_request') }}</flux:badge>
                    @endif
                </div>
                <flux:button size="sm" variant="ghost" wire:click="cancel({{ $entry->id }})" wire:confirm="{{ __('search.waitlist.cancel_confirmation') }}" icon="x-mark" />
            </flux:card>
        @empty
            <flux:card>
                <flux:text class="text-center text-zinc-500 py-8">{{ __('search.waitlist.empty') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</div>
