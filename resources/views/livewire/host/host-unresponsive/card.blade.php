<section class="mx-auto w-full max-w-xl space-y-4 px-4 py-4">
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:badge color="amber">{{ __('host_unresponsive.components.host_' . $variant) }}</flux:badge>
                <flux:heading size="lg" class="mt-3">{{ __('host_unresponsive.host_title') }}</flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-300">
                    {{ __('host_unresponsive.messages.host_intro') }}
                </flux:text>
            </div>

            @if ($case)
                <flux:badge color="red">{{ __('host_unresponsive.statuses.' . $case->status) }}</flux:badge>
            @endif
        </div>

        @if ($booking)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.guest') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">{{ $booking->guest?->name ?? __('host_unresponsive.empty.unknown_guest') }}</span>
                </div>

                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.sleeping_place') }}</span>
                    {{ $booking->sleepingPlace?->display_name ?? $booking->sleepingPlace?->title ?? __('host_unresponsive.empty.unknown_place') }}
                </div>
            </div>
        @endif

        @if ($case)
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-950 dark:border-red-900 dark:bg-red-950/30 dark:text-red-100">
                <p class="font-medium">{{ __('host_unresponsive.messages.host_urgent_heading') }}</p>
                <p class="mt-1">{{ __('host_unresponsive.messages.waiting_until', ['time' => $case->response_deadline_at?->format('H:i') ?? __('host_unresponsive.empty.unknown_time')]) }}</p>
            </div>

            <div class="mt-4 grid gap-3">
                <flux:textarea wire:model.blur="hostMessage" :label="__('host_unresponsive.fields.host_response')" rows="3" />
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <flux:button wire:click="markAvailable" wire:loading.attr="disabled">
                        {{ __('host_unresponsive.host_response_types.i_am_available') }}
                    </flux:button>
                    <flux:button wire:click="sendInstruction" wire:loading.attr="disabled">
                        {{ __('host_unresponsive.actions.send_instruction') }}
                    </flux:button>
                    <flux:button wire:click="sendAccessDetails" wire:loading.attr="disabled">
                        {{ __('host_unresponsive.actions.send_access_details') }}
                    </flux:button>
                    <flux:button wire:click="markAccessResolved" wire:loading.attr="disabled">
                        {{ __('host_unresponsive.actions.mark_access_resolved') }}
                    </flux:button>
                    <flux:button wire:click="denyUnresponsive" wire:loading.attr="disabled">
                        {{ __('host_unresponsive.host_response_types.deny_unresponsive') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="confirmUnresolved" wire:loading.attr="disabled">
                        {{ __('host_unresponsive.actions.confirm_unresolved') }}
                    </flux:button>
                </div>
            </div>

            <div class="mt-4 rounded-md bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.case_type') }}</span>
                        {{ __('host_unresponsive.case_types.' . $case->case_type) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.reason') }}</span>
                        {{ __('host_unresponsive.reasons.' . $case->reason_key) }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.host_contact_attempts_count') }}</span>
                        {{ $case->host_contact_attempts_count }}
                    </div>
                    <div>
                        <span class="block text-xs text-zinc-500">{{ __('host_unresponsive.fields.representative_contact_attempts_count') }}</span>
                        {{ $case->representative_contact_attempts_count }}
                    </div>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                <flux:heading size="sm">{{ __('host_unresponsive.fields.contact_attempts') }}</flux:heading>
                @forelse ($case->contactAttempts as $attempt)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <p class="font-medium text-zinc-950 dark:text-white">{{ __('host_unresponsive.attempt_types.' . $attempt->attempt_type) }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            {{ __('host_unresponsive.contact_channels.' . $attempt->contact_channel) }}
                            {{ __('host_unresponsive.contact_statuses.' . $attempt->status) }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('host_unresponsive.empty.no_attempts') }}</p>
                @endforelse
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($cases as $item)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-zinc-950 dark:text-white">{{ $item->guest?->name ?? __('host_unresponsive.empty.unknown_guest') }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">
                            {{ $item->sleepingPlace?->display_name ?? $item->sleepingPlace?->title ?? __('host_unresponsive.empty.unknown_place') }}
                        </p>
                    </div>
                    <flux:badge>{{ __('host_unresponsive.statuses.' . $item->status) }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                {{ __('host_unresponsive.empty.no_cases') }}
            </div>
        @endforelse
    </div>
</section>
