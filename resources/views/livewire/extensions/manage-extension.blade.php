@php
    $booking = $extension->booking;
    $place = $booking->sleepingPlace;
    $placeTitle = $place?->translations?->firstWhere('locale', app()->getLocale())?->title
        ?: $place?->translations?->firstWhere('locale', config('app.fallback_locale', 'en'))?->title
        ?: $place?->display_name
        ?: __('booking.bed');
    $statusValue = $extension->status instanceof \App\Enums\BookingExtensionStatus
        ? $extension->status->value
        : (string) $extension->status;
@endphp

<div class="mx-auto max-w-2xl space-y-5 px-4 py-4 pb-24 sm:px-6">
    <section class="space-y-2">
        <flux:badge color="emerald">{{ __('booking.extension.host_eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">{{ __('booking.extension.manage_title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">
            {{ __('booking.extension.manage_helper') }}
        </flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-4">
        <div class="flex items-start gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="user" class="size-5 text-zinc-500" />
            </div>
            <div class="min-w-0 space-y-1">
                <flux:heading size="lg">{{ $booking->guest?->name ?: __('booking.guest') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $placeTitle }}</flux:text>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.extension.current_checkout') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $extension->current_checkout_date?->translatedFormat('d M Y') }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.extension.new_checkout') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $extension->requested_new_checkout_date?->translatedFormat('d M Y') }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.extension.extra_nights') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $extension->additional_nights }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.extension.payment_required') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ number_format((float) $extension->total_extra, 2) }} {{ $booking->currency }}</div>
            </div>
        </div>

        <flux:badge>{{ __('statuses.extension.'.$statusValue) }}</flux:badge>

        @if($extension->guest_message)
            <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.extension.fields.guest_message') }}</div>
                <p class="mt-1 text-zinc-800 dark:text-zinc-200">{{ $extension->guest_message }}</p>
            </div>
        @endif

        @error('extension')
            <flux:callout color="amber" icon="information-circle">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        @if($extension->status === \App\Enums\BookingExtensionStatus::AwaitingHostApproval)
            <div class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('booking.extension.fields.host_response') }}</flux:label>
                    <flux:textarea rows="3" wire:model.blur="hostResponse" />
                    <flux:error name="hostResponse" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('booking.extension.fields.decline_reason') }}</flux:label>
                    <flux:select wire:model.change="declineReason">
                        <flux:select.option value="">{{ __('booking.extension.fields.choose_decline_reason') }}</flux:select.option>
                        @foreach($declineReasons as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="declineReason" />
                </flux:field>

                <div class="grid gap-2 sm:grid-cols-2">
                    <flux:button wire:click="approve" variant="primary" class="w-full data-loading:opacity-70">
                        <span wire:loading.remove wire:target="approve">{{ __('booking.extension.actions.approve') }}</span>
                        <span wire:loading wire:target="approve">{{ __('booking.extension.actions.approving') }}</span>
                    </flux:button>
                    <flux:button wire:click="reject" variant="danger" class="w-full data-loading:opacity-70">
                        <span wire:loading.remove wire:target="reject">{{ __('booking.extension.actions.decline') }}</span>
                        <span wire:loading wire:target="reject">{{ __('booking.extension.actions.declining') }}</span>
                    </flux:button>
                </div>
            </div>
        @else
            <flux:callout icon="information-circle">
                <flux:callout.text>{{ __('booking.extension.host_status_note') }}</flux:callout.text>
            </flux:callout>
        @endif
    </flux:card>
</div>
