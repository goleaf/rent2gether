<x-ui.section>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="amber" icon="exclamation-triangle">{{ __('listing_mismatch.components.guest_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_mismatch.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('listing_mismatch.messages.guest_intro') }}
                </flux:text>
            </div>

            @if ($report)
                <flux:badge color="red" icon="exclamation-triangle">{{ __('listing_mismatch.statuses.' . $report->status) }}</flux:badge>
            @endif
        </div>

        @if ($booking || $report)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.sleeping_place') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $report?->sleepingPlace?->display_name ?? $booking?->sleepingPlace?->display_name ?? $report?->sleepingPlace?->title ?? $booking?->sleepingPlace?->title ?? __('listing_mismatch.empty.unknown_place') }}
                    </span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.booking') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $report?->booking?->booking_number ?? $booking?->booking_number ?? __('listing_mismatch.empty.unknown_booking') }}
                    </span>
                </div>
            </div>
        @endif

        @if ($report)
            <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100">
                <p class="font-medium">{{ __('listing_mismatch.messages.host_notified') }}</p>
                <p class="mt-1">{{ __('listing_mismatch.messages.snapshot_used') }}</p>
                @if ($report->severity === 'unsafe')
                    <p class="mt-1">{{ __('listing_mismatch.messages.unsafe_notice') }}</p>
                @endif
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                <flux:button wire:click="requestRelocation" wire:loading.attr="disabled" icon="plus">
                    {{ __('listing_mismatch.actions.request_relocation') }}
                </flux:button>
                <flux:button wire:click="requestCancellation" wire:loading.attr="disabled" icon="x-mark">
                    {{ __('listing_mismatch.actions.request_cancellation') }}
                </flux:button>
                <flux:button wire:click="requestRefund" wire:loading.attr="disabled" icon="plus">
                    {{ __('listing_mismatch.actions.request_refund') }}
                </flux:button>
                <flux:button wire:click="acceptResolution" wire:loading.attr="disabled" icon="check">
                    {{ __('listing_mismatch.actions.accept_resolution') }}
                </flux:button>
                <flux:button wire:click="rejectResolution" wire:loading.attr="disabled" icon="x-mark">
                    {{ __('listing_mismatch.actions.reject_resolution') }}
                </flux:button>
                <flux:button variant="danger" wire:click="openDispute" wire:loading.attr="disabled" icon="exclamation-triangle">
                    {{ __('listing_mismatch.actions.open_dispute') }}
                </flux:button>
            </div>

            <div class="mt-4 rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.mismatch_type') }}</span>
                        {{ __('listing_mismatch.types.' . $report->mismatch_type) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.severity') }}</span>
                        {{ __('listing_mismatch.severities.' . $report->severity) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.refund_amount') }}</span>
                        {{ number_format((float) $report->refund_amount, 2) }} {{ $report->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.compensation_amount') }}</span>
                        {{ number_format((float) $report->compensation_amount, 2) }} {{ $report->currency }}
                    </div>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_mismatch.fields.resolution_options') }}</span>
                    </span>
                </flux:heading>
                @forelse ($report->resolutionOptions as $option)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <p class="font-medium text-zinc-950 dark:text-white">{{ __('listing_mismatch.resolution_types.' . $option->resolution_type) }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ __('listing_mismatch.resolution_statuses.' . $option->status) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('listing_mismatch.empty.no_resolution_options') }}</p>
                @endforelse
            </div>

            <div class="mt-4 space-y-3">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_mismatch.fields.warnings') }}</span>
                    </span>
                </flux:heading>
                @forelse ($report->warnings as $warning)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <p class="font-medium text-zinc-950 dark:text-white">{{ __('listing_mismatch.warning_keys.' . $warning->warning_key) }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ __($warning->message_key) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('listing_mismatch.empty.no_warnings') }}</p>
                @endforelse
            </div>
        @else
            <div class="mt-4 grid gap-3">
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_mismatch.fields.mismatch_type') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="mismatchType">
                    @foreach ($types as $type)
                        <flux:select.option value="{{ $type }}">{{ __('listing_mismatch.types.' . $type) }}</flux:select.option>
                    @endforeach
                </flux:select>
                    <flux:error name="mismatchType" />
                </flux:field>

                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_mismatch.fields.severity') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="severity">
                    @foreach ($severities as $severity)
                        <flux:select.option value="{{ $severity }}">{{ __('listing_mismatch.severities.' . $severity) }}</flux:select.option>
                    @endforeach
                </flux:select>
                    <flux:error name="severity" />
                </flux:field>

                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('listing_mismatch.fields.guest_description') }}</span>
                        </span>
                    </flux:label>
                    <flux:textarea wire:model.blur="guestDescription" rows="3" />
                    <flux:error name="guestDescription" />
                </flux:field>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="guestWantsFix" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('listing_mismatch.fields.guest_wants_fix') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="guestWantsFix" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="guestWantsRelocation" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('listing_mismatch.fields.guest_wants_relocation') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="guestWantsRelocation" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="guestWantsCancellation" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('listing_mismatch.fields.guest_wants_cancellation') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="guestWantsCancellation" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="guestWantsRefund" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('listing_mismatch.fields.guest_wants_refund') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="guestWantsRefund" />
                    </flux:field>
                </div>

                <flux:button variant="danger" wire:click="reportMismatch" wire:loading.attr="disabled" icon="exclamation-triangle">
                    {{ __('listing_mismatch.actions.report_mismatch') }}
                </flux:button>
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($reports as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->mismatch_number }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ __('listing_mismatch.types.' . $item->mismatch_type) }}</p>
                    </div>
                    <flux:badge icon="exclamation-triangle">{{ __('listing_mismatch.statuses.' . $item->status) }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('listing_mismatch.empty.no_reports') }}
            </div>
        @endforelse
    </div>
</x-ui.section>
