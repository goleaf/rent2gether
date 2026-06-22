<x-ui.page class="space-y-0 flex min-h-screen flex-col gap-5">
    <div class="space-y-2">
        <flux:heading size="lg">{{ __('rooms.create.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('rooms.create.helper') }}</flux:text>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <flux:text>{{ __('rooms.create.mobile_step_helper') }}</flux:text>
    </div>

    <div class="fixed inset-x-0 bottom-0 border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto w-full max-w-5xl">
            <flux:button variant="primary" class="w-full">{{ __('rooms.actions.start') }}</flux:button>
        </div>
    </div>
</x-ui.page>
