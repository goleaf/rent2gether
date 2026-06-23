<x-ui.section>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="amber" icon="exclamation-triangle">{{ __('host_unresponsive.components.guest_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_unresponsive.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('host_unresponsive.messages.guest_intro') }}
                </flux:text>
            </div>

            @if ($case)
                <flux:badge color="red" icon="exclamation-triangle">{{ __('host_unresponsive.statuses.' . $case->status) }}</flux:badge>
            @endif
        </div>

        @if ($booking || $case)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.sleeping_place') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $case?->sleepingPlace?->display_name ?? $booking?->sleepingPlace?->display_name ?? $case?->sleepingPlace?->title ?? $booking?->sleepingPlace?->title ?? __('host_unresponsive.empty.unknown_place') }}
                    </span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.check_in_date') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">
                        {{ $case?->check_in_date?->toDateString() ?? $booking?->check_in_date?->toDateString() ?? __('host_unresponsive.empty.unknown_date') }}
                    </span>
                </div>
            </div>
        @endif

        @if ($case)
            <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100">
                <p class="font-medium">{{ __('host_unresponsive.messages.urgent_host_alert_sent') }}</p>
                <p class="mt-1">{{ __('host_unresponsive.messages.waiting_until', ['time' => $case->response_deadline_at?->format('H:i') ?? __('host_unresponsive.empty.unknown_time')]) }}</p>
                <p class="mt-1">{{ __('host_unresponsive.messages.no_show_blocked') }}</p>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                <flux:button wire:click="markAtAddress" wire:loading.attr="disabled" icon="check">
                    {{ __('host_unresponsive.actions.mark_at_address') }}
                </flux:button>
                <flux:button wire:click="markWaitingOutside" wire:loading.attr="disabled" icon="clock">
                    {{ __('host_unresponsive.actions.mark_waiting_outside') }}
                </flux:button>
                <flux:button wire:click="markFeelsUnsafe" wire:loading.attr="disabled" icon="check">
                    {{ __('host_unresponsive.actions.mark_feels_unsafe') }}
                </flux:button>
                <flux:button wire:click="continueWaiting" wire:loading.attr="disabled" icon="arrow-right">
                    {{ __('host_unresponsive.actions.continue_waiting') }}
                </flux:button>
                <flux:button wire:click="requestCancellation" wire:loading.attr="disabled" icon="x-mark">
                    {{ __('host_unresponsive.actions.request_cancellation') }}
                </flux:button>
                <flux:button wire:click="requestRelocation" wire:loading.attr="disabled" icon="plus">
                    {{ __('host_unresponsive.actions.request_relocation') }}
                </flux:button>
            </div>

            <div class="mt-4 rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.case_type') }}</span>
                        {{ __('host_unresponsive.case_types.' . $case->case_type) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.reason') }}</span>
                        {{ __('host_unresponsive.reasons.' . $case->reason_key) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.refund_amount') }}</span>
                        {{ number_format((float) $case->refund_amount, 2) }} {{ $case->currency }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.response_deadline') }}</span>
                        {{ $case->response_deadline_at?->format('H:i') ?? __('host_unresponsive.empty.unknown_time') }}
                    </div>
                </div>
                <p class="mt-3 text-xs text-zinc-600 dark:text-zinc-300">{{ __('host_unresponsive.messages.guest_friendly_refund_notice') }}</p>
            </div>

            <flux:button class="mt-4 w-full" variant="danger" wire:click="openDispute" wire:loading.attr="disabled" icon="exclamation-triangle">
                {{ __('host_unresponsive.actions.open_dispute') }}
            </flux:button>

            <div class="mt-4 space-y-3">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_unresponsive.fields.guest_actions') }}</span>
                    </span>
                </flux:heading>
                @forelse ($case->guestActions as $action)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <p class="font-medium text-zinc-950 dark:text-white">{{ __('host_unresponsive.guest_action_types.' . $action->action_type) }}</p>
                        @if ($action->message)
                            <p class="mt-1 text-zinc-600 dark:text-zinc-300">{{ $action->message }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('host_unresponsive.empty.no_actions') }}</p>
                @endforelse
            </div>
        @else
            <div class="mt-4 grid gap-3">
                <flux:select wire:model.change="caseType" :label="__('host_unresponsive.fields.case_type')">
                    @foreach ($caseTypes as $type)
                        <flux:select.option value="{{ $type }}">{{ __('host_unresponsive.case_types.' . $type) }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.change="reasonKey" :label="__('host_unresponsive.fields.reason')">
                    @foreach ($reasons as $reason)
                        <flux:select.option value="{{ $reason }}">{{ __('host_unresponsive.reasons.' . $reason) }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea wire:model.blur="message" :label="__('host_unresponsive.fields.guest_comment')" rows="3" />
                <flux:textarea wire:model.blur="locationNote" :label="__('host_unresponsive.fields.guest_location_note')" rows="2" />

                <flux:button variant="danger" wire:click="reportHostNotAnswering" wire:loading.attr="disabled" icon="chat-bubble-left-right">
                    {{ __('host_unresponsive.actions.report_host_not_answering') }}
                </flux:button>
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($cases as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->case_number }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ __('host_unresponsive.reasons.' . $item->reason_key) }}</p>
                    </div>
                    <flux:badge icon="calendar-days">{{ __('host_unresponsive.statuses.' . $item->status) }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('host_unresponsive.empty.no_cases') }}
            </div>
        @endforelse
    </div>
</x-ui.section>
