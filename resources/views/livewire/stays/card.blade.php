<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="lg">{{ __('stays.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('stays.messages.current_stay_helper') }}
            </flux:text>
        </div>

        @if ($summary)
            <flux:badge color="emerald">{{ $summary['status'] }}</flux:badge>
        @endif
    </div>

    @if ($summary)
        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <flux:text class="font-medium">{{ $summary['sleeping_place'] }}</flux:text>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $summary['room'] }} · {{ $summary['property'] }}</flux:text>
                </div>
                <flux:text size="sm">{{ $summary['dates'] }}</flux:text>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                <div>
                    <flux:text size="xs" class="text-zinc-500">{{ __('stays.fields.nights_remaining') }}</flux:text>
                    <flux:text>{{ $summary['nights_remaining'] }}</flux:text>
                </div>
                <div>
                    <flux:text size="xs" class="text-zinc-500">{{ __('stays.fields.payment_status') }}</flux:text>
                    <flux:text>{{ $summary['payment_status'] }}</flux:text>
                </div>
            </div>
        </div>

        @if (in_array($variant, ['guest_page', 'roommates', 'compatibility', 'visibility']))
            <section class="space-y-3">
                <flux:heading size="md">{{ __('occupants.title') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('occupants.messages.roommates_summary_private') }}</flux:text>

                <div class="space-y-2">
                    @forelse ($roommates as $roommate)
                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <flux:text class="font-medium">{{ $roommate['label'] ?? __('occupants.messages.roommate') }}</flux:text>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @if (! empty($roommate['stay_purpose']))
                                    <flux:badge size="sm">{{ __('occupants.purposes.'.$roommate['stay_purpose']) }}</flux:badge>
                                @endif
                                @if (! empty($roommate['age_range']))
                                    <flux:badge size="sm">{{ $roommate['age_range'] }}</flux:badge>
                                @endif
                                @if (! empty($roommate['sociability_level']))
                                    <flux:badge size="sm">{{ __('occupants.sociability.'.$roommate['sociability_level']) }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    @empty
                        <flux:text size="sm">{{ __('occupants.messages.no_current_roommates') }}</flux:text>
                    @endforelse
                </div>
            </section>
        @endif

        @if (in_array($variant, ['guest_page', 'compatibility']))
            <section class="space-y-2">
                <flux:heading size="md">{{ __('stays.components.compatibility') }}</flux:heading>
                @forelse ($warnings as $warning)
                    <flux:callout variant="warning">{{ __('stays.compatibility.'.$warning) }}</flux:callout>
                @empty
                    <flux:text size="sm">{{ __('stays.messages.no_compatibility_warnings') }}</flux:text>
                @endforelse
            </section>
        @endif

        @if (in_array($variant, ['guest_page', 'actions']))
            <div class="grid gap-2">
                <flux:button variant="primary" class="w-full">{{ __('stays.actions.message_host') }}</flux:button>
                <flux:button class="w-full">{{ __('stays.actions.request_extension') }}</flux:button>
                <flux:button class="w-full">{{ __('stays.actions.request_relocation') }}</flux:button>
                <x-ui.report-problem-button class="w-full">{{ __('stays.actions.report_problem') }}</x-ui.report-problem-button>
            </div>
        @endif
    @else
        <flux:text>{{ __('stays.empty.no_current_stay') }}</flux:text>
    @endif
</flux:card>
