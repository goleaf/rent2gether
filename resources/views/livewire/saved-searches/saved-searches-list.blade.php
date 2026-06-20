<div class="mx-auto max-w-5xl space-y-5 px-4 py-4 pb-24 sm:px-6 lg:py-6">
    <section class="space-y-2">
        <flux:badge color="emerald">{{ __('decision.saved.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">{{ __('decision.saved.title') }}</flux:heading>
        <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('decision.saved.helper') }}</flux:text>
    </section>

    @if(session('decision-saved-search-status'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('decision-saved-search-status') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="lg">
                    {{ $editingId ? __('decision.saved.edit_title') : __('decision.saved.create_title') }}
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('decision.saved.form_helper') }}</flux:text>
            </div>

            @if($editingId)
                <flux:button type="button" size="sm" variant="ghost" wire:click="cancelEdit">
                    {{ __('app.actions.cancel') }}
                </flux:button>
            @endif
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('decision.saved.fields.name') }}</flux:label>
                <flux:input wire:model.blur="name" placeholder="{{ __('decision.saved.placeholders.name') }}" />
                <flux:error name="name" />
            </flux:field>

            <flux:field class="relative">
                <flux:label>{{ __('decision.saved.fields.city') }}</flux:label>
                <flux:input wire:model.live.debounce.500ms="cityQuery" placeholder="{{ __('decision.saved.placeholders.city') }}" />
                <flux:error name="cityQuery" />

                @if($cityOptions->isNotEmpty())
                    <div class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                        @foreach($cityOptions as $city)
                            <flux:button
                                type="button"
                                variant="ghost"
                                class="h-auto w-full justify-start whitespace-normal rounded-none px-3 py-2 text-left"
                                wire:click="selectCity({{ $city->id }})"
                            >
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $city->name }}</span>
                                <span class="text-zinc-500">{{ __('decision.saved.city_population', ['count' => number_format((int) $city->population)]) }}</span>
                            </flux:button>
                        @endforeach
                    </div>
                @elseif(strlen($cityQuery) >= 2 && ! $cityId)
                    <flux:text size="sm" class="text-zinc-500">{{ __('decision.saved.city_empty') }}</flux:text>
                @endif
            </flux:field>

            <flux:field>
                <flux:label>{{ __('decision.saved.fields.district') }}</flux:label>
                <flux:input wire:model.blur="district" placeholder="{{ __('decision.saved.placeholders.district') }}" />
                <flux:error name="district" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('decision.saved.fields.currency') }}</flux:label>
                <flux:input maxlength="3" wire:model.blur="currency" />
                <flux:error name="currency" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('decision.saved.fields.check_in') }}</flux:label>
                <flux:input type="date" wire:model.change="checkIn" min="{{ now()->toDateString() }}" />
                <flux:error name="checkIn" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('decision.saved.fields.check_out') }}</flux:label>
                <flux:input type="date" wire:model.change="checkOut" min="{{ $checkIn ?: now()->toDateString() }}" />
                <flux:error name="checkOut" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('decision.saved.fields.price_min') }}</flux:label>
                <flux:input type="number" step="0.01" min="0" inputmode="decimal" wire:model.blur="priceMin" />
                <flux:error name="priceMin" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('decision.saved.fields.price_max') }}</flux:label>
                <flux:input type="number" step="0.01" min="0" inputmode="decimal" wire:model.blur="priceMax" />
                <flux:error name="priceMax" />
            </flux:field>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:checkbox wire:model.change="flexibleDates" label="{{ __('decision.saved.fields.flexible_dates') }}" />
            <flux:checkbox wire:model.change="notifyNewMatches" label="{{ __('decision.saved.fields.notify_new_matches') }}" />
            <flux:checkbox wire:model.change="notifyPriceDrop" label="{{ __('decision.saved.fields.notify_price_drop') }}" />
            <flux:checkbox wire:model.change="notifyAvailability" label="{{ __('decision.saved.fields.notify_availability') }}" />
        </div>

        <div class="space-y-2">
            <flux:heading size="sm">{{ __('decision.saved.filters_title') }}</flux:heading>
            <div class="grid gap-2 sm:grid-cols-2">
                <flux:checkbox wire:model.change="filters.wifi" label="{{ __('search.filters_flags.wifi') }}" />
                <flux:checkbox wire:model.change="filters.kitchen" label="{{ __('search.filters_flags.kitchen') }}" />
                <flux:checkbox wire:model.change="filters.locker" label="{{ __('search.filters_flags.locker') }}" />
                <flux:checkbox wire:model.change="filters.quiet_hours" label="{{ __('search.filters_flags.quiet_hours') }}" />
            </div>
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
                {{ $editingId ? __('decision.saved.update_action') : __('decision.saved.create_action') }}
            </flux:button>
        </div>
    </flux:card>

    <section class="space-y-3" aria-labelledby="saved-searches-list-title">
        <flux:heading id="saved-searches-list-title" size="lg">{{ __('decision.saved.list_title') }}</flux:heading>

        <div wire:loading.delay wire:target="save,delete,toggleNotifications,runSearch,edit" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            {{ __('decision.common.updating') }}
        </div>

        <div class="grid gap-3">
            @forelse($searches as $search)
                <flux:card class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 space-y-1">
                            <flux:heading size="sm" class="truncate">{{ $search->name }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500">
                                {{ $search->city ?: $search->cityModel?->name ?: __('decision.saved.any_city') }}
                                @if($search->district)
                                    - {{ $search->district }}
                                @endif
                            </flux:text>
                        </div>

                        <flux:badge color="{{ $search->notify_new_places || $search->notify_price_drop || $search->notify_available ? 'green' : 'zinc' }}">
                            {{ $search->notify_new_places || $search->notify_price_drop || $search->notify_available ? __('decision.saved.notifications_on') : __('decision.saved.notifications_off') }}
                        </flux:badge>
                    </div>

                    <div class="grid gap-2 text-sm sm:grid-cols-3">
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.saved.fields.check_in') }}</div>
                            <div class="font-medium">{{ $search->check_in?->toFormattedDateString() ?: __('decision.saved.flexible') }}</div>
                        </div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.saved.fields.check_out') }}</div>
                            <div class="font-medium">{{ $search->check_out?->toFormattedDateString() ?: __('decision.saved.flexible') }}</div>
                        </div>
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-zinc-500">{{ __('decision.saved.budget') }}</div>
                            <div class="font-medium">
                                {{ $search->price_min ?? 0 }} - {{ $search->price_max ?? __('decision.saved.no_max') }} {{ $search->currency ?: 'EUR' }}
                            </div>
                        </div>
                    </div>

                    @if($search->filters_json)
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_keys($search->filters_json) as $filter)
                                <flux:badge size="sm">{{ __('search.filters_flags.'.$filter) }}</flux:badge>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        <flux:button type="button" size="sm" icon="magnifying-glass" wire:click="runSearch({{ $search->id }})">
                            {{ __('app.actions.search') }}
                        </flux:button>
                        <flux:button type="button" size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $search->id }})">
                            {{ __('app.actions.edit') }}
                        </flux:button>
                        <flux:button type="button" size="sm" variant="ghost" icon="bell" wire:click="toggleNotifications({{ $search->id }})">
                            {{ __('decision.saved.toggle_notifications') }}
                        </flux:button>
                        <flux:button
                            type="button"
                            size="sm"
                            variant="ghost"
                            icon="trash"
                            wire:click="delete({{ $search->id }})"
                            wire:confirm="{{ __('decision.saved.delete_confirmation') }}"
                        >
                            {{ __('app.actions.remove') }}
                        </flux:button>
                    </div>
                </flux:card>
            @empty
                <flux:card>
                    <div class="space-y-2 text-center">
                        <flux:heading size="lg">{{ __('decision.saved.empty_title') }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('decision.saved.empty_helper') }}</flux:text>
                    </div>
                </flux:card>
            @endforelse
        </div>
    </section>
</div>
