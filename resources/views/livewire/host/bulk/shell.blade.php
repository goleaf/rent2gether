<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="squares-2x2">{{ __('host_bulk.title') }}</flux:badge>

        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('host_bulk.sections.'.$section) }}</span>
                </span>
            </flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                {{ __('host_bulk.helpers.'.$section) }}
            </flux:text>
        </div>
    </section>

    @if($noticeKey)
        <flux:callout icon="check-circle" color="green">
            <flux:callout.text>{{ __($noticeKey) }}</flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="applyBulkAction" class="space-y-4">
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="squares-2x2" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_bulk.choose_action') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host_bulk.helpers.actions') }}</flux:text>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="squares-2x2" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.action_type') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="actionType">
                        @foreach($this->actionOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="actionType" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.target_type') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="targetType">
                        @foreach($this->targetTypeOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="targetType" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_bulk.choose_targets') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host_bulk.helpers.targets') }}</flux:text>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.property') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="propertyId">
                        <flux:select.option value="">{{ __('host_bulk.options.all_properties') }}</flux:select.option>
                        @foreach($this->properties as $property)
                            <flux:select.option value="{{ $property['id'] }}">{{ $property['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.room') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="roomId">
                        <flux:select.option value="">{{ __('host_bulk.options.all_rooms') }}</flux:select.option>
                        @foreach($this->rooms as $room)
                            <flux:select.option value="{{ $room['id'] }}">{{ $room['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex flex-wrap gap-2">
                <flux:button type="button" size="sm" variant="ghost" icon="check" wire:click="selectVisibleTargets" wire:loading.attr="disabled">
                    {{ __('host_bulk.buttons.select_visible') }}
                </flux:button>
                <flux:button type="button" size="sm" variant="ghost" icon="x-mark" wire:click="clearTargets" wire:loading.attr="disabled">
                    {{ __('host_bulk.buttons.clear_selection') }}
                </flux:button>
                <flux:badge color="zinc" icon="check">{{ __('host_bulk.messages.selected_count', ['count' => count($selectedTargetIds)]) }}</flux:badge>
            </div>

            <div class="space-y-2">
                @forelse($this->targetOptions as $target)
                    <label class="flex gap-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <flux:checkbox wire:model.change="selectedTargetIds" value="{{ $target['id'] }}" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $target['label'] }}</span>
                            @if($target['meta'])
                                <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $target['meta'] }}</span>
                            @endif
                        </span>
                    </label>
                @empty
                    <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-center dark:border-zinc-700">
                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host_bulk.empty.targets') }}</flux:text>
                    </div>
                @endforelse
            </div>

            <flux:error name="selectedTargetIds" />
            <flux:error name="selectedTargetIds.0" />
        </flux:card>

        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="squares-2x2" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_bulk.sections.actions') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host_bulk.messages.critical_action_confirm') }}</flux:text>
            </div>

            @if($actionType === 'change_price')
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.price') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="number" inputmode="decimal" step="0.01" min="0" wire:model.blur="price" icon="banknotes" />
                        <flux:error name="price" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.currency') }}</span>
                            </span>
                        </flux:label>
                        <flux:input maxlength="3" wire:model.blur="currency" icon="banknotes" />
                        <flux:error name="currency" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.range_start_optional') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="rangeStart" icon="calendar-days" />
                        <flux:error name="rangeStart" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.range_end_optional') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="rangeEnd" icon="calendar-days" />
                        <flux:error name="rangeEnd" />
                    </flux:field>
                </div>
            @elseif($actionType === 'open_dates')
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.range_start') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="rangeStart" icon="calendar-days" />
                        <flux:error name="rangeStart" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.range_end') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="rangeEnd" icon="calendar-days" />
                        <flux:error name="rangeEnd" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.price_optional') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="number" inputmode="decimal" step="0.01" min="0" wire:model.blur="price" icon="banknotes" />
                        <flux:error name="price" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.currency') }}</span>
                            </span>
                        </flux:label>
                        <flux:input maxlength="3" wire:model.blur="currency" icon="banknotes" />
                        <flux:error name="currency" />
                    </flux:field>
                </div>
            @elseif($actionType === 'close_dates' || $actionType === 'mark_occupied')
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.range_start') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="rangeStart" icon="calendar-days" />
                        <flux:error name="rangeStart" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.range_end') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="rangeEnd" icon="calendar-days" />
                        <flux:error name="rangeEnd" />
                    </flux:field>
                    <flux:field class="sm:col-span-2">
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="pencil-square" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.reason') }}</span>
                            </span>
                        </flux:label>
                        <flux:input wire:model.blur="occupiedReason" icon="pencil-square" />
                        <flux:error name="occupiedReason" />
                    </flux:field>
                </div>
            @elseif($actionType === 'add_discount')
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="receipt-percent" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.discount_type') }}</span>
                            </span>
                        </flux:label>
                        <flux:select wire:model.change="discountType">
                            <flux:select.option value="weekly">{{ __('host_bulk.options.weekly_discount') }}</flux:select.option>
                            <flux:select.option value="monthly">{{ __('host_bulk.options.monthly_discount') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="discountType" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="receipt-percent" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.discount_percent') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="number" inputmode="decimal" step="0.01" min="0" max="90" wire:model.blur="discountPercent" icon="receipt-percent" />
                        <flux:error name="discountPercent" />
                    </flux:field>
                </div>
            @elseif($actionType === 'change_rules')
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="pencil-square" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.rules') }}</span>
                        </span>
                    </flux:label>
                    <flux:textarea rows="5" wire:model.blur="rulesText" placeholder="{{ __('host_bulk.placeholders.rules') }}" />
                    <flux:description>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="pencil-square" variant="mini" class="size-4 shrink-0 text-zinc-400" />
                            <span class="min-w-0">{{ __('host_bulk.helpers.rules') }}</span>
                        </span>
                    </flux:description>
                    <flux:error name="rulesText" />
                </flux:field>
            @elseif($actionType === 'change_check_in_time')
                <div class="grid gap-3 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.check_in_time_from') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="time" wire:model.change="checkInTimeFrom" icon="clock" />
                        <flux:error name="checkInTimeFrom" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.check_in_time_until') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="time" wire:model.change="checkInTimeUntil" icon="clock" />
                        <flux:error name="checkInTimeUntil" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.check_out_time_until') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="time" wire:model.change="checkOutTimeUntil" icon="clock" />
                        <flux:error name="checkOutTimeUntil" />
                    </flux:field>
                </div>
            @elseif($actionType === 'change_cleaning_fee')
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.cleaning_fee') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="number" inputmode="decimal" step="0.01" min="0" wire:model.blur="cleaningFee" icon="banknotes" />
                    <flux:error name="cleaningFee" />
                </flux:field>
            @elseif($actionType === 'message_guests')
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="pencil-square" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.message') }}</span>
                        </span>
                    </flux:label>
                    <flux:textarea rows="5" wire:model.blur="message" />
                    <flux:description>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="pencil-square" variant="mini" class="size-4 shrink-0 text-zinc-400" />
                            <span class="min-w-0">{{ __('host_bulk.helpers.messages') }}</span>
                        </span>
                    </flux:description>
                    <flux:error name="message" />
                </flux:field>
            @elseif($actionType === 'assign_cleaning')
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.cleaning_scheduled_date') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="cleaningScheduledDate" icon="calendar-days" />
                        <flux:error name="cleaningScheduledDate" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.cleaning_scheduled_time') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="time" wire:model.change="cleaningScheduledTime" icon="clock" />
                        <flux:error name="cleaningScheduledTime" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="pencil-square" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.cleaning_reason') }}</span>
                            </span>
                        </flux:label>
                        <flux:input wire:model.blur="cleaningReason" icon="pencil-square" />
                        <flux:error name="cleaningReason" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="pencil-square" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.cleaning_note') }}</span>
                            </span>
                        </flux:label>
                        <flux:input wire:model.blur="cleaningNote" icon="pencil-square" />
                        <flux:error name="cleaningNote" />
                    </flux:field>
                </div>
            @else
                <flux:callout icon="exclamation-triangle">
                    <flux:callout.text>{{ __('host_bulk.messages.publication_action_notice') }}</flux:callout.text>
                </flux:callout>
            @endif
        </flux:card>

        @if($preview)
            <flux:card class="space-y-3">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="eye" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_bulk.preview') }}</span>
                    </span>
                </flux:heading>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <flux:badge color="zinc" icon="check">{{ __('host_bulk.messages.selected_count', ['count' => $preview['selected_count'] ?? 0]) }}</flux:badge>
                    <flux:badge color="green" icon="check-circle">{{ __('host_bulk.messages.affected_count', ['count' => $preview['affected_count'] ?? 0]) }}</flux:badge>
                    <flux:badge color="amber" icon="exclamation-triangle">{{ __('host_bulk.messages.skipped_count', ['count' => $preview['skipped_count'] ?? 0]) }}</flux:badge>
                    <flux:badge color="red" icon="x-mark">{{ __('host_bulk.messages.failed_count', ['count' => $preview['failed_count'] ?? 0]) }}</flux:badge>
                </div>
            </flux:card>
        @endif

        @if($result)
            <flux:card class="space-y-3">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="check-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_bulk.result') }}</span>
                    </span>
                </flux:heading>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <flux:badge color="zinc" icon="check">{{ __('host_bulk.messages.selected_count', ['count' => $result['selected_count'] ?? 0]) }}</flux:badge>
                    <flux:badge color="green" icon="check-circle">{{ __('host_bulk.messages.affected_count', ['count' => $result['affected_count'] ?? 0]) }}</flux:badge>
                    <flux:badge color="amber" icon="exclamation-triangle">{{ __('host_bulk.messages.skipped_count', ['count' => $result['skipped_count'] ?? 0]) }}</flux:badge>
                    <flux:badge color="red" icon="x-mark">{{ __('host_bulk.messages.failed_count', ['count' => $result['failed_count'] ?? 0]) }}</flux:badge>
                </div>
            </flux:card>
        @endif

        <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white/95 p-4 dark:border-zinc-800 dark:bg-zinc-950/95">
            <div class="grid grid-cols-2 gap-2">
                <flux:button type="button" variant="ghost" icon="eye" wire:click="previewBulkAction" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="previewBulkAction">{{ __('host_bulk.preview') }}</span>
                    <span wire:loading wire:target="previewBulkAction">{{ __('host_bulk.buttons.loading') }}</span>
                </flux:button>
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="applyBulkAction">{{ __('host_bulk.actions.apply') }}</span>
                    <span wire:loading wire:target="applyBulkAction">{{ __('host_bulk.buttons.loading') }}</span>
                </flux:button>
            </div>
        </div>
    </form>

    <section class="space-y-3">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="document-duplicate" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_bulk.sections.copy_tools') }}</span>
            </span>
        </flux:heading>
        <div class="grid gap-4 lg:grid-cols-3">
            <flux:card class="space-y-3">
                <flux:heading size="base">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="document-duplicate" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_bulk.actions.clone_room') }}</span>
                    </span>
                </flux:heading>
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.room') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="cloneRoomId">
                        <flux:select.option value="">{{ __('host_bulk.options.choose_room') }}</flux:select.option>
                        @foreach($this->rooms as $room)
                            <flux:select.option value="{{ $room['id'] }}">{{ $room['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="cloneRoomId" />
                </flux:field>
                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="copyPhotos" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="document-duplicate" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.copy_photos') }}</span>
                        </span>
                    </flux:label>
                </flux:field>
                <flux:button type="button" variant="ghost" icon="document-duplicate" wire:click="cloneRoom" wire:loading.attr="disabled">
                    {{ __('host_bulk.actions.clone_room') }}
                </flux:button>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="base">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="document-duplicate" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_bulk.actions.clone_sleeping_place') }}</span>
                    </span>
                </flux:heading>
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.sleeping_place') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="cloneSleepingPlaceId">
                        <flux:select.option value="">{{ __('host_bulk.options.choose_sleeping_place') }}</flux:select.option>
                        @foreach($this->sleepingPlaces as $place)
                            <flux:select.option value="{{ $place['id'] }}">{{ $place['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="cloneSleepingPlaceId" />
                </flux:field>
                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="copyPrice" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.copy_price') }}</span>
                        </span>
                    </flux:label>
                </flux:field>
                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="copyCalendar" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.copy_calendar') }}</span>
                        </span>
                    </flux:label>
                </flux:field>
                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="copyPhotos" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="document-duplicate" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.copy_photos') }}</span>
                        </span>
                    </flux:label>
                </flux:field>
                <flux:button type="button" variant="ghost" icon="document-duplicate" wire:click="cloneSleepingPlace" wire:loading.attr="disabled">
                    {{ __('host_bulk.actions.clone_sleeping_place') }}
                </flux:button>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="base">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="plus" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_bulk.actions.create_identical_places') }}</span>
                    </span>
                </flux:heading>
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.room') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="identicalRoomId">
                        <flux:select.option value="">{{ __('host_bulk.options.choose_room') }}</flux:select.option>
                        @foreach($this->rooms as $room)
                            <flux:select.option value="{{ $room['id'] }}">{{ $room['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="identicalRoomId" />
                </flux:field>
                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="plus" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.identical_count') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="number" inputmode="numeric" min="1" max="25" wire:model.blur="identicalCount" icon="plus" />
                        <flux:error name="identicalCount" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="users" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.max_guests') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="number" inputmode="numeric" min="1" max="10" wire:model.blur="identicalMaxGuests" icon="users" />
                        <flux:error name="identicalMaxGuests" />
                    </flux:field>
                </div>
                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="pencil-square" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_bulk.fields.identical_name') }}</span>
                        </span>
                    </flux:label>
                    <flux:input wire:model.blur="identicalName" icon="pencil-square" />
                    <flux:error name="identicalName" />
                </flux:field>
                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.price') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="number" inputmode="decimal" step="0.01" min="0" wire:model.blur="identicalPrice" icon="banknotes" />
                        <flux:error name="identicalPrice" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_bulk.fields.currency') }}</span>
                            </span>
                        </flux:label>
                        <flux:input maxlength="3" wire:model.blur="identicalCurrency" icon="banknotes" />
                        <flux:error name="identicalCurrency" />
                    </flux:field>
                </div>
                <flux:button type="button" variant="ghost" icon="plus" wire:click="createIdenticalPlaces" wire:loading.attr="disabled">
                    {{ __('host_bulk.actions.create_identical_places') }}
                </flux:button>
            </flux:card>
        </div>
    </section>
</x-ui.page>
