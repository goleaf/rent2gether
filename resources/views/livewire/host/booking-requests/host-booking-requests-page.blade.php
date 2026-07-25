<x-ui.page class="space-y-4">
    <div class="space-y-1">
        <flux:heading>
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('booking_requests.host_page.title') }}</span>
            </span>
        </flux:heading>
        <flux:text>{{ __('booking_requests.host_page.helper') }}</flux:text>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach($filters as $filterName)
            <flux:button type="button" size="sm" variant="{{ $filter === $filterName ? 'primary' : 'outline' }}" wire:click="setFilter('{{ $filterName }}')" icon="funnel">
                {{ __('booking_requests.filters.'.$filterName) }}
            </flux:button>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse($requests as $request)
            <livewire:host.booking-requests.host-booking-request-card :request="$request->id" :key="'host-request-card-'.$request->id" />
        @empty
            <flux:card>
                <flux:text>{{ __('booking_requests.empty.no_host_requests') }}</flux:text>
            </flux:card>
        @endforelse
    </div>

    @if($hasMore)
        <flux:button type="button" variant="primary" class="w-full" wire:click="loadMore" icon="arrow-down">
            {{ __('booking_requests.actions.load_more') }}
        </flux:button>
    @endif
</x-ui.page>
