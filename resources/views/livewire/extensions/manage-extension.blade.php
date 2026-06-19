<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('booking.extension.request_title') }}</flux:heading>

    @if(session('success'))
        <flux:badge color="green">{{ session('success') }}</flux:badge>
    @endif

    <flux:card class="space-y-3">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                <flux:icon name="user" class="size-5 text-zinc-400" />
            </div>
            <div>
                <flux:text class="font-medium">{{ $extension->booking->guest->name }}</flux:text>
                <flux:text size="sm" class="text-zinc-500">{{ $extension->booking->bed->title }}</flux:text>
            </div>
        </div>

        <div class="text-sm space-y-2">
            <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.extension.original_checkout') }}</span><span>{{ $extension->original_check_out->translatedFormat('d M Y') }}</span></div>
            <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.extension.new_checkout') }}</span><span>{{ $extension->new_check_out->translatedFormat('d M Y') }}</span></div>
            <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.extension.extra_nights') }}</span><span>{{ $extension->extra_nights }}</span></div>
            <div class="flex justify-between font-semibold"><span>{{ __('booking.extension.extra_amount') }}</span><span>&euro;{{ number_format($extension->extra_amount, 2) }}</span></div>
        </div>

        <flux:badge>{{ __('statuses.extension.'.$extension->status) }}</flux:badge>
    </flux:card>

    @if($extension->status === 'pending')
        <div class="flex gap-3">
            <flux:button wire:click="approve" variant="primary">{{ __('host.manage_booking.approve') }}</flux:button>
            <flux:button wire:click="reject" variant="danger">{{ __('host.manage_booking.reject') }}</flux:button>
        </div>
    @endif
</div>
