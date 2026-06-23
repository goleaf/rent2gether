<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="amber" icon="exclamation-triangle">{{ __('booking.complaint.detail_eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking.complaint.detail_title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.complaint.detail_helper') }}
            </flux:text>
        </div>
    </section>

    @if(session('complaint-status'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('complaint-status') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking.complaint.reference', ['reference' => $complaint->complaint_number ?: $complaint->reference]) }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ $complaint->type->label() }}
                </flux:text>
            </div>
            <flux:badge color="zinc" icon="exclamation-triangle">{{ $complaint->status->label() }}</flux:badge>
        </div>

        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.complaint.fields.priority') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('booking.complaint.priority.'.($complaint->priority ?: $complaint->urgency)) }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.complaint.fields.booking') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $complaint->booking?->reference }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.complaint.fields.reporter') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $complaint->reporterUser?->name ?: $complaint->reporter?->name }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.complaint.fields.other_side') }}</div>
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $complaint->reportedUser?->name ?: __('booking.complaint.no_other_side') }}</div>
            </div>
        </div>
    </flux:card>

    <flux:card class="space-y-3">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('booking.complaint.description_title') }}</span>
            </span>
        </flux:heading>
        <p class="whitespace-pre-line text-sm text-zinc-700 dark:text-zinc-300">{{ $complaint->description }}</p>

        @if($complaint->desired_resolution)
            <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-800">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('booking.complaint.fields.desired_resolution') }}</div>
                <div class="mt-1 whitespace-pre-line text-zinc-900 dark:text-zinc-100">{{ $complaint->desired_resolution }}</div>
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            @if($complaint->refund_requested)
                <flux:badge color="blue" icon="exclamation-triangle">{{ __('booking.complaint.fields.refund_requested') }}</flux:badge>
            @endif
            @if($complaint->deposit_hold_requested)
                <flux:badge color="amber" icon="exclamation-triangle">{{ __('booking.complaint.fields.deposit_hold_requested') }}</flux:badge>
            @endif
        </div>

        @if(count($complaint->media ?: $complaint->photos ?: []))
            <div class="space-y-2">
                <flux:text size="sm" class="font-medium">{{ __('booking.complaint.fields.media') }}</flux:text>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(($complaint->media ?: $complaint->photos ?: []) as $path)
                        <a href="{{ Storage::disk('public')->url($path) }}" target="_blank" class="block overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800">
                            <img src="{{ Storage::disk('public')->url($path) }}" alt="{{ __('booking.complaint.media_alt') }}" class="aspect-square w-full object-cover" loading="lazy" decoding="async">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </flux:card>

    <flux:card class="space-y-3">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('booking.complaint.other_side_response_title') }}</span>
            </span>
        </flux:heading>

        @if($complaint->other_side_response ?: $complaint->respondent_reply)
            <p class="whitespace-pre-line text-sm text-zinc-700 dark:text-zinc-300">{{ $complaint->other_side_response ?: $complaint->respondent_reply }}</p>
        @elseif($canRespond)
            <form wire:submit="respond" class="space-y-3">
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking.complaint.fields.other_side_response') }}</span>
                        </span>
                    </flux:label>
                    <flux:textarea
                        wire:model.blur="otherSideResponse"
                        rows="5"
                        :error="$errors->first('otherSideResponse')" />
                    <flux:error name="otherSideResponse" />
                </flux:field>
                <flux:button type="submit" wire:loading.attr="disabled" data-loading variant="primary" class="w-full" icon="calendar-days">
                    <span wire:loading.remove>{{ __('booking.complaint.actions.respond') }}</span>
                    <span wire:loading>{{ __('booking.complaint.actions.responding') }}</span>
                </flux:button>
            </form>
        @else
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('booking.complaint.other_side_waiting') }}
            </flux:text>
        @endif
    </flux:card>

    <flux:card class="space-y-3">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('booking.complaint.timeline.title') }}</span>
            </span>
        </flux:heading>
        <div class="space-y-3">
            @foreach($timeline as $item)
                <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-800">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item['status'] }}</div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $item['note'] }}</div>
                            @if($item['actor'])
                                <div class="text-xs text-zinc-500 dark:text-zinc-500">{{ __('booking.complaint.timeline.actor', ['name' => $item['actor']]) }}</div>
                            @endif
                        </div>
                        <div class="shrink-0 text-xs text-zinc-500 dark:text-zinc-500">{{ $item['date'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </flux:card>

    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-4 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95 sm:static sm:rounded-lg sm:border sm:backdrop-blur-none">
        <div class="mx-auto w-full max-w-5xl">
            <flux:button href="{{ in_array((int) auth()->id(), [(int) $complaint->booking?->host_user_id, (int) $complaint->booking?->host_id], true) ? route('host.bookings.manage', ['locale' => app()->getLocale(), 'booking' => $complaint->booking]) : route('guest.bookings.show', ['locale' => app()->getLocale(), 'booking' => $complaint->booking]) }}" wire:navigate variant="ghost" class="w-full" icon="arrow-left">
                {{ __('booking.complaint.actions.back_to_booking') }}
            </flux:button>
        </div>
    </div>
</x-ui.page>
