<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('booking.check_in') }}</flux:heading>

    <flux:card class="space-y-1">
        <flux:text class="font-medium">{{ $booking->bed->title }}</flux:text>
        <flux:text size="sm" class="text-zinc-500">{{ $booking->check_in->translatedFormat('d M Y') }} - {{ $booking->check_out->translatedFormat('d M Y') }}</flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('booking.checkin.checklist') }}</flux:heading>
            <flux:checkbox wire:model="keysReceived" label="{{ __('booking.checkin.keys_received') }}" />
            <flux:checkbox wire:model="bedInspected" label="{{ __('booking.checkin.bed_inspected') }}" />
            <flux:checkbox wire:model="rulesExplained" label="{{ __('booking.checkin.rules_explained') }}" />
            <flux:checkbox wire:model="conditionAcceptable" label="{{ __('booking.checkin.condition_acceptable') }}" />
        </flux:card>

        <flux:textarea wire:model="notes" label="{{ __('booking.notes_optional') }}" rows="3" />

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('host.manage_booking.confirm_checkin') }}</flux:button>
            <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" variant="ghost">{{ __('app.actions.back') }}</flux:button>
        </div>
    </form>
</div>
