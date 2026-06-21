<section class="space-y-3">
    <flux:card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-normal text-zinc-500">
                    {{ __('check_out.components.' . $variant) }}
                </p>
                <h2 class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">
                    {{ __('check_out.host_title') }}
                </h2>
            </div>

            @if ($checkOut)
                <flux:badge color="{{ in_array($checkOut->status, ['completed', 'closed'], true) ? 'emerald' : 'zinc' }}">
                    {{ __('check_out.statuses.' . $checkOut->status) }}
                </flux:badge>
            @endif
        </div>

        @if ($checkOut)
            <div class="grid grid-cols-1 gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_out.fields.guest') }}</span>
                    <span class="font-medium">{{ $checkOut->guest?->name ?? __('check_out.empty.unknown_guest') }}</span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_out.fields.sleeping_place') }}</span>
                    <span class="font-medium">
                        {{ $checkOut->room?->title ?? __('check_out.empty.unknown_room') }}
                        {{ $checkOut->sleepingPlace?->display_name ? ' · ' . $checkOut->sleepingPlace->display_name : '' }}
                    </span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_out.fields.check_out_date') }}</span>
                    <span class="font-medium">
                        {{ $checkOut->check_out_date?->format('Y-m-d') }}
                        {{ $checkOut->planned_check_out_time ? ' · ' . $checkOut->planned_check_out_time : '' }}
                    </span>
                </div>
            </div>

            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">{{ __('check_out.sections.checklist') }}</p>
                @forelse ($checkOut->steps as $step)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <span class="min-w-0 text-zinc-700 dark:text-zinc-200">{{ __('check_out.steps.' . $step->step_key) }}</span>
                        <flux:badge color="{{ $step->status === 'completed' ? 'emerald' : 'zinc' }}">
                            {{ __('check_out.item_statuses.' . $step->status) }}
                        </flux:badge>
                    </div>
                @empty
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('check_out.empty.no_steps') }}</p>
                @endforelse
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_out.fields.cleaning_required') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">{{ $checkOut->cleaning_required ? __('check_out.boolean.yes') : __('check_out.boolean.no') }}</span>
                </div>
                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                    <span class="block text-xs text-zinc-500">{{ __('check_out.fields.deposit_review_required') }}</span>
                    <span class="font-medium text-zinc-950 dark:text-white">{{ $checkOut->deposit_review_required ? __('check_out.boolean.yes') : __('check_out.boolean.no') }}</span>
                </div>
            </div>
        @else
            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('check_out.empty.no_checkout') }}</p>
        @endif
    </flux:card>
</section>
