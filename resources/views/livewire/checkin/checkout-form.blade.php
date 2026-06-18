<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Check-out') }}</flux:heading>

    <flux:card class="space-y-1">
        <flux:text class="font-medium">{{ $booking->bed->title }}</flux:text>
        <flux:text size="sm" class="text-zinc-500">{{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }}</flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Checklist') }}</flux:heading>
            <flux:checkbox wire:model="keysReturned" label="{{ __('Keys returned') }}" />
            <flux:checkbox wire:model="bedInspected" label="{{ __('Bed inspected') }}" />
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Condition Report') }}</flux:heading>
            <flux:checkbox wire:model="hasDamage" label="{{ __('Damage found') }}" />
            @if($hasDamage)
                <flux:textarea wire:model="damageDescription" label="{{ __('Describe damage') }}" rows="2" />
            @endif
            <flux:checkbox wire:model="hasDirt" label="{{ __('Excessive dirt') }}" />
            @if($hasDirt)
                <flux:textarea wire:model="dirtDescription" label="{{ __('Describe condition') }}" rows="2" />
            @endif
        </flux:card>

        <flux:textarea wire:model="notes" label="{{ __('Notes (optional)') }}" rows="3" />

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('Confirm check-out') }}</flux:button>
            <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" variant="ghost">{{ __('Back') }}</flux:button>
        </div>
    </form>
</div>
