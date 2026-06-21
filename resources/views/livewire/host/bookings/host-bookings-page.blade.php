<div class="mx-auto w-full max-w-4xl space-y-4 px-4 py-4">
    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('bookings.host.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.host.subtitle') }}</flux:text>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach ($filters as $filterKey => $label)
                <flux:button size="sm" variant="{{ $filter === $filterKey ? 'primary' : 'filled' }}" wire:click="setFilter('{{ $filterKey }}')">
                    {{ $label }}
                </flux:button>
            @endforeach
        </div>
    </flux:card>

    <div class="space-y-3">
        @forelse ($bookings as $booking)
            <livewire:host.bookings.host-booking-card :booking-id="$booking['id']" :key="'host-booking-'.$booking['id']" />
        @empty
            <flux:card>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.empty_states.no_host_bookings') }}</flux:text>
            </flux:card>
        @endforelse
    </div>

    @if ($bookings->count() >= $limit)
        <flux:button class="w-full" icon="plus" wire:click="loadMore" wire:loading.attr="disabled">
            {{ __('bookings.actions.load_more') }}
        </flux:button>
    @endif
</div>
