<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Check-in') }}</flux:heading>

    <flux:card class="space-y-1">
        <flux:text class="font-medium">{{ $booking->bed->title }}</flux:text>
        <flux:text size="sm" class="text-zinc-500">{{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }}</flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Checklist') }}</flux:heading>
            <flux:checkbox wire:model="keysReceived" label="{{ __('Keys received') }}" />
            <flux:checkbox wire:model="bedInspected" label="{{ __('Bed inspected') }}" />
            <flux:checkbox wire:model="rulesExplained" label="{{ __('House rules explained') }}" />
            <flux:checkbox wire:model="conditionAcceptable" label="{{ __('Condition acceptable') }}" />
        </flux:card>

        <flux:textarea wire:model="notes" label="{{ __('Notes (optional)') }}" rows="3" />

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('Confirm check-in') }}</flux:button>
            <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" variant="ghost">{{ __('Back') }}</flux:button>
        </div>
    </form>
</div>
