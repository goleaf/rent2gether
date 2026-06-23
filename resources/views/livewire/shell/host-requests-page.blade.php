<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="check-circle">{{ $page['eyebrow'] }}</flux:badge>

        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $page['title'] }}</span>
                </span>
            </flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                {{ $page['helper'] }}
            </flux:text>
        </div>
    </section>

    @if (session('host-request-status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
            {{ session('host-request-status') }}
        </div>
    @endif

    <div wire:loading.delay class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-3 text-sm text-sky-800 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-200">
        {{ __('host.requests.loading') }}
    </div>

    @if ($requests->count() === 0)
        <flux:card class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <flux:icon name="{{ $page['icon'] }}" class="size-5" />
                </div>

                <div class="min-w-0 space-y-1">
                    <flux:heading size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $page['empty_title'] }}</span>
                        </span>
                    </flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        {{ $page['empty_text'] }}
                    </flux:text>
                </div>
            </div>

            <div class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                {{ $page['note'] }}
            </div>
        </flux:card>
    @else
        <div class="space-y-3">
            @foreach ($requests as $booking)
                <flux:card class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 space-y-1">
                            <flux:heading size="lg" class="truncate">{{ $this->placeTitle($booking) }}</flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $this->placeMeta($booking) }}
                            </flux:text>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('host.requests.card.guest', ['name' => $this->guestName($booking)]) }}
                            </flux:text>
                        </div>

                        <flux:badge color="amber" icon="exclamation-triangle">{{ $this->statusLabel($booking) }}</flux:badge>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.requests.fields.dates') }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->dateSummary($booking) }}</div>
                        </div>

                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.requests.fields.price') }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->money($booking->total_amount ?: $booking->total, $booking->currency) }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <span>{{ $this->nightsSummary($booking) }}</span>
                        <span>{{ $this->expiryLabel($booking) }}</span>
                    </div>

                    <flux:button wire:click="selectRequest({{ $booking->id }})" variant="primary" class="w-full data-loading:opacity-70" icon="eye">
                        {{ __('host.requests.actions.review') }}
                    </flux:button>
                </flux:card>
            @endforeach
        </div>
    @endif

    @if ($selectedRequest && $guestSummary && $compatibility)
        <div class="fixed inset-0 z-30 bg-zinc-950/30 sm:hidden" wire:click="closeDetail"></div>

        <section class="fixed inset-x-0 bottom-0 z-40 mx-auto max-h-[88vh] max-w-3xl overflow-y-auto rounded-t-2xl border border-zinc-200 bg-white p-4 shadow-xl dark:border-zinc-800 dark:bg-zinc-950 sm:static sm:max-h-none sm:rounded-xl sm:shadow-none">
            <div class="space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <flux:badge color="emerald" icon="check-circle">{{ __('host.requests.detail.eyebrow') }}</flux:badge>
                        <flux:heading size="lg" level="2">
                            <span class="inline-flex min-w-0 items-center gap-2">
                                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ $this->placeTitle($selectedRequest) }}</span>
                            </span>
                        </flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('host.requests.detail.reference', ['reference' => $selectedRequest->reference]) }}
                        </flux:text>
                    </div>

                    <flux:button wire:click="closeDetail" variant="ghost" icon="x-mark" aria-label="{{ __('host.requests.actions.close') }}" />
                </div>

                <flux:card class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="size-12 shrink-0 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            @if ($guestSummary['avatar_path'])
                                <img
                                    src="{{ str_starts_with((string) $guestSummary['avatar_path'], 'http') ? $guestSummary['avatar_path'] : asset('storage/'.$guestSummary['avatar_path']) }}"
                                    alt="{{ __('host.requests.profile.avatar_alt', ['name' => $guestSummary['name']]) }}"
                                    class="size-full object-cover"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div class="flex size-full items-center justify-center text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                                    {{ mb_substr($guestSummary['name'], 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 space-y-1">
                            <flux:heading size="md">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ $guestSummary['name'] }}</span>
                                </span>
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('host.requests.profile.rating_line', ['rating' => $guestSummary['rating'], 'reviews' => $guestSummary['reviews_count'], 'stays' => $guestSummary['previous_stays_count']]) }}
                            </flux:text>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('host.requests.profile.complaints_count', ['count' => $guestSummary['complaints_count']]) }}
                            </flux:text>
                        </div>
                    </div>

                    <div class="grid gap-2 text-sm">
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.requests.profile.travel_purpose') }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $guestSummary['travel_purpose'] }}</div>
                        </div>

                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.requests.profile.languages') }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $guestSummary['languages'] ? implode(', ', $guestSummary['languages']) : __('host.requests.profile.not_shared') }}
                            </div>
                        </div>
                    </div>

                    @if ($guestSummary['about'])
                        <div class="rounded-lg border border-zinc-200 px-3 py-3 text-sm text-zinc-700 dark:border-zinc-800 dark:text-zinc-300">
                            {{ $guestSummary['about'] }}
                        </div>
                    @endif

                    <div class="space-y-2">
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('host.requests.profile.verification') }}</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($guestSummary['verification'] as $item)
                                <flux:badge color="{{ $item['verified'] ? 'emerald' : 'zinc' }}" icon="check-circle">
                                    {{ $item['verified'] ? __('host.requests.profile.verified_prefix') : __('host.requests.profile.not_verified_prefix') }}
                                    {{ $item['label'] }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('host.requests.profile.relevant_preferences') }}</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($guestSummary['preferences'] as $preference)
                                <flux:badge color="sky" icon="user">{{ $preference }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                </flux:card>

                <flux:card class="space-y-3">
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.requests.fields.dates') }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->dateSummary($selectedRequest) }}</div>
                        </div>

                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.requests.fields.nights') }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->nightsSummary($selectedRequest) }}</div>
                        </div>

                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.requests.fields.total') }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->money($selectedRequest->total_amount ?: $selectedRequest->total, $selectedRequest->currency) }}</div>
                        </div>

                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.requests.fields.deposit') }}</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->money($selectedRequest->deposit_amount ?: $selectedRequest->deposit, $selectedRequest->currency) }}</div>
                        </div>
                    </div>

                    @if ($selectedRequest->guest_message)
                        <div class="rounded-lg border border-zinc-200 px-3 py-3 text-sm text-zinc-700 dark:border-zinc-800 dark:text-zinc-300">
                            <div class="mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('host.requests.fields.guest_message') }}</div>
                            {{ $selectedRequest->guest_message }}
                        </div>
                    @endif
                </flux:card>

                <flux:card class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('host.requests.compatibility.title') }}</div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('host.requests.compatibility.score', ['score' => $compatibility['score'], 'level' => $compatibility['fit_level']]) }}</div>
                        </div>
                    </div>

                    @if ($compatibility['warnings'])
                        <div class="space-y-2">
                            @foreach ($compatibility['warnings'] as $warning)
                                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
                                    {{ $warning }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                            {{ __('host.requests.compatibility.no_warnings') }}
                        </div>
                    @endif
                </flux:card>

                <flux:card class="space-y-4">
                    <flux:textarea
                        wire:model.blur="acceptMessage"
                        rows="3"
                        label="{{ __('host.requests.fields.accept_message') }}"
                        placeholder="{{ __('host.requests.placeholders.accept_message') }}"
                    />

                    <flux:input
                        type="datetime-local"
                        wire:model.change="expiryAt"
                        label="{{ __('host.requests.fields.expiry') }}"
                        :error="$errors->first('expiryAt') ?: $errors->first('paymentDeadline')" icon="calendar-days" />

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <flux:button wire:click="acceptSelected" variant="primary" class="data-loading:opacity-70" icon="check">
                            {{ __('host.requests.actions.accept') }}
                        </flux:button>

                        <flux:button wire:click="saveExpiry" variant="filled" class="data-loading:opacity-70" icon="check">
                            {{ __('host.requests.actions.save_expiry') }}
                        </flux:button>
                    </div>
                </flux:card>

                <flux:card class="space-y-4">
                    <flux:textarea
                        wire:model.blur="hostMessage"
                        rows="3"
                        label="{{ __('host.requests.fields.message') }}"
                        placeholder="{{ __('host.requests.placeholders.message') }}"
                        :error="$errors->first('hostMessage')"
                    />

                    <flux:button wire:click="sendMessage" variant="filled" class="w-full data-loading:opacity-70" icon="paper-airplane">
                        {{ __('host.requests.actions.send_message') }}
                    </flux:button>
                </flux:card>

                <flux:card class="space-y-4">
                    <flux:select
                        wire:model.change="declineReason"
                        label="{{ __('host.requests.fields.decline_reason') }}"
                        :error="$errors->first('declineReason')"
                    >
                        <flux:select.option value="">{{ __('host.requests.placeholders.decline_reason') }}</flux:select.option>
                        @foreach ($declineReasons as $reasonKey => $reasonLabel)
                            <flux:select.option value="{{ $reasonKey }}">{{ $reasonLabel }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:textarea
                        wire:model.blur="declineMessage"
                        rows="3"
                        label="{{ __('host.requests.fields.decline_message') }}"
                        placeholder="{{ __('host.requests.placeholders.decline_message') }}"
                        :error="$errors->first('declineMessage')"
                    />

                    <flux:button wire:click="declineSelected" variant="danger" class="w-full data-loading:opacity-70" icon="x-mark">
                        {{ __('host.requests.actions.decline') }}
                    </flux:button>
                </flux:card>
            </div>
        </section>
    @endif
</x-ui.page>
