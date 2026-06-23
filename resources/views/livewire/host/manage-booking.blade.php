<x-ui.page class="space-y-6">
    <flux:heading size="xl">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('host.manage_booking.title') }} #{{ $booking->id }}</span>
        </span>
    </flux:heading>

    @if(session('success'))
        <flux:badge color="green" icon="check-circle">{{ session('success') }}</flux:badge>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card class="space-y-3">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.guest') }}</span>
                </span>
            </flux:heading>
            <div class="flex items-center gap-3">
                <div class="size-12 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                    <flux:icon name="user" class="size-6 text-zinc-400" />
                </div>
                <div>
                    <flux:text class="font-medium">{{ $booking->guest->name }}</flux:text>
                    <flux:text size="sm" class="text-zinc-500">{{ $booking->guest->email }}</flux:text>
                    @if($booking->guest->completed_stays_count)
                        <flux:text size="sm" class="text-zinc-500">{{ $booking->guest->completed_stays_count }} {{ __('app.units.stays') }} &middot; {{ number_format($booking->guest->rating_as_guest ?? 0, 1) }} &#9733;</flux:text>
                    @endif
                </div>
            </div>
            @if($booking->guest_message)
                <div class="mt-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <flux:text size="sm" class="text-zinc-500">{{ __('booking.guest_message') }}:</flux:text>
                    <flux:text>{{ $booking->guest_message }}</flux:text>
                </div>
            @endif
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.details') }}</span>
                </span>
            </flux:heading>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.status') }}</span><flux:badge icon="calendar-days">{{ $booking->status->label() }}</flux:badge></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.bed') }}</span><span>{{ $placeTitle }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.check_in') }}</span><span>{{ $booking->check_in->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.check_out') }}</span><span>{{ $booking->check_out->translatedFormat('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-zinc-500">{{ __('booking.nights') }}</span><span>{{ $booking->nights }}</span></div>
                <div class="flex justify-between font-semibold"><span>{{ __('booking.total') }}</span><span>&euro;{{ number_format($booking->total, 2) }}</span></div>
            </div>
        </flux:card>
    </div>

    <div class="flex flex-wrap gap-3">
        @if($booking->status->value === 'pending_host')
            <flux:button wire:click="approve" variant="primary" icon="eye">{{ __('host.manage_booking.approve') }}</flux:button>
            <flux:button wire:click="$set('showRejectModal', true)" variant="danger" icon="x-mark">{{ __('host.manage_booking.reject') }}</flux:button>
        @endif

        @if(in_array($booking->status->value, ['confirmed', 'paid', 'ready_for_checkin']))
            <flux:button wire:click="confirmCheckIn" variant="primary" icon="eye">{{ __('host.manage_booking.confirm_checkin') }}</flux:button>
            <flux:button wire:click="$set('showCancelModal', true)" variant="danger" icon="x-mark">{{ __('app.actions.cancel') }}</flux:button>
        @endif

        @if(in_array($booking->status->value, ['checked_in', 'in_progress', 'active_stay', 'leaving_soon', 'checked_out']))
            <flux:button wire:click="confirmCheckOut" variant="primary" icon="eye">{{ __('host.manage_booking.confirm_checkout') }}</flux:button>
        @endif

        @if($booking->status->value === 'completed' && ! $booking->host_review_left)
            <flux:button href="{{ route('host.reviews.create', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate variant="primary" icon="eye">
                {{ __('host.manage_booking.review_guest') }}
            </flux:button>
        @endif

        <x-ui.report-problem-button href="{{ route('complaints.create', ['locale' => app()->getLocale(), 'booking' => $booking]) }}" wire:navigate>
            {{ __('booking.trips.actions.report_problem') }}
        </x-ui.report-problem-button>

        <flux:button href="{{ route('host.bookings.index', ['locale' => app()->getLocale()]) }}" variant="ghost" wire:navigate icon="arrow-left">
            {{ __('app.actions.back') }}
        </flux:button>
    </div>

    @if($showRejectModal)
        <flux:modal wire:model="showRejectModal">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('host.manage_booking.reject_title') }}</span>
                </span>
            </flux:heading>
            <flux:textarea wire:model.blur="rejectReason" label="{{ __('host.manage_booking.reason_optional') }}" rows="3" />
            <div class="flex gap-3 mt-4">
                <flux:button wire:click="reject" variant="danger" icon="x-mark">{{ __('host.manage_booking.reject') }}</flux:button>
                <flux:button wire:click="$set('showRejectModal', false)" variant="ghost" icon="x-mark">{{ __('app.actions.cancel') }}</flux:button>
            </div>
        </flux:modal>
    @endif

    @if($showCancelModal)
        <flux:modal wire:model="showCancelModal">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('host.manage_booking.cancel_title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-500">{{ __('host.manage_booking.full_refund') }}</flux:text>
            <flux:textarea wire:model.blur="cancelReason" label="{{ __('host.manage_booking.reason_optional') }}" rows="3" />
            <div class="flex gap-3 mt-4">
                <flux:button wire:click="cancel" variant="danger" icon="x-mark">{{ __('host.manage_booking.confirm_cancellation') }}</flux:button>
                <flux:button wire:click="$set('showCancelModal', false)" variant="ghost" icon="arrow-left">{{ __('app.actions.back') }}</flux:button>
            </div>
        </flux:modal>
    @endif
</x-ui.page>
