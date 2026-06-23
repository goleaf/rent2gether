<x-ui.page class="space-y-4">
    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('bookings.host.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.host.subtitle') }}</flux:text>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach ($filters as $filterKey => $label)
                <flux:button size="sm" variant="{{ $filter === $filterKey ? 'primary' : 'filled' }}" wire:click="setFilter('{{ $filterKey }}')" icon="funnel">
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
        <flux:button class="w-full" icon="arrow-down" wire:click="loadMore" wire:loading.attr="disabled">
            {{ __('bookings.actions.load_more') }}
        </flux:button>
    @endif
</x-ui.page>
