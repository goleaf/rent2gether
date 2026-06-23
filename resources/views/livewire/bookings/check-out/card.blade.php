<div class="space-y-3">
    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-normal text-zinc-500">
                    {{ __('check_out.components.' . $variant) }}
                </p>
                <h2 class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">
                    {{ __('check_out.title') }}
                </h2>
            </div>

            <flux:badge color="{{ $status === 'completed' ? 'emerald' : (in_array($status, ['problem_reported', 'deposit_disputed', 'checkout_overdue'], true) ? 'amber' : 'zinc') }}" icon="exclamation-triangle">
                {{ __('check_out.statuses.' . $status) }}
            </flux:badge>
        </div>

        @if ($booking)
            <div class="grid grid-cols-1 gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_out.fields.guest') }}</span>
                    <span class="font-medium">{{ $booking->guest?->name ?? __('check_out.empty.unknown_guest') }}</span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_out.fields.room') }}</span>
                    <span class="font-medium">
                        {{ $booking->room?->title ?? __('check_out.empty.unknown_room') }}
                        {{ $booking->sleepingPlace?->display_name ? ' · ' . $booking->sleepingPlace->display_name : '' }}
                    </span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_out.fields.check_out_date') }}</span>
                    <span class="font-medium">
                        {{ $booking->check_out_date?->format('Y-m-d') }}
                        {{ $booking->check_out_time ? ' · ' . $booking->check_out_time->format('H:i') : '' }}
                    </span>
                </div>
            </div>
        @else
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('check_out.empty.no_booking') }}
            </p>
        @endif

        @if ($canOfferExtension)
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-950 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">
                {{ __('check_out.messages.extension_available') }}
            </div>
        @endif

        @if ($items->isNotEmpty())
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('check_out.sections.checklist') }}</p>
                <div class="space-y-2">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                            <span class="min-w-0 text-zinc-700 dark:text-zinc-200">{{ __($item->label_key) }}</span>
                            <flux:badge color="{{ $item->status === 'completed' ? 'emerald' : 'zinc' }}" icon="check-circle">
                                {{ __('check_out.item_statuses.' . $item->status) }}
                            </flux:badge>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($issues->isNotEmpty())
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('check_out.sections.issues') }}</p>
                @foreach ($issues as $issue)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100">
                        <p class="font-medium">{{ __('check_out.issues.' . $issue->issue_type) }}</p>
                        <p>{{ $issue->description ?: __('check_out.empty.no_issue_description') }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($variant === 'forgotten_items')
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('check_out.sections.forgotten_items') }}</p>
                @forelse ($forgottenItems as $forgottenItem)
                    <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $forgottenItem->item_name ?: __('check_out.sections.forgotten_items') }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ __('check_out.forgotten_item_statuses.' . $forgottenItem->status) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('check_out.empty.no_items') }}</p>
                @endforelse
            </div>
        @endif

        @if ($variant === 'deposit_decision')
            <div class="space-y-2 rounded-lg bg-zinc-50 p-3 text-sm dark:bg-zinc-900">
                <p class="font-medium text-zinc-950 dark:text-white">{{ __('check_out.sections.deposit') }}</p>
                @if ($depositDecision)
                    <p class="text-zinc-700 dark:text-zinc-200">
                        {{ __('check_out.deposit_decisions.' . $depositDecision->decision) }} · {{ __('check_out.deposit_statuses.' . $depositDecision->status) }}
                    </p>
                @else
                    <p class="text-zinc-600 dark:text-zinc-300">{{ __('check_out.empty.no_deposit_decision') }}</p>
                @endif
            </div>
        @endif

        @if ($variant === 'review_request')
            <div class="space-y-2 rounded-lg bg-zinc-50 p-3 text-sm dark:bg-zinc-900">
                <p class="font-medium text-zinc-950 dark:text-white">{{ __('check_out.sections.reviews') }}</p>
                <p class="text-zinc-600 dark:text-zinc-300">
                    {{ $reviewRequests->isNotEmpty() ? __('check_out.messages.review_requested') : __('check_out.empty.no_review_requests') }}
                </p>
            </div>
        @endif

        @if ($variant === 'guest_page' || $variant === 'guest_confirm_button')
            <flux:button type="button" variant="primary" class="w-full" wire:click="confirm" wire:loading.attr="disabled" icon="clipboard-document-check">
                {{ __('check_out.actions.i_checked_out') }}
            </flux:button>
        @endif

        @if ($variant === 'host_confirm_button')
            <flux:button type="button" variant="primary" class="w-full" wire:click="confirm" wire:loading.attr="disabled" icon="clipboard-document-check">
                {{ __('check_out.actions.confirm_checkout') }}
            </flux:button>
        @endif

        @if ($variant === 'inspection_panel')
            <div class="space-y-3">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="roomChecked" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('check_out.fields.room_checked') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="roomChecked" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="sleepingPlaceChecked" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('check_out.fields.sleeping_place_free') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="sleepingPlaceChecked" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="hasDamage" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('check_out.fields.has_damage') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasDamage" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="hasExtraDirty" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('check_out.fields.has_extra_dirty') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasExtraDirty" />
                </flux:field>
                <flux:button type="button" variant="primary" class="w-full" wire:click="completeInspection" wire:loading.attr="disabled" icon="clipboard-document-check">
                    {{ __('check_out.actions.create_inspection') }}
                </flux:button>
            </div>
        @endif

        @if ($variant === 'issue_sheet')
            <form wire:submit="report" class="space-y-3">
                <flux:select wire:model.change="issueType" :label="__('check_out.fields.issue_type')">
                    @foreach (array_keys(__('check_out.issues')) as $issueType)
                        <flux:select.option value="{{ $issueType }}">{{ __('check_out.issues.' . $issueType) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.change="severity" :label="__('check_out.fields.issue_severity')">
                    @foreach (array_keys(__('check_out.severities')) as $severityKey)
                        <flux:select.option value="{{ $severityKey }}">{{ __('check_out.severities.' . $severityKey) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:textarea wire:model.blur="description" :label="__('check_out.fields.issue_description')" />
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="depositRelated" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('check_out.fields.needs_deposit_deduction') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="depositRelated" />
                </flux:field>
                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" icon="clipboard-document-check">
                    {{ __('check_out.actions.report_issue') }}
                </flux:button>
            </form>
        @endif

        @if ($variant === 'forgotten_items')
            <form wire:submit="createItem" class="space-y-3">
                <flux:input wire:model.blur="itemName" :label="__('check_out.fields.forgotten_item_name')" icon="calendar-days" />
                <flux:input wire:model.blur="storageLocation" :label="__('check_out.fields.storage_location')" icon="calendar-days" />
                <flux:input type="date" wire:model.change="keepUntil" :label="__('check_out.fields.keep_until')" icon="calendar-days" />
                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" icon="clipboard-document-check">
                    {{ __('check_out.actions.add_forgotten_item') }}
                </flux:button>
            </form>
        @endif

        @if ($variant === 'deposit_decision')
            <div class="space-y-3">
                <flux:button type="button" variant="primary" class="w-full" wire:click="returnFull" wire:loading.attr="disabled" icon="clipboard-document-check">
                    {{ __('check_out.actions.return_deposit') }}
                </flux:button>
                <flux:input wire:model.blur="deductionAmount" :label="__('check_out.fields.deposit_deduction_amount')" icon="calendar-days" />
                <flux:textarea wire:model.blur="deductionReason" :label="__('check_out.fields.deposit_deduction_reason')" />
                <flux:button type="button" variant="danger" class="w-full" wire:click="requestDeduction" wire:loading.attr="disabled" icon="clipboard-document-check">
                    {{ __('check_out.actions.deduct_deposit') }}
                </flux:button>
            </div>
        @endif

        @if ($variant === 'review_request')
            <flux:button type="button" variant="primary" class="w-full" wire:click="sendRequests" wire:loading.attr="disabled" icon="paper-airplane">
                {{ __('check_out.actions.request_review') }}
            </flux:button>
        @endif
    </flux:card>
</div>
