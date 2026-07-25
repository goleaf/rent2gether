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

        <section id="host-calendar-overview" class="space-y-4">
            <div class="space-y-1">
                <flux:badge color="blue" icon="calendar-days">{{ __('host_calendar.title') }}</flux:badge>
                <flux:heading size="lg" level="2">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_calendar.sections.page') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('host_calendar.helpers.page') }}
                </flux:text>
            </div>

            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.summary_cards.events') }}</p>
                    <p class="mt-1 text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->calendarOverviewSummary['total_events'] }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.summary_cards.turnover') }}</p>
                    <p class="mt-1 text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ __('host_calendar.summary_cards.turnover_value', ['check_ins' => $this->calendarOverviewSummary['check_ins'], 'check_outs' => $this->calendarOverviewSummary['check_outs']]) }}
                    </p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.summary_cards.work') }}</p>
                    <p class="mt-1 text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ __('host_calendar.summary_cards.work_value', ['cleanings' => $this->calendarOverviewSummary['cleanings'], 'repairs' => $this->calendarOverviewSummary['repairs']]) }}
                    </p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.summary_cards.problems') }}</p>
                    <p class="mt-1 text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->calendarOverviewSummary['problem_events'] }}</p>
                </div>
            </div>

            <form wire:submit="applyCalendarFilters" class="space-y-4 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_calendar.fields.calendar_view') }}</span>
                            </span>
                        </flux:label>
                        <flux:select wire:model.change="calendarScopeView">
                            @foreach($this->calendarViewOptions as $view)
                                <flux:select.option value="{{ $view['value'] }}">{{ $view['label'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="calendarScopeView" />
                    </flux:field>

                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_calendar.fields.range_start') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="calendarRangeStart" icon="calendar-days" />
                        <flux:error name="calendarRangeStart" />
                    </flux:field>

                    <flux:field>
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_calendar.fields.range_end') }}</span>
                            </span>
                        </flux:label>
                        <flux:input type="date" wire:model.change="calendarRangeEnd" icon="calendar-days" />
                        <flux:error name="calendarRangeEnd" />
                    </flux:field>

                    <flux:field class="justify-end">
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('host_calendar.filters.only_problems') }}</span>
                            </span>
                        </flux:label>
                        <label class="flex min-h-10 items-center gap-2 rounded-lg border border-zinc-200 px-3 text-sm dark:border-zinc-700">
                            <flux:checkbox wire:model.change="calendarOnlyProblems" />
                            <span>{{ __('host_calendar.filters.only_problems_short') }}</span>
                        </label>
                    </flux:field>

                    <div class="flex items-end gap-2">
                        <flux:button type="submit" variant="primary" class="flex-1 data-loading:opacity-70" wire:target="applyCalendarFilters" icon="funnel">
                            {{ __('host_calendar.actions.apply_filters') }}
                        </flux:button>
                        <flux:button type="button" variant="ghost" wire:click="resetCalendarFilters" wire:loading.attr="disabled" icon="x-mark">
                            {{ __('host_calendar.actions.reset_filters') }}
                        </flux:button>
                    </div>
                </div>
            </form>

            @if($this->calendarOverviewRows->count() === 0)
                <flux:callout icon="calendar-days">
                    <flux:callout.heading>
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('host_calendar.empty.events_title') }}</span>
                        </span>
                    </flux:callout.heading>
                    <flux:callout.text>{{ __('host_calendar.empty.events') }}</flux:callout.text>
                </flux:callout>
            @else
                <div class="space-y-3 md:hidden">
                    @foreach($this->calendarOverviewRows as $row)
                        <flux:card class="space-y-3" wire:key="{{ $row['wire_key'] }}-mobile">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 space-y-1">
                                    <flux:badge size="sm" color="{{ $row['event_type_color'] }}" icon="calendar-days">{{ $row['event_type_label'] }}</flux:badge>
                                    <flux:heading size="sm">
                                        <span class="inline-flex min-w-0 items-center gap-2">
                                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                            <span class="min-w-0">{{ $row['date_label'] }}</span>
                                        </span>
                                    </flux:heading>
                                    <flux:text size="sm" class="truncate text-zinc-600 dark:text-zinc-400">{{ $row['sleeping_place'] }}</flux:text>
                                </div>
                                <flux:badge size="sm" color="{{ $row['place_status_color'] }}" icon="user">{{ $row['place_status_label'] }}</flux:badge>
                            </div>

                            <dl class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.fields.property') }}</dt>
                                    <dd class="truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $row['property'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.fields.room') }}</dt>
                                    <dd class="truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $row['room'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.fields.guest_name') }}</dt>
                                    <dd class="truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $row['guest_name'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.fields.nights_count') }}</dt>
                                    <dd class="font-medium text-zinc-800 dark:text-zinc-100">{{ $row['nights_count_label'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.fields.payment_status') }}</dt>
                                    <dd class="truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $row['payment_status_label'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.fields.check_in_status') }}</dt>
                                    <dd class="truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $row['check_in_status_label'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.fields.date_price') }}</dt>
                                    <dd class="font-medium text-zinc-800 dark:text-zinc-100">{{ $row['date_price'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.fields.payout_amount') }}</dt>
                                    <dd class="font-medium text-zinc-800 dark:text-zinc-100">{{ $row['payout'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host_calendar.fields.host_comment') }}</dt>
                                    <dd class="truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $row['host_comment'] }}</dd>
                                </div>
                            </dl>

                            <div class="flex flex-wrap gap-2">
                                @if($row['needs_cleaning'])
                                    <flux:badge size="sm" color="amber" icon="sparkles">{{ __('host_calendar.fields.needs_cleaning') }}</flux:badge>
                                @endif
                                @if($row['needs_inspection'])
                                    <flux:badge size="sm" color="amber" icon="check-circle">{{ __('host_calendar.fields.needs_inspection') }}</flux:badge>
                                @endif
                                @if($row['needs_repair'])
                                    <flux:badge size="sm" color="red" icon="wrench">{{ __('host_calendar.fields.needs_repair') }}</flux:badge>
                                @endif
                            </div>
                        </flux:card>
                    @endforeach
                </div>

                <div class="hidden overflow-x-auto rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-950 md:block">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('host_calendar.fields.date') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.property') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.room') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.sleeping_place') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.place_status') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.guest_name') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.check_in_date') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.check_out_date') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.nights_count') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.payment_status') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.check_in_status') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.needs_cleaning') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.needs_inspection') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.needs_repair') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.date_price') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.payout_amount') }}</flux:table.column>
                            <flux:table.column>{{ __('host_calendar.fields.host_comment') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($this->calendarOverviewRows as $row)
                                <flux:table.row :key="$row['wire_key']">
                                    <flux:table.cell variant="strong">{{ $row['date_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['property'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['room'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['sleeping_place'] }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" color="{{ $row['place_status_color'] }}" icon="user">{{ $row['place_status_label'] }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $row['guest_name'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['check_in_date_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['check_out_date_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['nights_count_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['payment_status_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['check_in_status_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['needs_cleaning_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['needs_inspection_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['needs_repair_label'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['date_price'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['payout'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $row['host_comment'] }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>

                @if($this->calendarOverviewRows->hasPages())
                    <div>
                        {{ $this->calendarOverviewRows->links(data: ['scrollTo' => '#host-calendar-overview']) }}
                    </div>
                @endif
            @endif
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
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.property') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="selectedPropertyId">
                        @foreach($this->properties as $property)
                            <flux:select.option value="{{ $property['id'] }}">{{ $property['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.room') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="selectedRoomId">
                        @foreach($this->rooms as $room)
                            <flux:select.option value="{{ $room['id'] }}">{{ $room['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.sleeping_place') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="selectedSleepingPlaceId">
                        @foreach($this->sleepingPlaces as $place)
                            <flux:select.option value="{{ $place['id'] }}">{{ $place['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="selectedSleepingPlaceId" />
                </flux:field>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.view_mode') }}</span>
    </span>
</flux:label>
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
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0"><div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate font-medium">{{ $property['label'] }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ __('availability.calendar.overview.property_line', ['rooms' => $property['rooms_count'], 'places' => $property['places_count']]) }}
                                            </p>
                                        </div>
                                        <flux:badge size="sm" color="blue" icon="home-modern">
                                            {{ __('availability.calendar.occupancy_value', ['percent' => $property['occupancy_percentage']]) }}
                                        </flux:badge>
                                    </div></span>
    </span>
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
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.range_start') }}</span>
    </span>
</flux:label>
                            <flux:input type="date" wire:model.change="rangeStart" icon="calendar-days" />
                            <flux:error name="rangeStart" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.range_end') }}</span>
    </span>
</flux:label>
                            <flux:input type="date" wire:model.change="rangeEnd" icon="calendar-days" />
                            <flux:error name="rangeEnd" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.status') }}</span>
    </span>
</flux:label>
                            <flux:select wire:model.change="bulkStatus">
                                @foreach($this->statusOptions() as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="bulkStatus" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.price_override') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="decimal" step="0.01" wire:model.blur="priceOverride" icon="banknotes" />
                            <flux:error name="priceOverride" />
                        </flux:field>
                        <div class="grid grid-cols-2 gap-3">
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.min_nights_override') }}</span>
    </span>
</flux:label>
                                <flux:input type="number" inputmode="numeric" wire:model.blur="minNightsOverride" icon="numbered-list" />
                                <flux:error name="minNightsOverride" />
                            </flux:field>
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.max_nights_override') }}</span>
    </span>
</flux:label>
                                <flux:input type="number" inputmode="numeric" wire:model.blur="maxNightsOverride" icon="numbered-list" />
                                <flux:error name="maxNightsOverride" />
                            </flux:field>
                        </div>
                        <div class="grid gap-2">
                                                        <flux:field variant="inline">
                                <flux:checkbox wire:model.change="checkInAllowed" />
                                <flux:label>
                                    <span class="inline-flex min-w-0 items-center gap-1.5">
                                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                        <span class="min-w-0">{{ __('availability.calendar.fields.check_in_allowed') }}</span>
                                    </span>
                                </flux:label>
                                <flux:error name="checkInAllowed" />
                            </flux:field>
                                                        <flux:field variant="inline">
                                <flux:checkbox wire:model.change="checkOutAllowed" />
                                <flux:label>
                                    <span class="inline-flex min-w-0 items-center gap-1.5">
                                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                        <span class="min-w-0">{{ __('availability.calendar.fields.check_out_allowed') }}</span>
                                    </span>
                                </flux:label>
                                <flux:error name="checkOutAllowed" />
                            </flux:field>
                        </div>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.note') }}</span>
    </span>
</flux:label>
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
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.range_start') }}</span>
    </span>
</flux:label>
                            <flux:input type="date" wire:model.change="rangeStart" icon="calendar-days" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.calendar.fields.range_end') }}</span>
    </span>
</flux:label>
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
