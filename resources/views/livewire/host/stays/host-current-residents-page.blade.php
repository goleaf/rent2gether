<section class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('stays.host_title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('stays.messages.host_residents_helper') }}</flux:text>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach ($filters as $key => $label)
            <flux:button
                size="sm"
                :variant="$activeFilter === $key ? 'primary' : 'outline'"
                wire:click="setFilter('{{ $key }}')"
            >
                {{ $label }}
            </flux:button>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($residents ?? [] as $resident)
            <flux:card class="space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <flux:text class="font-medium">{{ $resident->guest?->name }}</flux:text>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ $resident->room?->title }} · {{ $resident->sleepingPlace?->display_name ?: $resident->sleepingPlace?->place_number }}
                        </flux:text>
                    </div>
                    <flux:badge>{{ __('stays.statuses.'.$resident->status) }}</flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <flux:text size="xs" class="text-zinc-500">{{ __('stays.fields.planned_check_out_date') }}</flux:text>
                        <flux:text size="sm">{{ $resident->planned_check_out_date?->format('M j') }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="xs" class="text-zinc-500">{{ __('stays.fields.nights_remaining') }}</flux:text>
                        <flux:text size="sm">{{ $resident->nights_remaining }}</flux:text>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <flux:button size="sm">{{ __('stays.actions.message_guest') }}</flux:button>
                    <flux:button size="sm">{{ __('stays.actions.add_note') }}</flux:button>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text>{{ __('stays.empty.no_current_residents') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</section>
