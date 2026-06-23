<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald" icon="check-circle">{{ __('host.sleeping_places.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host.sleeping_places.heading', ['room' => $this->room->title]) }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.sleeping_places.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <div class="sticky top-3 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-sm backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
        <flux:button
            class="w-full"
            href="{{ route('host.sleeping-places.create', ['locale' => app()->getLocale(), 'room' => $roomId]) }}"
            variant="primary"
            icon="plus"
            wire:navigate
        >
            {{ __('host.sleeping_places.actions.add') }}
        </flux:button>
    </div>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('host.sleeping_places.bulk.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.sleeping_places.bulk.helper') }}</flux:text>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('host.sleeping_places.fields.bulk_count') }}</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="bulkCount" icon="numbered-list" />
                <flux:error name="bulkCount" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('host.sleeping_places.fields.bulk_title_prefix') }}</flux:label>
                <flux:input wire:model.blur="bulkTitlePrefix" icon="tag" />
                <flux:error name="bulkTitlePrefix" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('host.sleeping_places.fields.bulk_type') }}</flux:label>
                <flux:select wire:model.change="bulkType">
                    @foreach($this->typeOptions() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="bulkType" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('host.sleeping_places.fields.bulk_base_price') }}</flux:label>
                <flux:input type="number" inputmode="decimal" step="0.01" wire:model.blur="bulkBasePrice" icon="banknotes" />
                <flux:error name="bulkBasePrice" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('host.sleeping_places.fields.bulk_currency') }}</flux:label>
                <flux:input maxlength="3" wire:model.blur="bulkCurrency" icon="banknotes" />
                <flux:error name="bulkCurrency" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('host.sleeping_places.fields.bulk_min_nights') }}</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="bulkMinNights" icon="numbered-list" />
                <flux:error name="bulkMinNights" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('host.sleeping_places.fields.bulk_max_guests') }}</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="bulkMaxGuests" icon="users" />
                <flux:error name="bulkMaxGuests" />
            </flux:field>
        </div>

        <flux:button type="button" variant="primary" wire:click="bulkCreate" class="w-full data-loading:opacity-70" icon="check">
            <span wire:loading.remove wire:target="bulkCreate">{{ __('host.sleeping_places.actions.bulk_create') }}</span>
            <span wire:loading wire:target="bulkCreate">{{ __('account.actions.saving') }}</span>
        </flux:button>
    </flux:card>

    <div class="space-y-4">
        @forelse($this->sleepingPlaces as $place)
            <flux:card class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:heading size="sm">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ $place['title'] }}</span>
                                </span>
                            </flux:heading>
                            <flux:badge size="sm" icon="home-modern">{{ $place['status_label'] }}</flux:badge>
                        </div>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ $place['type_label'] }} &middot; {{ $place['currency'] }} {{ $place['price'] }} &middot; {{ __('host.sleeping_place_wizard.fields.max_guests') }}: {{ $place['max_guests'] }}
                        </flux:text>
                    </div>
                    <div class="shrink-0 text-right">
                        <div class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $place['readiness_percent'] }}%</div>
                        <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ __('host.listings.readiness.label') }}</flux:text>
                    </div>
                </div>

                <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $place['readiness_percent'] }}%"></div>
                </div>

                @if($place['description'])
                    <flux:text size="sm">{{ $place['description'] }}</flux:text>
                @else
                    <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                        {{ __('host.sleeping_places.empty_description') }}
                    </div>
                @endif

                @if($place['special_conditions'])
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $place['special_conditions'] }}</flux:text>
                @endif

                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host.sleeping_place_wizard.readiness.title') }}</span>
                        </span>
                    </flux:heading>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach($place['readiness'] as $item)
                            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                                <span>{{ $item['label'] }}</span>
                                @if($item['done'])
                                    <flux:badge size="sm" color="green" icon="check-circle">{{ __('host.sleeping_place_wizard.readiness.done') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" icon="check-circle">{{ __('host.sleeping_place_wizard.readiness.later') }}</flux:badge>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <flux:button
                        size="sm"
                        href="{{ route('host.sleeping-places.edit', ['locale' => app()->getLocale(), 'room' => $roomId, 'sleepingPlace' => $place['id']]) }}"
                        icon="home-modern"
                        wire:navigate
                    >
                        {{ __('app.actions.edit') }}
                    </flux:button>
                    <flux:button
                        size="sm"
                        type="button"
                        variant="ghost"
                        wire:click="duplicateSleepingPlace({{ $place['id'] }})"
                        class="data-loading:opacity-70"
                     icon="map-pin">
                        {{ __('host.sleeping_places.actions.duplicate') }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:card class="space-y-3 text-center">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.sleeping_places.empty_title') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.sleeping_places.empty_text') }}</flux:text>
                <flux:button
                    href="{{ route('host.sleeping-places.create', ['locale' => app()->getLocale(), 'room' => $roomId]) }}"
                    variant="primary"
                    icon="plus"
                    wire:navigate
                >
                    {{ __('host.sleeping_places.actions.add') }}
                </flux:button>
            </flux:card>
        @endforelse
    </div>

    <flux:button href="{{ route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $this->room->property_id]) }}" variant="ghost" wire:navigate icon="arrow-left">
        {{ __('host.sleeping_places.actions.back_to_rooms') }}
    </flux:button>
</x-ui.page>
