<x-ui.page class="space-y-6">
    <flux:heading size="xl">{{ __('host.dashboard') }}</flux:heading>

    <livewire:host.hints.host-hints-panel lazy />

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <flux:card class="text-center">
            <div class="text-2xl font-bold">&euro;{{ number_format($this->monthlyIncome, 2) }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('host.this_month') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">{{ $this->pendingRequests->count() }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('host.pending_requests') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">{{ $this->activeGuests->count() }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('host.active_guests') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">{{ $this->totalFreeBeds }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('host.free_beds') }}</flux:text>
        </flux:card>
    </div>

    @if($this->pendingRequests->isNotEmpty())
        <div class="space-y-3">
            <flux:heading size="lg">{{ __('host.pending_requests') }}</flux:heading>
            @foreach($this->pendingRequests as $booking)
                <flux:card class="flex items-center justify-between">
                    <div>
                        <flux:text class="font-medium">{{ $booking->guest->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">
                            {{ $booking->bed->title }} &middot;
                            {{ $booking->check_in->translatedFormat('d M') }} - {{ $booking->check_out->translatedFormat('d M') }}
                            &middot; &euro;{{ number_format($booking->total, 2) }}
                        </flux:text>
                    </div>
                    <flux:button size="sm" href="{{ route('host.bookings.manage', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate>
                        {{ __('app.actions.review') }}
                    </flux:button>
                </flux:card>
            @endforeach
        </div>
    @endif

    @if($this->upcomingCheckIns->isNotEmpty())
        <div class="space-y-3">
            <flux:heading size="lg">{{ __('host.upcoming_checkins') }}</flux:heading>
            @foreach($this->upcomingCheckIns as $booking)
                <flux:card class="flex items-center justify-between">
                    <div>
                        <flux:text class="font-medium">{{ $booking->guest->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">{{ $booking->bed->title }} &middot; {{ $booking->check_in->translatedFormat('d M') }}</flux:text>
                    </div>
                    <flux:badge color="blue">{{ $booking->check_in->diffForHumans() }}</flux:badge>
                </flux:card>
            @endforeach
        </div>
    @endif

    @if($this->activeGuests->isNotEmpty())
        <div class="space-y-3">
            <flux:heading size="lg">{{ __('host.active_guests') }}</flux:heading>
            @foreach($this->activeGuests as $booking)
                <flux:card class="flex items-center justify-between">
                    <div>
                        <flux:text class="font-medium">{{ $booking->guest->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">{{ $booking->bed->title }} &middot; {{ __('host.checkout') }}: {{ $booking->check_out->translatedFormat('d M') }}</flux:text>
                    </div>
                    <flux:button size="sm" href="{{ route('host.bookings.manage', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate>
                        {{ __('app.actions.manage') }}
                    </flux:button>
                </flux:card>
            @endforeach
        </div>
    @endif

    <div class="space-y-3">
        <flux:heading size="lg">{{ __('host.properties') }}</flux:heading>
        @forelse($this->properties as $property)
            <flux:card class="flex items-center justify-between">
                <div>
                    <flux:text class="font-medium">{{ $property->title }}</flux:text>
                    <flux:text size="sm" class="text-zinc-500">{{ $property->city }} &middot; {{ $property->rooms_count }} {{ __('app.units.rooms') }} &middot; {{ $property->beds_count }} {{ __('app.units.beds') }}</flux:text>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text class="text-center text-zinc-500 py-4">{{ __('host.empty_properties') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</x-ui.page>
