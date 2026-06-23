<div>
    @auth
        @if($this->existingEntry)
            <flux:card class="space-y-2">
                <flux:text size="sm" class="text-green-600 font-medium">{{ __('search.waitlist.on_list') }}</flux:text>
                <flux:text size="sm" class="text-zinc-500">
                    {{ $this->existingEntry->desired_check_in }} - {{ $this->existingEntry->desired_check_out }}
                </flux:text>
                <flux:button size="sm" variant="ghost" wire:click="leave" wire:confirm="{{ __('search.waitlist.leave_confirmation') }}" icon="magnifying-glass">
                    {{ __('search.waitlist.leave') }}
                </flux:button>
            </flux:card>
        @elseif($showForm)
            <flux:card class="space-y-4">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.waitlist.join_title') }}</span>
                    </span>
                </flux:heading>
                <form wire:submit="join" class="space-y-3">
                                        <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('search.waitlist.desired_check_in') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model="desiredCheckIn" :error="$errors->first('desiredCheckIn')" icon="calendar-days" />
                        <flux:error name="desiredCheckIn" />
                    </flux:field>
                                        <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('search.waitlist.desired_check_out') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model="desiredCheckOut" :error="$errors->first('desiredCheckOut')" icon="calendar-days" />
                        <flux:error name="desiredCheckOut" />
                    </flux:field>
                                        <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('search.waitlist.max_price') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="number" wire:model="maxPrice" step="0.01" icon="banknotes" />
                        <flux:error name="maxPrice" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model="autoRequest" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('search.waitlist.auto_request_when_available') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="autoRequest" />
                    </flux:field>
                    <div class="flex gap-3">
                        <flux:button type="submit" variant="primary" size="sm" icon="check">{{ __('app.actions.join') }}</flux:button>
                        <flux:button wire:click="$set('showForm', false)" variant="ghost" size="sm" icon="x-mark">{{ __('app.actions.cancel') }}</flux:button>
                    </div>
                </form>
            </flux:card>
        @else
            <flux:button size="sm" variant="ghost" icon="eye" wire:click="$set('showForm', true)">
                {{ __('search.waitlist.join_title') }}
            </flux:button>
        @endif
    @endauth
</div>
