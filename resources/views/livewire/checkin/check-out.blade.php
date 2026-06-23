<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="check-circle">{{ __('booking.checkout.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.checkout.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.checkout.helper') }}
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
                {{ $trip['dates'] }} · {{ $trip['check_out_time'] }}
            </flux:text>
        </div>

        <div class="rounded-lg border border-zinc-200 px-3 py-3 text-sm dark:border-zinc-800">
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('booking.checkout.deposit_title') }}</div>
            <div class="mt-1 text-zinc-600 dark:text-zinc-400">{{ $trip['deposit_status']['helper'] }}</div>
        </div>
    </flux:card>

    <form wire:submit="submit" class="space-y-4">
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking.checkout.checklist') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('booking.checkout.checklist_helper') }}
                </flux:text>
            </div>

            <div class="space-y-3">
                <flux:checkbox wire:model.change="keysReturned" label="{{ __('booking.checkout.keys_returned') }}" />
                <flux:checkbox wire:model.change="belongingsRemoved" label="{{ __('booking.checkout.belongings_removed') }}" />
                <flux:checkbox wire:model.change="lockerEmptied" label="{{ __('booking.checkout.locker_emptied') }}" />
                <flux:checkbox wire:model.change="placeClean" label="{{ __('booking.checkout.place_clean') }}" />
            </div>

            @error('booking')
                <flux:text size="sm" class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
            @enderror
        </flux:card>

        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:backdrop-blur-none">
            <div class="mx-auto grid w-full max-w-5xl grid-cols-2 gap-2">
                <flux:button href="{{ route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full" icon="arrow-left">
                    {{ __('app.actions.back') }}
                </flux:button>
                <flux:button type="submit" wire:loading.attr="disabled" data-loading variant="primary" class="w-full" icon="clipboard-document-check">
                    <span wire:loading.remove>{{ __('booking.checkout.submit') }}</span>
                    <span wire:loading>{{ __('booking.checkout.submitting') }}</span>
                </flux:button>
            </div>
        </div>
    </form>
</x-ui.page>
