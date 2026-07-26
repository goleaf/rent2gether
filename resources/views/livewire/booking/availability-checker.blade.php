<div class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('availability.checker.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('availability.checker.helper') }}</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.checker.fields.check_in') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="checkIn" icon="calendar-days" />
            <flux:error name="checkIn" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('availability.checker.fields.check_out') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="checkOut" icon="calendar-days" />
            <flux:error name="checkOut" />
        </flux:field>
    </div>

    <flux:button type="button" variant="primary" class="w-full data-loading:opacity-70" wire:click="checkAvailability" icon="magnifying-glass">
        <span wire:loading.remove wire:target="checkAvailability">{{ __('availability.checker.actions.check') }}</span>
        <span wire:loading wire:target="checkAvailability">{{ __('availability.checker.actions.checking') }}</span>
    </flux:button>

    @if($result)
        @if($result['available'])
            <flux:callout color="green" icon="check-circle">
                <flux:callout.heading icon="check-circle" icon:variant="mini">{{ __('availability.checker.available_title') }}</flux:callout.heading>
                <flux:callout.text>{{ __('availability.checker.available_text') }}</flux:callout.text>
            </flux:callout>
        @else
            <flux:callout color="amber" icon="exclamation-triangle">
                <flux:callout.heading icon="exclamation-triangle" icon:variant="mini">{{ __('availability.checker.unavailable_title') }}</flux:callout.heading>
                <flux:callout.text>{{ __('availability.checker.unavailable_text') }}</flux:callout.text>
            </flux:callout>

            @if($result['unavailable_dates'])
                <div class="space-y-2">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('availability.checker.unavailable_dates') }}</span>
                        </span>
                    </flux:heading>
                    <div class="flex flex-wrap gap-2">
                        @foreach($result['unavailable_dates'] as $date)
                            <flux:badge size="sm" icon="calendar-days">{{ $date['label'] }}</flux:badge>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($result['nearest_ranges'])
                <div class="space-y-2">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('availability.checker.nearest_ranges') }}</span>
                        </span>
                    </flux:heading>
                    <div class="space-y-2">
                        @foreach($result['nearest_ranges'] as $range)
                            <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                                {{ $range['label'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    @endif
</div>
