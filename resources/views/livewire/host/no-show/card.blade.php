<x-ui.section>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="amber" icon="exclamation-triangle">{{ __('no_show.components.host_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('no_show.host_title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('no_show.messages.host_waiting_notice') }}
                </flux:text>
            </div>

            @if ($noShow)
                <flux:badge color="red" icon="exclamation-triangle">{{ __('no_show.statuses.' . $noShow->status) }}</flux:badge>
            @endif
        </div>

        @if ($booking)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('no_show.fields.guest') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">{{ $booking->guest?->name ?? __('no_show.empty.unknown_guest') }}</span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('no_show.fields.sleeping_place') }}</span>
                    {{ $booking->sleepingPlace?->display_name ?? $booking->sleepingPlace?->title ?? __('no_show.empty.unknown_place') }}
                </div>
            </div>

            <div class="mt-4 grid gap-3">
                <flux:textarea wire:model.blur="hostComment" :label="__('no_show.fields.host_comment')" rows="3" />
                <flux:button variant="danger" wire:click="reportNoShow" wire:loading.attr="disabled" icon="eye">
                    {{ __('no_show.actions.report_no_show') }}
                </flux:button>
            </div>
        @endif

        @if ($noShow)
            <div class="mt-4 rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('no_show.fields.waiting_until') }}</span>
                        {{ $noShow->waiting_until?->format('H:i') ?? __('no_show.empty.unknown_time') }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('no_show.fields.guest_response') }}</span>
                        {{ $noShow->guest_response_type ? __('no_show.guest_responses.' . $noShow->guest_response_type) : __('no_show.empty.no_responses') }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('no_show.fields.refund_amount') }}</span>
                        {{ number_format((float) $noShow->refund_amount, 2) }} {{ $noShow->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('no_show.fields.host_payout_amount') }}</span>
                        {{ number_format((float) $noShow->host_payout_amount, 2) }} {{ $noShow->currency }}
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                <flux:button variant="danger" wire:click="confirmNoShow" wire:loading.attr="disabled" icon="eye">
                    {{ __('no_show.actions.confirm_no_show') }}
                </flux:button>
                <flux:button wire:click="rejectNoShow" wire:loading.attr="disabled" icon="x-mark">
                    {{ __('no_show.actions.reject_no_show') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="cancelNoShow" wire:loading.attr="disabled" icon="x-mark">
                    {{ __('no_show.actions.cancel_no_show') }}
                </flux:button>
            </div>

            <div class="mt-4 space-y-3">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('no_show.fields.contact_attempts') }}</span>
                    </span>
                </flux:heading>
                @forelse ($noShow->contactAttempts as $attempt)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <p class="font-medium text-zinc-950 dark:text-white">{{ __('no_show.contact_attempt_types.' . $attempt->attempt_type) }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            {{ __('no_show.contact_channels.' . $attempt->contact_channel) }} · {{ __('no_show.contact_statuses.' . $attempt->status) }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('no_show.empty.no_attempts') }}</p>
                @endforelse
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($noShows as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->guest?->name ?? __('no_show.empty.unknown_guest') }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            {{ $item->sleepingPlace?->display_name ?? $item->sleepingPlace?->title ?? __('no_show.empty.unknown_place') }}
                        </p>
                    </div>
                    <flux:badge icon="exclamation-triangle">{{ __('no_show.statuses.' . $item->status) }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('no_show.empty.no_no_show') }}
            </div>
        @endforelse
    </div>
</x-ui.section>
