<div class="mx-auto max-w-3xl space-y-5 px-4 py-4 pb-32 sm:px-6">
    <section class="space-y-3">
        <flux:badge color="emerald">{{ __('booking.trips.detail.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">{{ $trip['title'] }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.trips.detail.helper') }}
            </flux:text>
        </div>
    </section>

    @if(session('trip-status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
            {{ session('trip-status') }}
        </div>
    @endif

    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('booking.trips.detail.summary') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking.trips.reference') }} {{ $booking->reference }}</flux:text>
            </div>
            <flux:badge color="zinc">{{ $booking->status->label() }}</flux:badge>
        </div>

        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.city') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['city'] }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.property') }}</div>
                <div class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['property'] }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.room') }}</div>
                <div class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['room'] }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.bed') }}</div>
                <div class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['title'] }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.dates') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['dates'] }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.nights') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['nights'] }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.check_in_time') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['check_in_time'] }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.check_out_time') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['check_out_time'] }}</div>
            </div>
        </div>
    </flux:card>

    <flux:card class="space-y-3">
        <flux:heading size="lg">{{ __('booking.trips.arrival') }}</flux:heading>
        <div class="space-y-2 text-sm">
            <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-800">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.payment_page.access.address') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['address'] ?: __('booking.trips.address_hidden') }}</div>
            </div>
            <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-800">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.host_contact') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['host_name'] }} · {{ $trip['host_contact'] }}</div>
            </div>
            <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-800">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.instructions') }}</div>
                <div class="whitespace-pre-line text-zinc-900 dark:text-zinc-100">{{ $trip['instructions'] }}</div>
            </div>
            <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-800">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.trips.wifi.title') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['wifi'] }}</div>
            </div>
        </div>
    </flux:card>

    <flux:card class="space-y-3">
        <flux:heading size="lg">{{ __('booking.trips.rules') }}</flux:heading>
        @forelse($trip['rules'] as $rule)
            <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-800">{{ $rule }}</div>
        @empty
            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('booking.trips.rules_empty') }}</flux:text>
        @endforelse
    </flux:card>

    <flux:card class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('booking.trips.receipt') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('booking.trips.receipt_helper') }}</flux:text>
        </div>

        <div class="space-y-2 text-sm">
            @foreach($trip['line_items'] as $line)
                <div class="flex items-start justify-between gap-3 {{ $loop->last ? 'border-t border-zinc-200 pt-2 font-semibold dark:border-zinc-700' : '' }}">
                    <div>
                        <div>{{ $line['label'] }}</div>
                        @if($line['refundable'])
                            <div class="text-xs text-emerald-700 dark:text-emerald-300">{{ __('booking.payment_page.price.refundable') }}</div>
                        @endif
                    </div>
                    <div class="shrink-0">{{ $line['amount'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-800">
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $trip['deposit_status']['label'] }}</div>
            <div class="mt-1 text-zinc-600 dark:text-zinc-400">{{ $trip['deposit_status']['helper'] }}</div>
        </div>
    </flux:card>

    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:backdrop-blur-none">
        <div class="mx-auto grid max-w-3xl grid-cols-2 gap-2 sm:grid-cols-3">
            @if($trip['actions']['message'])
                <flux:button href="{{ route('messages.index', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full">
                    {{ __('booking.trips.actions.message_host') }}
                </flux:button>
            @endif
            @if($trip['actions']['payment'])
                <flux:button href="{{ route('guest.bookings.payment', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="primary" class="w-full">
                    {{ __('booking.payment_page.actions.open_payment') }}
                </flux:button>
            @endif
            @if($trip['actions']['check_in'])
                <flux:button href="{{ route('bookings.checkin', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="primary" class="w-full">
                    {{ __('booking.trips.actions.check_in') }}
                </flux:button>
            @endif
            @if($trip['actions']['report_problem'])
                <flux:button href="{{ route('complaints.create', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full">
                    {{ __('booking.trips.actions.report_problem') }}
                </flux:button>
            @endif
            @if($trip['actions']['extend'])
                <flux:button href="{{ route('bookings.extend', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="ghost" class="w-full">
                    {{ __('booking.trips.actions.extend') }}
                </flux:button>
            @endif
            @if($trip['actions']['cancel'])
                <flux:button href="{{ route('guest.bookings.cancel', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="danger" class="w-full">
                    {{ __('booking.trips.actions.cancel') }}
                </flux:button>
            @endif
            @if($trip['actions']['check_out'])
                <flux:button href="{{ route('bookings.checkout', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="primary" class="w-full">
                    {{ __('booking.trips.actions.check_out') }}
                </flux:button>
            @endif
            @if($trip['actions']['review'])
                <flux:button href="{{ route('reviews.create', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="primary" class="w-full">
                    {{ __('booking.trips.actions.review') }}
                </flux:button>
            @endif
        </div>
    </div>
</div>
