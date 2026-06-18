<div class="max-w-5xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Host Dashboard') }}</flux:heading>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <flux:card class="text-center">
            <div class="text-2xl font-bold">&euro;{{ number_format($this->monthlyIncome, 2) }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('This month') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">{{ $this->pendingRequests->count() }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('Pending requests') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">{{ $this->activeGuests->count() }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('Active guests') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-bold">{{ $this->totalFreeBeds }}</div>
            <flux:text size="sm" class="text-zinc-500">{{ __('Free beds') }}</flux:text>
        </flux:card>
    </div>

    @if($this->pendingRequests->isNotEmpty())
        <div class="space-y-3">
            <flux:heading size="lg">{{ __('Pending Requests') }}</flux:heading>
            @foreach($this->pendingRequests as $booking)
                <flux:card class="flex items-center justify-between">
                    <div>
                        <flux:text class="font-medium">{{ $booking->guest->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">
                            {{ $booking->bed->title }} &middot;
                            {{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d') }}
                            &middot; &euro;{{ number_format($booking->total, 2) }}
                        </flux:text>
                    </div>
                    <flux:button size="sm" href="{{ route('host.bookings.manage', ['locale' => app()->getLocale(), 'booking' => $booking]) }}">
                        {{ __('Review') }}
                    </flux:button>
                </flux:card>
            @endforeach
        </div>
    @endif

    @if($this->upcomingCheckIns->isNotEmpty())
        <div class="space-y-3">
            <flux:heading size="lg">{{ __('Upcoming Check-ins') }}</flux:heading>
            @foreach($this->upcomingCheckIns as $booking)
                <flux:card class="flex items-center justify-between">
                    <div>
                        <flux:text class="font-medium">{{ $booking->guest->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">{{ $booking->bed->title }} &middot; {{ $booking->check_in->format('M d') }}</flux:text>
                    </div>
                    <flux:badge color="blue">{{ $booking->check_in->diffForHumans() }}</flux:badge>
                </flux:card>
            @endforeach
        </div>
    @endif

    @if($this->activeGuests->isNotEmpty())
        <div class="space-y-3">
            <flux:heading size="lg">{{ __('Active Guests') }}</flux:heading>
            @foreach($this->activeGuests as $booking)
                <flux:card class="flex items-center justify-between">
                    <div>
                        <flux:text class="font-medium">{{ $booking->guest->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-500">{{ $booking->bed->title }} &middot; {{ __('Checkout') }}: {{ $booking->check_out->format('M d') }}</flux:text>
                    </div>
                    <flux:button size="sm" href="{{ route('host.bookings.manage', ['locale' => app()->getLocale(), 'booking' => $booking]) }}">
                        {{ __('Manage') }}
                    </flux:button>
                </flux:card>
            @endforeach
        </div>
    @endif

    <div class="space-y-3">
        <flux:heading size="lg">{{ __('Properties') }}</flux:heading>
        @forelse($this->properties as $property)
            <flux:card class="flex items-center justify-between">
                <div>
                    <flux:text class="font-medium">{{ $property->title }}</flux:text>
                    <flux:text size="sm" class="text-zinc-500">{{ $property->city }} &middot; {{ $property->rooms_count }} {{ __('rooms') }} &middot; {{ $property->beds_count }} {{ __('beds') }}</flux:text>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text class="text-center text-zinc-500 py-4">{{ __('No properties yet.') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</div>
