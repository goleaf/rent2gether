<div class="mx-auto flex min-h-screen w-full max-w-xl flex-col gap-5 px-4 pb-24 pt-6">
    <div class="space-y-2">
        <flux:heading size="lg">{{ __('sleeping_places.create.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('sleeping_places.create.helper') }}</flux:text>
    </div>

    <div class="rounded-lg border border-lime-200 bg-lime-50 p-4 text-sm text-lime-950 dark:border-lime-900 dark:bg-lime-950 dark:text-lime-50">
        {{ __('domain.main_rule.sleeping_place_is_rental_unit') }}
    </div>

    <div class="fixed inset-x-0 bottom-0 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-xl">
            <flux:button variant="primary" class="w-full">{{ __('sleeping_places.actions.start') }}</flux:button>
        </div>
    </div>
</div>
