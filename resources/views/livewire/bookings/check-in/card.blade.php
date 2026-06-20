<div class="space-y-3">
    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-normal text-zinc-500">
                    {{ __('check_in.components.' . $variant) }}
                </p>
                <h2 class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">
                    {{ __('check_in.title') }}
                </h2>
            </div>

            <flux:badge color="{{ $status === 'checked_in' ? 'emerald' : ($status === 'check_in_problem' || $status === 'waiting_for_resolution' ? 'amber' : 'zinc') }}">
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
                    <span class="block text-xs text-zinc-500">{{ __('check_in.fields.room') }}</span>
                    <span class="font-medium">
                        {{ $booking->room?->title ?? __('check_in.empty.unknown_room') }}
                        {{ $booking->sleepingPlace?->display_name ? ' · ' . $booking->sleepingPlace->display_name : '' }}
                    </span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_in.fields.check_in_date') }}</span>
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

        @isset($instructions)
            <div class="space-y-2 rounded-lg bg-zinc-50 p-3 text-sm dark:bg-zinc-900">
                <p class="font-medium text-zinc-950 dark:text-white">{{ __('check_in.fields.address') }}</p>
                <p class="text-zinc-700 dark:text-zinc-200">
                    {{ $instructions['exact_address'] ?? $instructions['approximate_area'] ?? __('check_in.privacy.address_hidden') }}
                </p>
                <p class="text-zinc-600 dark:text-zinc-300">
                    {{ $instructions['instructions'] ?? __('check_in.privacy.instructions_hidden') }}
                </p>
            </div>
        @endisset

        @if ($items->isNotEmpty())
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('check_in.sections.checklist') }}</p>
                <div class="space-y-2">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                            <span class="min-w-0 text-zinc-700 dark:text-zinc-200">{{ __($item->label_key) }}</span>
                            <flux:badge color="{{ $item->status === 'completed' ? 'emerald' : 'zinc' }}">
                                {{ __('check_in.item_statuses.' . $item->status) }}
                            </flux:badge>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($reports->isNotEmpty())
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('check_in.sections.problems') }}</p>
                @foreach ($reports as $report)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100">
                        <p class="font-medium">{{ __('check_in.problems.' . $report->problem_type) }}</p>
                        <p>{{ $report->description ?: __('check_in.empty.no_problem_description') }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($variant === 'arrival_button' || $variant === 'guest_page')
            <flux:button type="button" variant="primary" class="w-full" wire:click="markArrived" wire:loading.attr="disabled">
                {{ __('check_in.actions.i_arrived') }}
            </flux:button>
        @endif

        @if ($variant === 'guest_confirm_button')
            <flux:button type="button" variant="primary" class="w-full" wire:click="confirm" wire:loading.attr="disabled">
                {{ __('check_in.actions.confirm_check_in') }}
            </flux:button>
        @endif

        @if ($variant === 'host_confirm_button')
            <flux:button type="button" variant="primary" class="w-full" wire:click="confirm" wire:loading.attr="disabled">
                {{ __('check_in.actions.confirm_check_in') }}
            </flux:button>
        @endif

        @if ($variant === 'problem_sheet')
            <form wire:submit="report" class="space-y-3">
                <flux:select wire:model.change="problemType" :label="__('check_in.fields.problem_type')">
                    @foreach (array_keys(__('check_in.problems')) as $problemType)
                        <flux:select.option value="{{ $problemType }}">{{ __('check_in.problems.' . $problemType) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.change="severity" :label="__('check_in.fields.problem_severity')">
                    @foreach (array_keys(__('check_in.severities')) as $severityKey)
                        <flux:select.option value="{{ $severityKey }}">{{ __('check_in.severities.' . $severityKey) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:textarea wire:model.blur="description" :label="__('check_in.fields.problem_description')" />
                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    {{ __('check_in.actions.problem') }}
                </flux:button>
            </form>
        @endif

        @if ($variant === 'problem_button')
            <flux:button type="button" variant="danger" class="w-full">
                {{ __('check_in.actions.problem') }}
            </flux:button>
        @endif
    </flux:card>
</div>
