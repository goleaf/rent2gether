<section class="mx-auto w-full max-w-xl space-y-4 px-4 py-4">
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="amber">{{ __('listing_mismatch.components.host_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">{{ __('listing_mismatch.host_title') }}</flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('listing_mismatch.messages.host_intro') }}
                </flux:text>
            </div>

            @if ($report)
                <flux:badge color="red">{{ __('listing_mismatch.statuses.' . $report->status) }}</flux:badge>
            @endif
        </div>

        @if ($report)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.guest') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">{{ $report->guest?->name ?? __('listing_mismatch.empty.unknown_guest') }}</span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.sleeping_place') }}</span>
                    {{ $report->sleepingPlace?->display_name ?? $report->sleepingPlace?->title ?? __('listing_mismatch.empty.unknown_place') }}
                </div>
            </div>

            <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100">
                <p class="font-medium">{{ __('listing_mismatch.messages.snapshot_used') }}</p>
                <p class="mt-1">{{ __('listing_mismatch.messages.unconfirmed_no_rating_impact') }}</p>
            </div>

            <div class="mt-4 grid gap-3">
                <flux:textarea wire:model.blur="hostMessage" :label="__('listing_mismatch.fields.host_response')" rows="3" />
                <flux:input type="number" step="0.01" wire:model.blur="amount" :label="__('listing_mismatch.fields.amount')" />

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <flux:button wire:click="acceptProblem" wire:loading.attr="disabled">
                        {{ __('listing_mismatch.actions.accept_problem') }}
                    </flux:button>
                    <flux:button wire:click="denyProblem" wire:loading.attr="disabled">
                        {{ __('listing_mismatch.actions.deny_problem') }}
                    </flux:button>
                    <flux:button wire:click="askMoreEvidence" wire:loading.attr="disabled">
                        {{ __('listing_mismatch.actions.ask_more_evidence') }}
                    </flux:button>
                    <flux:button wire:click="offerFix" wire:loading.attr="disabled">
                        {{ __('listing_mismatch.actions.offer_fix') }}
                    </flux:button>
                    <flux:button wire:click="offerCleaning" wire:loading.attr="disabled">
                        {{ __('listing_mismatch.actions.offer_cleaning') }}
                    </flux:button>
                    <flux:button wire:click="offerRepair" wire:loading.attr="disabled">
                        {{ __('listing_mismatch.actions.offer_repair') }}
                    </flux:button>
                    <flux:button wire:click="offerRelocation" wire:loading.attr="disabled">
                        {{ __('listing_mismatch.actions.offer_relocation') }}
                    </flux:button>
                    <flux:button wire:click="offerRefund" wire:loading.attr="disabled">
                        {{ __('listing_mismatch.actions.offer_refund') }}
                    </flux:button>
                    <flux:button wire:click="offerCompensation" wire:loading.attr="disabled">
                        {{ __('listing_mismatch.actions.offer_compensation') }}
                    </flux:button>
                </div>
            </div>

            <div class="mt-4 rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.mismatch_type') }}</span>
                        {{ __('listing_mismatch.types.' . $report->mismatch_type) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.severity') }}</span>
                        {{ __('listing_mismatch.severities.' . $report->severity) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.what_was_promised') }}</span>
                        {{ $report->what_was_promised ?? __('listing_mismatch.empty.not_compared') }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('listing_mismatch.fields.what_was_actual') }}</span>
                        {{ $report->what_was_actual ?? __('listing_mismatch.empty.no_guest_detail') }}
                    </div>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                <flux:heading size="sm">{{ __('listing_mismatch.fields.items') }}</flux:heading>
                @forelse ($report->items as $item)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <p class="font-medium text-zinc-950 dark:text-white">{{ __('listing_mismatch.item_keys.' . $item->item_key) }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ __('listing_mismatch.item_types.' . $item->item_type) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('listing_mismatch.empty.no_items') }}</p>
                @endforelse
            </div>

            <div class="mt-4 space-y-3">
                <flux:heading size="sm">{{ __('listing_mismatch.fields.media') }}</flux:heading>
                @forelse ($report->media as $media)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <p class="font-medium text-zinc-950 dark:text-white">{{ __('listing_mismatch.media_roles.' . $media->media_role) }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ $media->caption ?? __('listing_mismatch.empty.no_caption') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('listing_mismatch.empty.no_media') }}</p>
                @endforelse
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($reports as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->guest?->name ?? __('listing_mismatch.empty.unknown_guest') }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            {{ $item->sleepingPlace?->display_name ?? $item->sleepingPlace?->title ?? __('listing_mismatch.empty.unknown_place') }}
                        </p>
                    </div>
                    <flux:badge>{{ __('listing_mismatch.statuses.' . $item->status) }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('listing_mismatch.empty.no_reports') }}
            </div>
        @endforelse
    </div>
</section>
