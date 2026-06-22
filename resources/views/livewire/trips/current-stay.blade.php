<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald">{{ __('booking.trips.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">{{ __('booking.trips.current.title') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.trips.current.helper') }}
            </flux:text>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <flux:button href="{{ route('trips.index', ['locale' => app()->getLocale()]) }}" wire:navigate size="sm" variant="ghost" class="justify-center">
            {{ __('booking.trips.tabs.upcoming') }}
        </flux:button>
        <flux:button href="{{ route('trips.current', ['locale' => app()->getLocale()]) }}" wire:navigate size="sm" variant="primary" class="justify-center">
            {{ __('booking.trips.tabs.current') }}
        </flux:button>
        <flux:button href="{{ route('trips.scope', ['locale' => app()->getLocale(), 'scope' => 'past']) }}" wire:navigate size="sm" variant="ghost" class="justify-center">
            {{ __('booking.trips.tabs.past') }}
        </flux:button>
        <flux:button href="{{ route('trips.scope', ['locale' => app()->getLocale(), 'scope' => 'cancelled']) }}" wire:navigate size="sm" variant="ghost" class="justify-center">
            {{ __('booking.trips.tabs.cancelled') }}
        </flux:button>
    </div>

    @if($stay)
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ $stay['title'] }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ $stay['address'] ?: __('booking.trips.address_hidden') }}
                </flux:text>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.current.room_number') }}</div>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stay['room_number'] }}</div>
                </div>
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.current.place_number') }}</div>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stay['place_number'] }}</div>
                </div>
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.current.nights_remaining') }}</div>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stay['nights_remaining'] }}</div>
                </div>
                <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.current.checkout_reminder') }}</div>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stay['checkout_reminder'] }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">{{ __('booking.trips.current.stay_help') }}</flux:heading>
            <div class="space-y-2 text-sm text-zinc-700 dark:text-zinc-300">
                <div><span class="text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.host_contact') }}:</span> {{ $stay['host_name'] }} · {{ $stay['host_contact'] }}</div>
                <div><span class="text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.instructions') }}:</span> {{ $stay['instructions'] }}</div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">{{ __('booking.trips.rules') }}</flux:heading>
            @forelse($stay['rules'] as $rule)
                <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-800">{{ $rule }}</div>
            @empty
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('booking.trips.rules_empty') }}</flux:text>
            @endforelse
        </flux:card>

        <livewire:extensions.extend-stay :booking="$booking" :key="'extend-stay-'.$booking->id" />

        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:backdrop-blur-none">
            <div class="mx-auto grid w-full max-w-5xl grid-cols-2 gap-2 sm:grid-cols-3">
                <flux:button href="{{ route('bookings.extend', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="primary" class="w-full">
                    {{ __('booking.trips.actions.extend') }}
                </flux:button>
                <x-ui.report-problem-button href="{{ route('complaints.create', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate class="w-full">
                    {{ __('booking.trips.actions.report_problem') }}
                </x-ui.report-problem-button>
                <flux:button href="{{ route('bookings.checkout', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full col-span-2 sm:col-span-1">
                    {{ __('booking.trips.actions.check_out') }}
                </flux:button>
            </div>
        </div>
    @else
        <flux:card class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <flux:icon name="home" class="size-5" />
                </div>
                <div class="space-y-1">
                    <flux:heading size="lg">{{ __('booking.trips.current.empty_title') }}</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        {{ __('booking.trips.current.empty_text') }}
                    </flux:text>
                </div>
            </div>

            <flux:button href="{{ route('trips.index', ['locale' => app()->getLocale()]) }}" wire:navigate variant="primary" class="w-full">
                {{ __('booking.trips.tabs.upcoming') }}
            </flux:button>
        </flux:card>
    @endif
</x-ui.page>
