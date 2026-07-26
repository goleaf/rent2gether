<div class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('waitlist.edit') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('waitlist.edit_helper') }}</flux:text>
    </div>

    @if($this->saved)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ __('waitlist.messages.saved') }}</flux:callout.text>
        </flux:callout>
    @endif

    <div wire:loading.delay wire:target="save" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
        {{ __('waitlist.states.saving') }}
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.check_in') }}</span>
                </span>
            </flux:label>
            <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="desiredCheckIn" icon="calendar-days" />
            <flux:error name="desiredCheckIn" />
        </flux:field>

        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.check_out') }}</span>
                </span>
            </flux:label>
            <flux:input type="date" min="{{ $desiredCheckIn ?: now()->toDateString() }}" wire:model.change="desiredCheckOut" icon="calendar-days" />
            <flux:error name="desiredCheckOut" />
        </flux:field>

        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="users" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.guests_count') }}</span>
                </span>
            </flux:label>
            <flux:input type="number" min="1" inputmode="numeric" wire:model.change="guestsCount" icon="users" />
            <flux:error name="guestsCount" />
        </flux:field>

        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.expires_at') }}</span>
                </span>
            </flux:label>
            <flux:input type="datetime-local" wire:model.blur="expiresAt" icon="calendar-days" />
            <flux:error name="expiresAt" />
        </flux:field>

        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.max_price') }}</span>
                </span>
            </flux:label>
            <flux:input type="number" min="0" step="0.01" inputmode="decimal" wire:model.blur="maxPricePerNight" icon="banknotes" />
            <flux:error name="maxPricePerNight" />
        </flux:field>

        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.max_total_price') }}</span>
                </span>
            </flux:label>
            <flux:input type="number" min="0" step="0.01" inputmode="decimal" wire:model.blur="maxTotalPrice" icon="banknotes" />
            <flux:error name="maxTotalPrice" />
        </flux:field>

        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.max_deposit') }}</span>
                </span>
            </flux:label>
            <flux:input type="number" min="0" step="0.01" inputmode="decimal" wire:model.blur="maxDeposit" icon="banknotes" />
            <flux:error name="maxDeposit" />
        </flux:field>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field variant="inline">
            <flux:checkbox wire:model.change="readyToBookImmediately" />
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="check-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.ready_to_book') }}</span>
                </span>
            </flux:label>
            <flux:error name="readyToBookImmediately" />
        </flux:field>

        <flux:field variant="inline">
            <flux:checkbox wire:model.change="autoSendRequest" />
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="paper-airplane" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.auto_send_request') }}</span>
                </span>
            </flux:label>
            <flux:error name="autoSendRequest" />
        </flux:field>

        <flux:field variant="inline">
            <flux:checkbox wire:model.change="notifyAvailable" />
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.notify_available') }}</span>
                </span>
            </flux:label>
            <flux:error name="notifyAvailable" />
        </flux:field>

        <flux:field variant="inline">
            <flux:checkbox wire:model.change="notifyPriceDrop" />
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.notify_price_drop') }}</span>
                </span>
            </flux:label>
            <flux:error name="notifyPriceDrop" />
        </flux:field>
    </div>

    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('waitlist.guest_message') }}</span>
            </span>
        </flux:label>
        <flux:textarea rows="3" wire:model.blur="guestMessage" />
        <flux:error name="guestMessage" />
    </flux:field>

    <div class="sticky bottom-20 -mx-4 border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950 sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:dark:bg-transparent">
        <flux:button type="button" variant="primary" class="w-full sm:w-auto" wire:click="save" wire:loading.attr="disabled" wire:target="save" icon="clock">
            {{ __('waitlist.actions.save') }}
        </flux:button>
    </div>
</div>
