<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Extension Request') }}</flux:heading>

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
            <div class="flex justify-between"><span class="text-zinc-500">{{ __('Original checkout') }}</span><span>{{ $extension->original_check_out->format('M d, Y') }}</span></div>
            <div class="flex justify-between"><span class="text-zinc-500">{{ __('New checkout') }}</span><span>{{ $extension->new_check_out->format('M d, Y') }}</span></div>
            <div class="flex justify-between"><span class="text-zinc-500">{{ __('Extra nights') }}</span><span>{{ $extension->extra_nights }}</span></div>
            <div class="flex justify-between font-semibold"><span>{{ __('Extra amount') }}</span><span>&euro;{{ number_format($extension->extra_amount, 2) }}</span></div>
        </div>

        <flux:badge>{{ ucfirst($extension->status) }}</flux:badge>
    </flux:card>

    @if($extension->status === 'pending')
        <div class="flex gap-3">
            <flux:button wire:click="approve" variant="primary">{{ __('Approve') }}</flux:button>
            <flux:button wire:click="reject" variant="danger">{{ __('Reject') }}</flux:button>
        </div>
    @endif
</div>
