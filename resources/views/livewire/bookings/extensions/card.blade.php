<section class="space-y-3">
    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <flux:badge color="emerald" icon="check-circle">{{ __('booking_extensions.components.' . $variant) }}</flux:badge>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking_extensions.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('booking_extensions.messages.guest_helper') }}
                </flux:text>
            </div>

            @if ($extension)
                <flux:badge color="{{ in_array($status, ['applied', 'paid', 'approved'], true) ? 'emerald' : (in_array($status, ['rejected', 'payment_failed', 'dates_unavailable'], true) ? 'amber' : 'zinc') }}" icon="exclamation-triangle">
                    {{ __('booking_extensions.statuses.' . $status) }}
                </flux:badge>
            @endif
        </div>

        @if ($booking)
            <div class="grid grid-cols-1 gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.current_booking') }}</span>
                    <span class="font-medium">{{ $booking->booking_number ?? __('booking_extensions.empty.no_booking_number') }}</span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.sleeping_place') }}</span>
                    <span class="font-medium">
                        {{ $booking->room?->title ?? __('booking_extensions.empty.unknown_room') }}
                        {{ $booking->sleepingPlace?->display_name ? ' · ' . $booking->sleepingPlace->display_name : '' }}
                    </span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.current_check_out_date') }}</span>
                    <span class="font-medium">
                        {{ $booking->check_out_date?->format('Y-m-d') ?? $booking->check_out }}
                        {{ $booking->check_out_time ? ' · ' . $booking->check_out_time->format('H:i') : '' }}
                    </span>
                </div>
            </div>
        @else
            <flux:text size="sm">{{ __('booking_extensions.empty.no_booking') }}</flux:text>
        @endif

        @if ($extension)
            <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.new_check_out_date') }}</span>
                        <span class="font-medium text-zinc-950 dark:text-white">{{ $extension->new_check_out_date?->format('Y-m-d') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.total_payable') }}</span>
                        <span class="font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $extension->total_payable, 2) }} {{ $extension->currency }}</span>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.additional_nights') }}</span>
                        <span>{{ $extension->additional_nights_count }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.requires_host_confirmation') }}</span>
                        <span>{{ $extension->requires_host_confirmation ? __('booking_extensions.boolean.yes') : __('booking_extensions.boolean.no') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array($variant, ['guest_page', 'form'], true))
            <form wire:submit="requestExtension" class="space-y-3">
                <flux:input type="date" wire:model.change="newCheckOutDate" :label="__('booking_extensions.fields.new_check_out_date')" icon="calendar-days" />
                <flux:textarea rows="3" wire:model.blur="guestMessage" :label="__('booking_extensions.fields.guest_message')" />

                <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" wire:loading.attr="disabled" icon="calendar-days">
                    <span wire:loading.remove wire:target="requestExtension">{{ __('booking_extensions.actions.request_extension') }}</span>
                    <span wire:loading wire:target="requestExtension">{{ __('booking_extensions.actions.requesting_extension') }}</span>
                </flux:button>
            </form>
        @elseif ($variant === 'request_button')
            <flux:button type="button" variant="primary" class="w-full" wire:loading.attr="disabled" icon="calendar-days">
                {{ __('booking_extensions.actions.request_extension') }}
            </flux:button>
        @endif

        @if (in_array($variant, ['guest_page', 'quote', 'payment'], true))
            <div class="space-y-2">
                <flux:heading size="md">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking_extensions.sections.price_lines') }}</span>
                    </span>
                </flux:heading>
                @forelse ($lines as $line)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800" wire:key="extension-line-{{ $line->id }}">
                        <span class="min-w-0 text-zinc-700 dark:text-zinc-200">{{ __($line->label_key) }}</span>
                        <span class="font-medium text-zinc-950 dark:text-white">{{ number_format((float) $line->amount, 2) }} {{ $line->currency }}</span>
                    </div>
                @empty
                    <flux:text size="sm">{{ __('booking_extensions.empty.no_price_lines') }}</flux:text>
                @endforelse
            </div>
        @endif

        @if (in_array($variant, ['guest_page', 'warnings'], true))
            <div class="space-y-2">
                <flux:heading size="md">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking_extensions.sections.warnings') }}</span>
                    </span>
                </flux:heading>
                @forelse ($warnings as $warning)
                    <flux:callout color="{{ $warning->blocking ? 'amber' : 'zinc' }}" icon="chat-bubble-left-right">
                        <flux:callout.text>{{ __($warning->message_key, $warning->message_params_json ?: []) }}</flux:callout.text>
                    </flux:callout>
                @empty
                    <flux:text size="sm">{{ __('booking_extensions.messages.available') }}</flux:text>
                @endforelse
            </div>
        @endif

        @if ($variant === 'payment')
            <flux:button type="button" variant="primary" class="w-full" wire:click="markPaid" wire:loading.attr="disabled" icon="credit-card">
                {{ __('booking_extensions.actions.pay') }}
            </flux:button>
        @endif

        @if ($variant === 'timeline')
            <div class="space-y-2">
                <flux:heading size="md">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking_extensions.sections.timeline') }}</span>
                    </span>
                </flux:heading>
                @forelse ($timeline ?? collect() as $item)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <span>{{ __('booking_extensions.timeline.' . $item['key']) }}</span>
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $item['date'] ?: __('booking_extensions.empty.not_set') }}</span>
                    </div>
                @empty
                    <flux:text size="sm">{{ __('booking_extensions.empty.no_timeline') }}</flux:text>
                @endforelse
            </div>
        @endif
    </flux:card>
</section>
