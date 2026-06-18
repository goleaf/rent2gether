<div class="space-y-6">
        <flux:card class="space-y-4">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('app.health_heading') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('app.health_text') }}</flux:text>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 p-4 dark:border-white/10">
                    <div class="text-sm text-zinc-500">{{ __('app.health_items.locale') }}</div>
                    <div class="mt-1 font-medium">{{ app()->getLocale() }}</div>
                </div>
                <div class="rounded-2xl border border-zinc-200 p-4 dark:border-white/10">
                    <div class="text-sm text-zinc-500">{{ __('app.health_items.database') }}</div>
                    <div class="mt-1 font-medium">{{ config('database.default') }}</div>
                </div>
            </div>
        </flux:card>
    </div>
