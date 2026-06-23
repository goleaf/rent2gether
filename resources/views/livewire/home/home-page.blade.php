<x-ui.page class="text-zinc-950 dark:text-white">
    <x-ui.section>
        <div class="space-y-2">
            <flux:badge color="lime" icon="home-modern">{{ __('domain.entities.sleeping_place') }}</flux:badge>
            <flux:heading size="xl">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('common.home.title') }}</span>
                </span>
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('common.home.helper') }}</flux:text>
        </div>

        <x-ui.surface class="grid gap-3">
            <flux:input wire:model.live.debounce.500ms="destination" :label="__('common.home.destination')" :placeholder="__('common.home.destination_placeholder')" icon="pencil-square" />

            <div class="grid grid-cols-2 gap-3">
                <flux:input type="date" :label="__('common.home.check_in')" icon="calendar-days" />
                <flux:input type="date" :label="__('common.home.check_out')" icon="calendar-days" />
            </div>

            <flux:input type="number" min="1" :label="__('common.home.guests')" icon="user" />

            <flux:button variant="primary" class="w-full" data-loading wire:loading.attr="disabled" icon="magnifying-glass">
                {{ __('common.actions.find_sleeping_place') }}
            </flux:button>
        </x-ui.surface>

        <x-ui.surface class="border-lime-200 bg-lime-50 text-sm text-lime-950 dark:border-lime-900 dark:bg-lime-950 dark:text-lime-50">
            {{ __('domain.main_rule.sleeping_place_is_rental_unit') }}
        </x-ui.surface>
    </x-ui.section>
</x-ui.page>
