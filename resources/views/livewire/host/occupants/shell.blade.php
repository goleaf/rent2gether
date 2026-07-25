<section class="space-y-4">
    <flux:card class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <flux:badge color="zinc" icon="user">{{ __('current_occupants.title') }}</flux:badge>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('current_occupants.sections.'.$section) }}</span>
                    </span>
                </flux:heading>
            </div>

            <flux:button
                type="button"
                variant="ghost"
                size="sm"
                wire:click="resetOccupantFilters"
                wire:loading.attr="disabled"
                icon="arrow-path"
            >
                {{ __('current_occupants.actions.reset_filters') }}
            </flux:button>
        </div>

        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('current_occupants.helpers.'.$section) }}
        </flux:text>
    </flux:card>

    <div class="grid grid-cols-2 gap-2">
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
            <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.summary_labels.current') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('current_occupants.summary.current', ['count' => $summary->currentCount]) }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
            <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.summary_labels.check_ins_today') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('current_occupants.summary.check_ins_today', ['count' => $summary->checkInsTodayCount]) }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
            <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.summary_labels.check_outs_today') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('current_occupants.summary.check_outs_today', ['count' => $summary->checkOutsTodayCount]) }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-950">
            <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.summary_labels.needs_attention') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('current_occupants.summary.needs_attention', ['count' => $summary->needsAttentionCount]) }}</flux:text>
        </div>
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="funnel" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('current_occupants.filters.title') }}</span>
            </span>
        </flux:heading>

        <div class="flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('current_occupants.filters.title') }}">
            @foreach ($scopeOptions as $option)
                <flux:button
                    type="button"
                    size="sm"
                    variant="{{ $scope === $option['value'] ? 'primary' : 'ghost' }}"
                    class="shrink-0"
                    wire:key="current-occupant-filter-{{ $option['value'] }}"
                    wire:click="setScope('{{ $option['value'] }}')"
                    wire:loading.attr="disabled"
                    icon="funnel"
                >
                    {{ $option['label'] }}
                </flux:button>
            @endforeach
        </div>

        <flux:field>
            <div class="flex items-center gap-2">
                <flux:checkbox wire:model.change="onlyNeedsAttention" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('current_occupants.filters.only_needs_attention') }}</span>
                    </span>
                </flux:label>
            </div>
        </flux:field>
    </flux:card>

    <div
        wire:loading.delay.flex
        wire:target="setScope,resetOccupantFilters,onlyNeedsAttention"
        class="hidden items-center gap-2 rounded-lg border border-sky-100 bg-sky-50 px-3 py-2 text-sm text-sky-800 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-100"
    >
        <flux:icon name="arrow-path" class="size-4 animate-spin" />
        <span>{{ __('current_occupants.loading.refreshing') }}</span>
    </div>

    <section id="host-current-occupants-list" class="space-y-3">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="users" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('current_occupants.cards.title') }}</span>
            </span>
        </flux:heading>

        @forelse ($occupants as $occupant)
            <article wire:key="current-occupant-{{ $occupant['booking_id'] }}" class="space-y-4 rounded-lg border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-950">
                <div class="flex gap-3">
                    <div class="size-12 shrink-0 overflow-hidden rounded-full bg-sky-50 text-sky-700 dark:bg-sky-400/10 dark:text-sky-200">
                        @if ($occupant['guest_avatar_url'])
                            <img
                                src="{{ $occupant['guest_avatar_url'] }}"
                                alt="{{ $occupant['guest_avatar_alt'] }}"
                                class="size-full object-cover"
                                loading="lazy"
                            />
                        @else
                            <div class="flex size-full items-center justify-center text-base font-semibold" aria-label="{{ __('current_occupants.fields.guest_photo') }}">
                                {{ $occupant['guest_avatar_initial'] }}
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1 space-y-2">
                        <div class="min-w-0">
                            <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.fields.guest') }}</flux:text>
                            <flux:heading size="base" class="truncate">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="user" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0 truncate">{{ $occupant['guest_display_name'] }}</span>
                                </span>
                            </flux:heading>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <flux:badge color="zinc" icon="banknotes">{{ $occupant['payment_status_label'] }}</flux:badge>
                            <flux:badge color="blue" icon="home-modern">{{ $occupant['stay_status_label'] }}</flux:badge>
                            @if ($occupant['has_complaints'])
                                <flux:badge color="red" icon="exclamation-triangle">{{ $occupant['complaints_label'] }}</flux:badge>
                            @endif
                        </div>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.room') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['room_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.sleeping_place') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['sleeping_place_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.check_in_date') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['check_in_date_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.check_out_date') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['check_out_date_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.nights_count') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['nights_count_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.nights_left') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['nights_left_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.payment_status') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['payment_status_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.stay_status') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['stay_status_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.guest_contact') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['guest_contact_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.guest_rating') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['guest_rating_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.has_complaints') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['complaints_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.needs_extension') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['needs_extension_label'] }}</dd>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-900">
                        <dt class="text-xs text-zinc-500">{{ __('current_occupants.fields.needs_checkout') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $occupant['needs_checkout_label'] }}</dd>
                    </div>
                </dl>

                <div class="space-y-3">
                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.fields.special_requests') }}</flux:text>
                        <flux:text size="sm" class="mt-1 text-zinc-800 dark:text-zinc-200">{{ $occupant['special_requests_label'] }}</flux:text>
                    </div>
                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.fields.host_comment') }}</flux:text>
                        <flux:text size="sm" class="mt-1 text-zinc-800 dark:text-zinc-200">{{ $occupant['host_comment'] }}</flux:text>
                    </div>
                </div>

                @if (count($occupant['flags']) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($occupant['flags'] as $flag)
                            <flux:badge color="{{ $flag['severity'] === 'critical' ? 'red' : 'amber' }}" icon="flag">
                                {{ $flag['label'] }}
                            </flux:badge>
                        @endforeach
                    </div>
                @endif

                <div class="flex gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                    <flux:button
                        href="{{ route('host.bookings.manage', ['locale' => app()->getLocale(), 'booking' => $occupant['booking_id']]) }}"
                        wire:navigate
                        variant="ghost"
                        class="flex-1"
                        icon="eye"
                    >
                        {{ __('current_occupants.actions.details') }}
                    </flux:button>
                    <flux:button
                        href="{{ route('host.bookings.manage', ['locale' => app()->getLocale(), 'booking' => $occupant['booking_id']]) }}"
                        wire:navigate
                        variant="primary"
                        class="flex-1"
                        icon="chat-bubble-left-right"
                    >
                        {{ __('current_occupants.actions.message_guest') }}
                    </flux:button>
                </div>
            </article>
        @empty
            <flux:card class="space-y-2">
                <flux:heading size="base">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('current_occupants.empty.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('current_occupants.empty.body') }}
                </flux:text>
            </flux:card>
        @endforelse
    </section>

    @if ($occupants->hasPages())
        <div class="rounded-lg border border-zinc-200 bg-white p-2 dark:border-zinc-700 dark:bg-zinc-950">
            {{ $occupants->links(data: ['scrollTo' => '#host-current-occupants-list']) }}
        </div>
    @endif
</section>
