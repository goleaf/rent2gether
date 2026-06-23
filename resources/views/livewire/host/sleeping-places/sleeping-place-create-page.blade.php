<x-ui.page class="space-y-0 flex min-h-screen flex-col gap-5">
    <div class="space-y-2">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('sleeping_places.create.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('sleeping_places.create.helper') }}</flux:text>
    </div>

    <div class="rounded-lg border border-lime-200 bg-lime-50 p-4 text-sm text-lime-950 dark:border-lime-900 dark:bg-lime-950 dark:text-lime-50">
        {{ __('domain.main_rule.sleeping_place_is_rental_unit') }}
    </div>

    <div class="fixed inset-x-0 bottom-0 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto w-full max-w-5xl">
            <flux:button variant="primary" class="w-full" icon="plus">{{ __('sleeping_places.actions.start') }}</flux:button>
        </div>
    </div>
</x-ui.page>
