<div class="mx-auto max-w-5xl space-y-5 px-4 py-4 pb-24 sm:px-6 lg:py-6">
    <section class="space-y-2">
        <flux:badge color="emerald">{{ __('decision.waitlist.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">{{ __('decision.waitlist.title') }}</flux:heading>
        <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('decision.waitlist.helper') }}</flux:text>
    </section>

    @if(session('decision-waitlist-status'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('decision-waitlist-status') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('decision.waitlist.create_title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('decision.waitlist.form_helper') }}</flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('decision.waitlist.fields.sleeping_place') }}</flux:label>
                <flux:input type="number" min="1" inputmode="numeric" wire:model.blur="sleepingPlaceId" placeholder="{{ __('decision.waitlist.place_placeholder') }}" />
                <flux:error name="sleepingPlaceId" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('decision.waitlist.fields.max_price') }}</flux:label>
                <flux:input type="number" step="0.01" min="0" inputmode="decimal" wire:model.blur="maxPrice" />
                <flux:error name="maxPrice" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('decision.waitlist.fields.desired_check_in') }}</flux:label>
                <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="desiredCheckIn" />
                <flux:error name="desiredCheckIn" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('decision.waitlist.fields.desired_check_out') }}</flux:label>
                <flux:input type="date" min="{{ $desiredCheckIn ?: now()->toDateString() }}" wire:model.change="desiredCheckOut" />
                <flux:error name="desiredCheckOut" />
            </flux:field>
        </div>

        @if($favoriteOptions->isNotEmpty())
            <div class="space-y-2">
                <flux:heading size="sm">{{ __('decision.waitlist.favorite_places') }}</flux:heading>
                <div class="flex gap-2 overflow-x-auto pb-1">
                    @foreach($favoriteOptions as $option)
                        <flux:button type="button" size="sm" variant="ghost" wire:click="$set('sleepingPlaceId', {{ $option['id'] }})">
                            {{ $option['title'] }}
                        </flux:button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:checkbox wire:model.change="notifyAvailable" label="{{ __('decision.waitlist.fields.notify_available') }}" />
            <flux:checkbox wire:model.change="notifyPriceDrop" label="{{ __('decision.waitlist.fields.notify_price_drop') }}" />
            <flux:checkbox wire:model.change="readyToBook" label="{{ __('decision.waitlist.fields.ready_to_book') }}" />
            <flux:checkbox wire:model.change="autoRequest" label="{{ __('decision.waitlist.fields.auto_request') }}" />
        </div>

        <div class="sticky bottom-20 -mx-4 border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950 sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:dark:bg-transparent">
            <flux:button
                type="button"
                variant="primary"
                class="w-full sm:w-auto"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                {{ __('decision.waitlist.create_action') }}
            </flux:button>
        </div>
    </flux:card>

    <section class="space-y-3" aria-labelledby="waitlist-items-title">
        <flux:heading id="waitlist-items-title" size="lg">{{ __('decision.waitlist.list_title') }}</flux:heading>

        <div wire:loading.delay wire:target="save,remove" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            {{ __('decision.common.updating') }}
        </div>

        <div class="grid gap-3">
            @forelse($items as $card)
                @php($item = $card['item'])

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
                            icon="x-mark"
                            wire:click="remove({{ $item->id }})"
                            wire:confirm="{{ __('decision.waitlist.remove_confirmation') }}"
                            aria-label="{{ __('decision.waitlist.remove') }}"
                        />
                    </div>

                    <div class="flex flex-wrap gap-1">
                        <flux:badge size="sm">{{ $card['room_type'] }}</flux:badge>
                        <flux:badge size="sm">{{ $card['sleeping_place_type'] }}</flux:badge>
                        @if($item->auto_request)
                            <flux:badge size="sm" color="blue">{{ __('decision.waitlist.auto_request_badge') }}</flux:badge>
                        @endif
                    </div>

                    <div class="grid gap-2 text-sm sm:grid-cols-4">
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.waitlist.fields.desired_check_in') }}</div>
                            <div class="font-medium">{{ $item->desired_check_in?->toFormattedDateString() }}</div>
                        </div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.waitlist.fields.desired_check_out') }}</div>
                            <div class="font-medium">{{ $item->desired_check_out?->toFormattedDateString() }}</div>
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
                        @if($item->notify_available)
                            <flux:badge color="green">{{ __('decision.waitlist.notify_available_badge') }}</flux:badge>
                        @endif
                        @if($item->notify_price_drop)
                            <flux:badge color="green">{{ __('decision.waitlist.notify_price_badge') }}</flux:badge>
                        @endif
                    </div>
                </flux:card>
            @empty
                <flux:card>
                    <div class="space-y-2 text-center">
                        <flux:heading size="lg">{{ __('decision.waitlist.empty_title') }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('decision.waitlist.empty_helper') }}</flux:text>
                    </div>
                </flux:card>
            @endforelse
        </div>
    </section>
</div>
