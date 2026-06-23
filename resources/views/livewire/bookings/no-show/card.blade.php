<x-ui.section>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="amber" icon="exclamation-triangle">{{ __('no_show.components.guest_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('no_show.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('no_show.messages.guest_response_required') }}
                </flux:text>
            </div>

            @if ($noShow)
                <flux:badge color="red" icon="exclamation-triangle">{{ __('no_show.statuses.' . $noShow->status) }}</flux:badge>
            @endif
        </div>

        @if ($booking || $noShow)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('no_show.fields.sleeping_place') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $noShow?->sleepingPlace?->display_name ?? $booking?->sleepingPlace?->display_name ?? $noShow?->sleepingPlace?->title ?? $booking?->sleepingPlace?->title ?? __('no_show.empty.unknown_place') }}
                    </span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('no_show.fields.dates') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $noShow?->check_in_date?->toDateString() ?? $booking?->check_in_date?->toDateString() }}
                        @if ($booking?->check_out_date)
                            - {{ $booking->check_out_date?->toDateString() }}
                        @endif
                    </span>
                </div>
            </div>
        @endif

        @if ($noShow)
            <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100">
                <p class="font-medium">{{ __('no_show.messages.host_reported_no_show') }}</p>
                <p class="mt-1">{{ __('no_show.messages.waiting_period_active', ['time' => $noShow->waiting_until?->format('H:i') ?? __('no_show.empty.unknown_time')]) }}</p>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                <flux:button wire:click="markOnTheWay" wire:loading.attr="disabled" icon="eye">
                    {{ __('no_show.guest_responses.i_am_on_the_way') }}
                </flux:button>
                <flux:button wire:click="markLate" wire:loading.attr="disabled" icon="eye">
                    {{ __('no_show.guest_responses.i_am_late') }}
                </flux:button>
                <flux:button wire:click="markArrived" wire:loading.attr="disabled" icon="eye">
                    {{ __('no_show.guest_responses.i_arrived') }}
                </flux:button>
                <flux:button wire:click="reportCheckInProblem" wire:loading.attr="disabled" icon="eye">
                    {{ __('no_show.guest_responses.i_have_check_in_problem') }}
                </flux:button>
                <flux:button wire:click="reportHostNotAnswering" wire:loading.attr="disabled" icon="chat-bubble-left-right">
                    {{ __('no_show.guest_responses.host_not_answering') }}
                </flux:button>
                <flux:button wire:click="requestCancellation" wire:loading.attr="disabled" icon="x-mark">
                    {{ __('no_show.guest_responses.i_want_to_cancel') }}
                </flux:button>
            </div>

            <div class="mt-4 grid gap-3">
                <flux:input wire:model.blur="newArrivalTime" :label="__('no_show.fields.new_arrival_time')" icon="clock" />
                <flux:textarea wire:model.blur="message" :label="__('no_show.fields.message')" rows="3" />
            </div>

            <div class="mt-4 rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('no_show.fields.refund_amount') }}</span>
                        {{ number_format((float) $noShow->refund_amount, 2) }} {{ $noShow->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('no_show.fields.penalty_amount') }}</span>
                        {{ number_format((float) $noShow->penalty_amount, 2) }} {{ $noShow->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('no_show.fields.deposit_refund_amount') }}</span>
                        {{ number_format((float) $noShow->deposit_refund_amount, 2) }} {{ $noShow->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('no_show.fields.calendar_release_status') }}</span>
                        {{ __('no_show.calendar_release_statuses.' . $noShow->calendar_release_status) }}
                    </div>
                </div>
                <p class="mt-3 text-xs text-zinc-600 dark:text-zinc-300">{{ __('no_show.messages.deposit_refund_notice') }}</p>
            </div>

            <flux:button class="mt-4 w-full" variant="danger" wire:click="disputeNoShow" wire:loading.attr="disabled" icon="eye">
                {{ __('no_show.actions.dispute') }}
            </flux:button>

            <div class="mt-4 space-y-3">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('no_show.fields.responses') }}</span>
                    </span>
                </flux:heading>
                @forelse ($noShow->guestResponses as $response)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <p class="font-medium text-zinc-950 dark:text-white">{{ __('no_show.guest_responses.' . $response->response_type) }}</p>
                        @if ($response->message)
                            <p class="mt-1 text-zinc-600 dark:text-zinc-300">{{ $response->message }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('no_show.empty.no_responses') }}</p>
                @endforelse
            </div>
        @else
            <div class="mt-4 rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('no_show.empty.no_no_show') }}
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($noShows as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->no_show_number }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ __('no_show.reasons.' . $item->reason_key) }}</p>
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
