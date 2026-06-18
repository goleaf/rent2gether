<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Manage Booking') }} #{{ $booking->id }}</flux:heading>

    @if(session('success'))
        <flux:badge color="green">{{ session('success') }}</flux:badge>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Guest') }}</flux:heading>
            <div class="flex items-center gap-3">
                <div class="size-12 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                    <flux:icon name="user" class="size-6 text-zinc-400" />
                </div>
                <div>
                    <flux:text class="font-medium">{{ $booking->guest->name }}</flux:text>
                    <flux:text size="sm" class="text-zinc-500">{{ $booking->guest->email }}</flux:text>
                    @if($booking->guest->completed_stays_count)
                        <flux:text size="sm" class="text-zinc-500">{{ $booking->guest->completed_stays_count }} {{ __('stays') }} &middot; {{ number_format($booking->guest->rating_as_guest ?? 0, 1) }} &#9733;</flux:text>
                    @endif
                </div>
            </div>
            @if($booking->guest_message)
                <div class="mt-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <flux:text size="sm" class="text-zinc-500">{{ __('Guest message') }}:</flux:text>
                    <flux:text>{{ $booking->guest_message }}</flux:text>
                </div>
            @endif
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Booking Details') }}</flux:heading>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Status') }}</span><flux:badge>{{ $booking->status->label() }}</flux:badge></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Bed') }}</span><span>{{ $booking->bed->title }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Check-in') }}</span><span>{{ $booking->check_in->format('M d, Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Check-out') }}</span><span>{{ $booking->check_out->format('M d, Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('Nights') }}</span><span>{{ $booking->nights }}</span></div>
                <div class="flex justify-between font-semibold"><span>{{ __('Total') }}</span><span>&euro;{{ number_format($booking->total, 2) }}</span></div>
            </div>
        </flux:card>
    </div>

    <div class="flex flex-wrap gap-3">
        @if($booking->status->value === 'pending_host')
            <flux:button wire:click="approve" variant="primary">{{ __('Approve') }}</flux:button>
            <flux:button wire:click="$set('showRejectModal', true)" variant="danger">{{ __('Reject') }}</flux:button>
        @endif

        @if(in_array($booking->status->value, ['confirmed', 'paid', 'ready_for_checkin']))
            <flux:button wire:click="confirmCheckIn" variant="primary">{{ __('Confirm check-in') }}</flux:button>
            <flux:button wire:click="$set('showCancelModal', true)" variant="danger">{{ __('Cancel') }}</flux:button>
        @endif

        @if(in_array($booking->status->value, ['checked_in', 'active_stay', 'leaving_soon']))
            <flux:button wire:click="confirmCheckOut" variant="primary">{{ __('Confirm check-out') }}</flux:button>
        @endif

        <flux:button href="{{ route('host.bookings.index', ['locale' => app()->getLocale()]) }}" variant="ghost">
            {{ __('Back') }}
        </flux:button>
    </div>

    @if($showRejectModal)
        <flux:modal wire:model="showRejectModal">
            <flux:heading size="lg">{{ __('Reject Booking') }}</flux:heading>
            <flux:textarea wire:model="rejectReason" label="{{ __('Reason (optional)') }}" rows="3" />
            <div class="flex gap-3 mt-4">
                <flux:button wire:click="reject" variant="danger">{{ __('Reject') }}</flux:button>
                <flux:button wire:click="$set('showRejectModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
            </div>
        </flux:modal>
    @endif

    @if($showCancelModal)
        <flux:modal wire:model="showCancelModal">
            <flux:heading size="lg">{{ __('Cancel Booking') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('The guest will receive a full refund.') }}</flux:text>
            <flux:textarea wire:model="cancelReason" label="{{ __('Reason (optional)') }}" rows="3" />
            <div class="flex gap-3 mt-4">
                <flux:button wire:click="cancel" variant="danger">{{ __('Confirm cancellation') }}</flux:button>
                <flux:button wire:click="$set('showCancelModal', false)" variant="ghost">{{ __('Back') }}</flux:button>
            </div>
        </flux:modal>
    @endif
</div>
