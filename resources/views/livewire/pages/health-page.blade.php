<div class="space-y-6">
        <flux:card class="space-y-4">
            <div class="space-y-2">
                <flux:heading size="xl">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="information-circle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('app.health_heading') }}</span>
                    </span>
                </flux:heading>
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

            <flux:button
                href="{{ route('home', ['locale' => app()->getLocale()]) }}"
                variant="primary"
                icon="arrow-left"
                wire:navigate
            >
                {{ __('app.back_home') }}
            </flux:button>
        </flux:card>
    </div>
