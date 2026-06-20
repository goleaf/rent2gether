<div class="min-h-screen bg-zinc-50 px-4 pb-24 pt-6 text-zinc-950 dark:bg-zinc-950 dark:text-white">
    <section class="mx-auto flex w-full max-w-xl flex-col gap-5">
        <div class="space-y-2">
            <flux:badge color="lime">{{ __('domain.entities.sleeping_place') }}</flux:badge>
            <flux:heading size="xl">{{ __('common.home.title') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('common.home.helper') }}</flux:text>
        </div>

        <div class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:input wire:model.live.debounce.500ms="destination" :label="__('common.home.destination')" :placeholder="__('common.home.destination_placeholder')" />

            <div class="grid grid-cols-2 gap-3">
                <flux:input type="date" :label="__('common.home.check_in')" />
                <flux:input type="date" :label="__('common.home.check_out')" />
            </div>

            <flux:input type="number" min="1" :label="__('common.home.guests')" />

            <flux:button variant="primary" class="w-full" data-loading wire:loading.attr="disabled">
                {{ __('common.actions.find_sleeping_place') }}
            </flux:button>
        </div>

        <div class="rounded-lg border border-lime-200 bg-lime-50 p-4 text-sm text-lime-950 dark:border-lime-900 dark:bg-lime-950 dark:text-lime-50">
            {{ __('domain.main_rule.sleeping_place_is_rental_unit') }}
        </div>
    </section>
</div>
