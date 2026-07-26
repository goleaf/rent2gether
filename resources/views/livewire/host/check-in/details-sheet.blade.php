<div class="space-y-3">
    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-normal text-zinc-500">
                    {{ __('check_in.components.' . $variant) }}
                </p>
                <h2 class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">
                    {{ __('check_in.host_title') }}
                </h2>
            </div>

            <flux:badge color="{{ $status === 'checked_in' ? 'emerald' : ($status === 'problem_reported' || $status === 'host_unresponsive' ? 'amber' : 'zinc') }}" icon="exclamation-triangle">
                {{ __('check_in.statuses.' . $status) }}
            </flux:badge>
        </div>

        @if ($booking)
            <div class="grid grid-cols-1 gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_in.fields.guest') }}</span>
                    <span class="font-medium">{{ $booking->guest?->name ?? __('check_in.empty.unknown_guest') }}</span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_in.fields.sleeping_place') }}</span>
                    <span class="font-medium">
                        {{ $booking->room?->title ?? __('check_in.empty.unknown_room') }}
                        {{ $booking->sleepingPlace?->display_name ? ' · ' . $booking->sleepingPlace->display_name : '' }}
                    </span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_in.fields.planned_check_in_time') }}</span>
                    <span class="font-medium">
                        {{ $booking->check_in_date?->format('Y-m-d') }}
                        {{ $booking->arrival_time ? ' · ' . $booking->arrival_time->format('H:i') : '' }}
                    </span>
                </div>
            </div>
        @else
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('check_in.empty.no_booking') }}
            </p>
        @endif

        @if ($steps->isNotEmpty())
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('check_in.sections.steps') }}</p>
                @foreach ($steps as $step)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <span class="min-w-0 text-zinc-700 dark:text-zinc-200">{{ __('check_in.step_keys.' . $step->step_key) }}</span>
                        <flux:badge color="{{ $step->status === 'completed' ? 'emerald' : 'zinc' }}" icon="check-circle">
                            {{ __('check_in.item_statuses.' . $step->status) }}
                        </flux:badge>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($problems->isNotEmpty())
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('check_in.sections.problems') }}</p>
                @foreach ($problems as $problem)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100">
                        <p class="font-medium">{{ __('check_in.problem_types.' . $problem->problem_type) }}</p>
                        <p>{{ $problem->description ?: __('check_in.empty.no_problem_description') }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($variant === 'host_details_sheet')
            <form wire:submit="saveChecklist" class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('check_in.sections.host_checklist') }}</p>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('check_in.fields.actual_arrival_time') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="time" wire:model.change="actualArrivalTime" icon="clock" />
                    <flux:error name="actualArrivalTime" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="user" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('check_in.fields.met_by') }}</span>
                        </span>
                    </flux:label>
                    <flux:input wire:model.blur="metByName" icon="user" />
                    <flux:error name="metByName" />
                </flux:field>

                <div class="grid grid-cols-1 gap-3 text-sm">
                    <label class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        <flux:checkbox wire:model.change="keysHandedOver" />
                        <span>{{ __('check_in.fields.keys_handed_over') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        <flux:checkbox wire:model.change="doorCodeShared" />
                        <span>{{ __('check_in.fields.door_code_shared') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        <flux:checkbox wire:model.change="roomShown" />
                        <span>{{ __('check_in.fields.room_shown') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        <flux:checkbox wire:model.change="sleepingPlaceShown" />
                        <span>{{ __('check_in.fields.sleeping_place_shown') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        <flux:checkbox wire:model.change="rulesExplained" />
                        <span>{{ __('check_in.fields.rules_explained') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        <flux:checkbox wire:model.change="beddingGiven" />
                        <span>{{ __('check_in.fields.bedding_given') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        <flux:checkbox wire:model.change="towelGiven" />
                        <span>{{ __('check_in.fields.towel_given') }}</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        <flux:checkbox wire:model.change="lockerGiven" />
                        <span>{{ __('check_in.fields.locker_given') }}</span>
                    </label>
                </div>

                <flux:button type="submit" variant="filled" class="w-full" wire:loading.attr="disabled" icon="check-circle">
                    {{ __('check_in.actions.save_checklist') }}
                </flux:button>
            </form>
        @endif

        @if ($variant === 'host_confirm_button' || $variant === 'host_details_sheet')
            <flux:button type="button" variant="primary" class="w-full" wire:click="confirm" wire:loading.attr="disabled" icon="key">
                {{ __('check_in.actions.host_confirm_check_in') }}
            </flux:button>
        @endif

        @if ($variant === 'host_instruction_sender')
            <flux:button type="button" variant="primary" class="w-full" wire:click="sendInstruction" wire:loading.attr="disabled" icon="paper-airplane">
                {{ __('check_in.actions.send_instruction') }}
            </flux:button>
        @endif
    </flux:card>
</div>
