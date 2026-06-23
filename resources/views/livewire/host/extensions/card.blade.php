<section class="space-y-3">
    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <flux:badge color="blue" icon="calendar-days">{{ __('booking_extensions.components.host_' . $variant) }}</flux:badge>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking_extensions.host_title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('booking_extensions.messages.host_helper') }}
                </flux:text>
            </div>

            @if ($extension)
                <flux:badge color="{{ in_array($status, ['applied', 'paid', 'approved'], true) ? 'emerald' : 'zinc' }}" icon="check-circle">
                    {{ __('booking_extensions.statuses.' . $status) }}
                </flux:badge>
            @endif
        </div>

        @if (($extensions ?? collect())->isNotEmpty())
            <div class="space-y-2">
                @forelse ($extensions as $hostExtension)
                    <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800" wire:key="host-extension-{{ $hostExtension->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-zinc-950 dark:text-white">{{ $hostExtension->guest?->name ?? __('booking_extensions.empty.unknown_guest') }}</p>
                                <p class="text-zinc-600 dark:text-zinc-300">
                                    {{ $hostExtension->room?->title ?? __('booking_extensions.empty.unknown_room') }}
                                    {{ $hostExtension->sleepingPlace?->display_name ? ' · ' . $hostExtension->sleepingPlace->display_name : '' }}
                                </p>
                            </div>
                            <span>{{ number_format((float) $hostExtension->total_payable, 2) }} {{ $hostExtension->currency }}</span>
                        </div>
                    </div>
                @empty
                    <flux:text size="sm">{{ __('booking_extensions.empty.no_extensions') }}</flux:text>
                @endforelse
            </div>
        @endif

        @if ($extension)
            <div class="grid grid-cols-1 gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.guest') }}</span>
                    <span class="font-medium">{{ $extension->guest?->name ?? __('booking_extensions.empty.unknown_guest') }}</span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.sleeping_place') }}</span>
                    <span class="font-medium">
                        {{ $extension->room?->title ?? __('booking_extensions.empty.unknown_room') }}
                        {{ $extension->sleepingPlace?->display_name ? ' · ' . $extension->sleepingPlace->display_name : '' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                        <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.current_check_out_date') }}</span>
                        <span class="font-medium">{{ $extension->current_check_out_date?->format('Y-m-d') }}</span>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                        <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.new_check_out_date') }}</span>
                        <span class="font-medium">{{ $extension->new_check_out_date?->format('Y-m-d') }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                        <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.additional_nights') }}</span>
                        <span class="font-medium">{{ $extension->additional_nights_count }}</span>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                        <span class="block text-xs text-zinc-500">{{ __('booking_extensions.fields.total_payable') }}</span>
                        <span class="font-medium">{{ number_format((float) $extension->total_payable, 2) }} {{ $extension->currency }}</span>
                    </div>
                </div>
            </div>

            @if (in_array($variant, ['details', 'response_panel', 'card'], true))
                <div class="grid gap-2">
                    <flux:button type="button" variant="primary" class="w-full" wire:click="approve" wire:loading.attr="disabled" icon="calendar-days">
                        {{ __('booking_extensions.actions.approve') }}
                    </flux:button>
                    <flux:button type="button" variant="danger" class="w-full" wire:click="reject" wire:loading.attr="disabled" icon="x-mark">
                        {{ __('booking_extensions.actions.reject') }}
                    </flux:button>
                                        <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('booking_extensions.fields.proposed_new_check_out_date') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="proposedNewCheckOutDate" icon="calendar-days" />
                        <flux:error name="proposedNewCheckOutDate" />
                    </flux:field>
                    <flux:button type="button" class="w-full" wire:click="proposeNewCheckout" wire:loading.attr="disabled" icon="clipboard-document-check">
                        {{ __('booking_extensions.actions.propose_new_checkout') }}
                    </flux:button>
                </div>
            @endif

            @if ($variant === 'price')
                <div class="space-y-2">
                    <flux:heading size="md">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_extensions.sections.price_lines') }}</span>
                        </span>
                    </flux:heading>
                    @forelse ($lines as $line)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800" wire:key="host-extension-line-{{ $line->id }}">
                            <span>{{ __($line->label_key) }}</span>
                            <span>{{ number_format((float) $line->amount, 2) }} {{ $line->currency }}</span>
                        </div>
                    @empty
                        <flux:text size="sm">{{ __('booking_extensions.empty.no_price_lines') }}</flux:text>
                    @endforelse
                </div>
            @endif
        @else
            <flux:text size="sm">{{ __('booking_extensions.empty.no_extension') }}</flux:text>
        @endif
    </flux:card>
</section>
