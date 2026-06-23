<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="check-circle">{{ $page['eyebrow'] }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $page['title'] }}</span>
                </span>
            </flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                {{ $page['helper'] }}
            </flux:text>
        </div>
    </section>

    @if(session('availability-status'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('availability-status') }}</flux:callout.text>
        </flux:callout>
    @endif

    @if(empty($this->sleepingPlaces))
        <flux:card class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <flux:icon name="{{ $page['icon'] }}" class="size-5" />
                </div>
                <div class="min-w-0 space-y-1">
                    <flux:heading size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $page['empty_title'] }}</span>
                        </span>
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $page['empty_text'] }}</flux:text>
                </div>
            </div>
            <div class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                {{ $page['note'] }}
            </div>
            <flux:button href="{{ route('host.properties.index', ['locale' => app()->getLocale()]) }}" variant="primary" wire:navigate icon="home-modern">
                {{ $page['action'] }}
            </flux:button>
        </flux:card>
    @else
        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('availability.calendar.summary.occupancy') }}</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('availability.calendar.occupancy_value', ['percent' => $summary['occupancy_percentage']]) }}
                </p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('availability.calendar.summary.places') }}</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $summary['places_count'] }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('availability.calendar.summary.occupied_nights') }}</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $summary['occupied_nights'] }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('availability.calendar.summary.available_days') }}</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $summary['available_days'] }}</p>
            </div>
        </section>

        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('availability.calendar.sections.filters') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('availability.calendar.filters_helper') }}</flux:text>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <flux:field>
                    <flux:label>{{ __('availability.calendar.fields.property') }}</flux:label>
                    <flux:select wire:model.change="selectedPropertyId">
                        @foreach($this->properties as $property)
                            <flux:select.option value="{{ $property['id'] }}">{{ $property['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('availability.calendar.fields.room') }}</flux:label>
                    <flux:select wire:model.change="selectedRoomId">
                        @foreach($this->rooms as $room)
                            <flux:select.option value="{{ $room['id'] }}">{{ $room['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('availability.calendar.fields.sleeping_place') }}</flux:label>
                    <flux:select wire:model.change="selectedSleepingPlaceId">
                        @foreach($this->sleepingPlaces as $place)
                            <flux:select.option value="{{ $place['id'] }}">{{ $place['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="selectedSleepingPlaceId" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('availability.calendar.fields.view_mode') }}</flux:label>
                    <flux:select wire:model.change="viewMode">
                        <flux:select.option value="list">{{ __('availability.calendar.view_modes.list') }}</flux:select.option>
                        <flux:select.option value="month">{{ __('availability.calendar.view_modes.month') }}</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>
        </flux:card>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <section class="space-y-4">
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <flux:button type="button" size="sm" variant="ghost" icon="arrow-left" wire:click="previousMonth">
                            {{ __('availability.calendar.actions.previous_month') }}
                        </flux:button>
                        <flux:heading size="sm">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ $monthLabel }}</span>
                            </span>
                        </flux:heading>
                        <flux:button type="button" size="sm" variant="ghost" icon="arrow-right" wire:click="nextMonth">
                            {{ __('availability.calendar.actions.next_month') }}
                        </flux:button>
                    </div>

                    @if($viewMode === 'month')
                        <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            @foreach(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $weekday)
                                <div>{{ __('availability.calendar.weekdays.'.$weekday) }}</div>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            @foreach($this->calendarDays as $day)
                                <flux:button
                                    type="button"
                                    variant="outline"
                                    wire:click="selectDate('{{ $day['date'] }}')"
                                    class="h-auto min-h-20 w-full flex-col items-start justify-start whitespace-normal px-1 py-2 text-left text-xs data-loading:opacity-70 {{ $day['is_current_month'] ? '' : 'opacity-60' }}"
                                 icon="calendar-days">
                                    <span class="block font-medium {{ $day['is_today'] ? 'text-emerald-700 dark:text-emerald-300' : '' }}">{{ $day['day'] }}</span>
                                    <span class="mt-1 block truncate rounded bg-zinc-100 px-1 py-0.5 text-[10px] dark:bg-zinc-800">
                                        {{ $day['status_label'] }}
                                    </span>
                                    @if($day['booking'])
                                        <span class="mt-1 block truncate text-[10px] font-medium text-blue-700 dark:text-blue-300">
                                            {{ $day['booking']['guest'] }}
                                        </span>
                                    @endif
                                    @if($day['price_override'])
                                        <span class="mt-1 block text-[10px] text-zinc-500 dark:text-zinc-400">
                                            {{ __('availability.calendar.price_short', ['price' => $day['price_override']]) }}
                                        </span>
                                    @endif
                                </flux:button>
                            @endforeach
                        </div>
                    @else
                        <div class="space-y-2">
                            <flux:heading size="sm">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('availability.calendar.sections.date_list') }}</span>
                                </span>
                            </flux:heading>
                            @foreach($this->listDays as $day)
                                <flux:button
                                    type="button"
                                    variant="outline"
                                    wire:click="selectDate('{{ $day['date'] }}')"
                                    class="h-auto w-full justify-start whitespace-normal px-3 py-3 text-left text-sm data-loading:opacity-70"
                                 icon="key">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-medium">{{ \Carbon\CarbonImmutable::parse($day['date'])->translatedFormat('d M') }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $day['check_in_allowed'] ? __('availability.calendar.check_in_yes') : __('availability.calendar.check_in_no') }}
                                                <span aria-hidden="true">·</span>
                                                {{ $day['check_out_allowed'] ? __('availability.calendar.check_out_yes') : __('availability.calendar.check_out_no') }}
                                            </p>
                                            @if($day['booking'])
                                                <p class="mt-1 truncate text-xs font-medium text-blue-700 dark:text-blue-300">
                                                    {{ __('availability.calendar.booking_guest', ['guest' => $day['booking']['guest'], 'count' => $day['booking']['guests_count']]) }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <flux:badge size="sm" color="{{ $day['color'] }}" icon="user">{{ $day['status_label'] }}</flux:badge>
                                            @if($day['price_override'])
                                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('availability.calendar.price_short', ['price' => $day['price_override']]) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </flux:button>
                            @endforeach
                        </div>
                    @endif
                </flux:card>

                <flux:card class="space-y-4">
                    <div class="space-y-1">
                        <flux:heading size="sm">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('availability.calendar.overview.title') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('availability.calendar.overview.helper') }}</flux:text>
                    </div>

                    <flux:accordion transition>
                        @foreach($this->hierarchyOverview as $property)
                            <flux:accordion.item :expanded="(int) $property['id'] === (int) $selectedPropertyId">
                                <flux:accordion.heading>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate font-medium">{{ $property['label'] }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ __('availability.calendar.overview.property_line', ['rooms' => $property['rooms_count'], 'places' => $property['places_count']]) }}
                                            </p>
                                        </div>
                                        <flux:badge size="sm" color="blue" icon="home-modern">
                                            {{ __('availability.calendar.occupancy_value', ['percent' => $property['occupancy_percentage']]) }}
                                        </flux:badge>
                                    </div>
                                </flux:accordion.heading>

                                <flux:accordion.content>
                                    <div class="space-y-2">
                                        @foreach($property['rooms'] as $room)
                                            <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-medium">{{ $room['label'] }}</p>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                            {{ __('availability.calendar.overview.room_line', ['places' => $room['places_count']]) }}
                                                        </p>
                                                    </div>
                                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                                        {{ __('availability.calendar.occupancy_value', ['percent' => $room['occupancy_percentage']]) }}
                                                    </span>
                                                </div>
                                                <div class="mt-2 space-y-1">
                                                    @foreach($room['places'] as $place)
                                                        <div class="flex items-center justify-between gap-3 text-xs">
                                                            <span class="min-w-0 truncate text-zinc-600 dark:text-zinc-300">{{ $place['label'] }}</span>
                                                            <span class="shrink-0 text-zinc-500 dark:text-zinc-400">
                                                                {{ __('availability.calendar.occupancy_value', ['percent' => $place['occupancy_percentage']]) }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </flux:accordion.content>
                            </flux:accordion.item>
                        @endforeach
                    </flux:accordion>
                </flux:card>
            </section>

            <aside class="space-y-4">
                <flux:card class="space-y-4">
                    <div class="space-y-1">
                        <flux:heading size="sm">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('availability.calendar.sections.range_actions') }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('availability.calendar.range_helper') }}</flux:text>
                    </div>

                    <div class="grid gap-3">
                        <flux:field>
                            <flux:label>{{ __('availability.calendar.fields.range_start') }}</flux:label>
                            <flux:input type="date" wire:model.change="rangeStart" icon="calendar-days" />
                            <flux:error name="rangeStart" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('availability.calendar.fields.range_end') }}</flux:label>
                            <flux:input type="date" wire:model.change="rangeEnd" icon="calendar-days" />
                            <flux:error name="rangeEnd" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('availability.calendar.fields.status') }}</flux:label>
                            <flux:select wire:model.change="bulkStatus">
                                @foreach($this->statusOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="bulkStatus" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('availability.calendar.fields.price_override') }}</flux:label>
                            <flux:input type="number" inputmode="decimal" step="0.01" wire:model.blur="priceOverride" icon="banknotes" />
                            <flux:error name="priceOverride" />
                        </flux:field>
                        <div class="grid grid-cols-2 gap-3">
                            <flux:field>
                                <flux:label>{{ __('availability.calendar.fields.min_nights_override') }}</flux:label>
                                <flux:input type="number" inputmode="numeric" wire:model.blur="minNightsOverride" icon="numbered-list" />
                                <flux:error name="minNightsOverride" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('availability.calendar.fields.max_nights_override') }}</flux:label>
                                <flux:input type="number" inputmode="numeric" wire:model.blur="maxNightsOverride" icon="numbered-list" />
                                <flux:error name="maxNightsOverride" />
                            </flux:field>
                        </div>
                        <div class="grid gap-2">
                            <flux:checkbox wire:model.change="checkInAllowed" label="{{ __('availability.calendar.fields.check_in_allowed') }}" />
                            <flux:checkbox wire:model.change="checkOutAllowed" label="{{ __('availability.calendar.fields.check_out_allowed') }}" />
                        </div>
                        <flux:field>
                            <flux:label>{{ __('availability.calendar.fields.note') }}</flux:label>
                            <flux:input wire:model.blur="note" maxlength="160" icon="pencil-square" />
                            <flux:error name="note" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <flux:button type="button" size="sm" variant="filled" wire:click="openRange" icon="calendar-days">
                            {{ __('availability.calendar.actions.open_dates') }}
                        </flux:button>
                        <flux:button type="button" size="sm" variant="filled" wire:click="closeRange" icon="x-mark">
                            {{ __('availability.calendar.actions.close_dates') }}
                        </flux:button>
                        <flux:button type="button" size="sm" variant="filled" wire:click="markRepairRange" icon="calendar-days">
                            {{ __('availability.calendar.actions.mark_repair') }}
                        </flux:button>
                        <flux:button type="button" size="sm" variant="filled" wire:click="markCleaningRange" icon="calendar-days">
                            {{ __('availability.calendar.actions.mark_cleaning') }}
                        </flux:button>
                    </div>

                    <flux:button type="button" variant="primary" class="w-full data-loading:opacity-70" wire:click="applyRange" icon="calendar-days">
                        <span wire:loading.remove wire:target="applyRange">{{ __('availability.calendar.actions.apply') }}</span>
                        <span wire:loading wire:target="applyRange">{{ __('availability.calendar.actions.applying') }}</span>
                    </flux:button>
                </flux:card>

                <flux:card class="space-y-3">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('availability.calendar.sections.upcoming_checkins') }}</span>
                        </span>
                    </flux:heading>
                    @forelse($this->upcomingCheckIns as $booking)
                        <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                            <p class="font-medium">{{ $booking['guest'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('availability.calendar.booking_line', ['place' => $booking['place'], 'date' => $booking['date']]) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('availability.calendar.empty_checkins') }}</p>
                    @endforelse
                </flux:card>

                <flux:card class="space-y-3">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('availability.calendar.sections.upcoming_checkouts') }}</span>
                        </span>
                    </flux:heading>
                    @forelse($this->upcomingCheckOuts as $booking)
                        <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                            <p class="font-medium">{{ $booking['guest'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('availability.calendar.booking_line', ['place' => $booking['place'], 'date' => $booking['date']]) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('availability.calendar.empty_checkouts') }}</p>
                    @endforelse
                </flux:card>
            </aside>
        </div>

        @if($dateActionsOpen)
            <div class="fixed inset-x-0 bottom-0 z-30 rounded-t-2xl border border-zinc-200 bg-white p-4 shadow-2xl dark:border-zinc-700 dark:bg-zinc-950 sm:left-auto sm:right-6 sm:max-w-sm sm:rounded-2xl">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <flux:heading size="sm">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('availability.calendar.sheet.title') }}</span>
                                </span>
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('availability.calendar.sheet.helper') }}</flux:text>
                        </div>
                        <flux:button type="button" size="sm" variant="ghost" wire:click="closeDateActions" icon="x-mark">
                            {{ __('availability.calendar.actions.close_sheet') }}
                        </flux:button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <flux:field>
                            <flux:label>{{ __('availability.calendar.fields.range_start') }}</flux:label>
                            <flux:input type="date" wire:model.change="rangeStart" icon="calendar-days" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('availability.calendar.fields.range_end') }}</flux:label>
                            <flux:input type="date" wire:model.change="rangeEnd" icon="calendar-days" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <flux:button type="button" size="sm" variant="filled" wire:click="openRange" icon="calendar-days">
                            {{ __('availability.calendar.actions.open_dates') }}
                        </flux:button>
                        <flux:button type="button" size="sm" variant="filled" wire:click="closeRange" icon="x-mark">
                            {{ __('availability.calendar.actions.close_dates') }}
                        </flux:button>
                        <flux:button type="button" size="sm" variant="filled" wire:click="markRepairRange" icon="calendar-days">
                            {{ __('availability.calendar.actions.mark_repair') }}
                        </flux:button>
                        <flux:button type="button" size="sm" variant="filled" wire:click="markCleaningRange" icon="calendar-days">
                            {{ __('availability.calendar.actions.mark_cleaning') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</x-ui.page>
