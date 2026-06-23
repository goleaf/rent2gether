<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald" icon="check-circle">{{ __('decision.waitlist.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('decision.waitlist.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('decision.waitlist.helper') }}</flux:text>
    </section>

    @if(session('decision-waitlist-status'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('decision-waitlist-status') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('decision.waitlist.create_title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('decision.waitlist.form_helper') }}</flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('decision.waitlist.fields.sleeping_place') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="1" inputmode="numeric" wire:model.blur="sleepingPlaceId" placeholder="{{ __('decision.waitlist.place_placeholder') }}" icon="home-modern" />
                <flux:error name="sleepingPlaceId" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('decision.waitlist.fields.max_price') }}</span>
    </span>
</flux:label>
                <flux:input type="number" step="0.01" min="0" inputmode="decimal" wire:model.blur="maxPrice" icon="banknotes" />
                <flux:error name="maxPrice" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('decision.waitlist.fields.desired_check_in') }}</span>
    </span>
</flux:label>
                <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="desiredCheckIn" icon="calendar-days" />
                <flux:error name="desiredCheckIn" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('decision.waitlist.fields.desired_check_out') }}</span>
    </span>
</flux:label>
                <flux:input type="date" min="{{ $desiredCheckIn ?: now()->toDateString() }}" wire:model.change="desiredCheckOut" icon="calendar-days" />
                <flux:error name="desiredCheckOut" />
            </flux:field>
        </div>

        @if($favoriteOptions->isNotEmpty())
            <div class="space-y-2">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('decision.waitlist.favorite_places') }}</span>
                    </span>
                </flux:heading>
                <div class="flex gap-2 overflow-x-auto pb-1">
                    @foreach($favoriteOptions as $option)
                        <flux:button type="button" size="sm" variant="ghost" wire:click="$set('sleepingPlaceId', {{ $option['id'] }})" icon="home-modern">
                            {{ $option['title'] }}
                        </flux:button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="notifyAvailable" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('decision.waitlist.fields.notify_available') }}</span>
                    </span>
                </flux:label>
                <flux:error name="notifyAvailable" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="notifyPriceDrop" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('decision.waitlist.fields.notify_price_drop') }}</span>
                    </span>
                </flux:label>
                <flux:error name="notifyPriceDrop" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="readyToBook" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="check-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('decision.waitlist.fields.ready_to_book') }}</span>
                    </span>
                </flux:label>
                <flux:error name="readyToBook" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="autoRequest" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="check-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('decision.waitlist.fields.auto_request') }}</span>
                    </span>
                </flux:label>
                <flux:error name="autoRequest" />
            </flux:field>
        </div>

        <div class="sticky bottom-20 -mx-4 border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950 sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:dark:bg-transparent">
            <flux:button
                type="button"
                variant="primary"
                class="w-full sm:w-auto"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
             icon="clock">
                {{ __('decision.waitlist.create_action') }}
            </flux:button>
        </div>
    </flux:card>

    <section class="space-y-3" aria-labelledby="waitlist-items-title">
        <flux:heading id="waitlist-items-title" size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('decision.waitlist.list_title') }}</span>
            </span>
        </flux:heading>

        <div wire:loading.delay wire:target="save,remove" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            {{ __('decision.common.updating') }}
        </div>

        <div class="grid gap-3">
            @forelse($items as $card)
                <flux:card class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 space-y-1">
                            <a href="{{ $card['url'] }}" wire:navigate>
                                <flux:heading size="sm" class="truncate hover:text-emerald-700 dark:hover:text-emerald-300">
                                    {{ $card['title'] }}
                                </flux:heading>
                            </a>
                            <flux:text size="sm" class="text-zinc-500">{{ $card['location'] }}</flux:text>
                        </div>

                        <flux:button
                            type="button"
                            size="sm"
                            variant="ghost"
                            icon="clock"
                            wire:click="remove({{ $card['item']->id }})"
                            wire:confirm="{{ __('decision.waitlist.remove_confirmation') }}"
                            aria-label="{{ __('decision.waitlist.remove') }}"
                        />
                    </div>

                    <div class="flex flex-wrap gap-1">
                        <flux:badge size="sm" icon="home-modern">{{ $card['room_type'] }}</flux:badge>
                        <flux:badge size="sm" icon="home-modern">{{ $card['sleeping_place_type'] }}</flux:badge>
                        @if($card['item']->auto_request)
                            <flux:badge size="sm" color="blue" icon="information-circle">{{ __('decision.waitlist.auto_request_badge') }}</flux:badge>
                        @endif
                    </div>

                    <div class="grid gap-2 text-sm sm:grid-cols-4">
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.waitlist.fields.desired_check_in') }}</div>
                            <div class="font-medium">{{ $card['item']->desired_check_in?->toFormattedDateString() }}</div>
                        </div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.waitlist.fields.desired_check_out') }}</div>
                            <div class="font-medium">{{ $card['item']->desired_check_out?->toFormattedDateString() }}</div>
                        </div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.waitlist.current_price') }}</div>
                            <div class="font-medium">{{ $card['current_price'] }}</div>
                        </div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.waitlist.max_price') }}</div>
                            <div class="font-medium">{{ $card['max_price'] }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if($card['item']->notify_available)
                            <flux:badge color="green" icon="check-circle">{{ __('decision.waitlist.notify_available_badge') }}</flux:badge>
                        @endif
                        @if($card['item']->notify_price_drop)
                            <flux:badge color="green" icon="check-circle">{{ __('decision.waitlist.notify_price_badge') }}</flux:badge>
                        @endif
                    </div>
                </flux:card>
            @empty
                <flux:card>
                    <div class="space-y-2 text-center">
                        <flux:heading size="lg">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('decision.waitlist.empty_title') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('decision.waitlist.empty_helper') }}</flux:text>
                    </div>
                </flux:card>
            @endforelse
        </div>
    </section>
</x-ui.page>
