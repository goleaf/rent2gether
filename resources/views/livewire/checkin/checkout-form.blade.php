<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('booking.check_out') }}</flux:heading>

    <flux:card class="space-y-1">
        <flux:text class="font-medium">{{ $booking->bed->title }}</flux:text>
        <flux:text size="sm" class="text-zinc-500">{{ $booking->check_in->translatedFormat('d M Y') }} - {{ $booking->check_out->translatedFormat('d M Y') }}</flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('booking.checkin.checklist') }}</flux:heading>
            <flux:checkbox wire:model="keysReturned" label="{{ __('booking.checkout.keys_returned') }}" />
            <flux:checkbox wire:model="bedInspected" label="{{ __('booking.checkin.bed_inspected') }}" />
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('booking.checkout.condition_report') }}</flux:heading>
            <flux:checkbox wire:model="hasDamage" label="{{ __('booking.checkout.damage_found') }}" />
            @if($hasDamage)
                <flux:textarea wire:model="damageDescription" label="{{ __('booking.checkout.describe_damage') }}" rows="2" />
            @endif
            <flux:checkbox wire:model="hasDirt" label="{{ __('booking.checkout.excessive_dirt') }}" />
            @if($hasDirt)
                <flux:textarea wire:model="dirtDescription" label="{{ __('booking.checkout.describe_condition') }}" rows="2" />
            @endif
        </flux:card>

        <flux:textarea wire:model="notes" label="{{ __('booking.notes_optional') }}" rows="3" />

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('host.manage_booking.confirm_checkout') }}</flux:button>
            <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" variant="ghost">{{ __('app.actions.back') }}</flux:button>
        </div>
    </form>
</div>
