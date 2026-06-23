<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="check-circle">{{ __('booking.checkin.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.checkin.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.checkin.helper') }}
            </flux:text>
        </div>
    </section>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $trip['title'] }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ $trip['dates'] }} · {{ $trip['check_in_time'] }}
            </flux:text>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-3 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">
            <div class="font-medium">{{ __('booking.checkin.instructions_title') }}</div>
            <div class="mt-1 whitespace-pre-line">{{ $trip['instructions'] }}</div>
        </div>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking.checkin.checklist') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.checkin.checklist_helper') }}
                </flux:text>
            </div>

            <div class="space-y-3">
                <flux:checkbox wire:model.change="propertyFound" label="{{ __('booking.checkin.property_found') }}" />
                <flux:checkbox wire:model.change="keysReceived" label="{{ __('booking.checkin.keys_received') }}" />
                <flux:checkbox wire:model.change="codeReceived" label="{{ __('booking.checkin.code_received') }}" />
                <flux:checkbox wire:model.change="roomSeen" label="{{ __('booking.checkin.room_seen') }}" />
                <flux:checkbox wire:model.change="sleepingPlaceShown" label="{{ __('booking.checkin.sleeping_place_shown') }}" />
                <flux:checkbox wire:model.change="rulesSeen" label="{{ __('booking.checkin.rules_seen') }}" />
                <flux:checkbox wire:model.change="everythingOk" label="{{ __('booking.checkin.everything_ok') }}" />
            </div>

            @error('keys_received')
                <flux:text size="sm" class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
            @enderror
            @error('booking')
                <flux:text size="sm" class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
            @enderror
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.problem_report.short_title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.problem_report.short_helper') }}
            </flux:text>
            <x-ui.report-problem-button href="{{ route('bookings.checkin.problem', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate class="w-full">
                {{ __('booking.trips.actions.report_problem') }}
            </x-ui.report-problem-button>
        </flux:card>

        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:backdrop-blur-none">
            <div class="mx-auto grid w-full max-w-5xl grid-cols-2 gap-2">
                <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full" icon="arrow-left">
                    {{ __('app.actions.back') }}
                </flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" data-loading variant="primary" class="w-full" icon="key">
                    <span wire:loading.remove>{{ __('booking.checkin.submit') }}</span>
                    <span wire:loading>{{ __('booking.checkin.submitting') }}</span>
                </flux:button>
            </div>
        </div>
    </form>
</x-ui.page>
