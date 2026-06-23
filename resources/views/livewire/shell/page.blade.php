<x-ui.page>
    <section class="space-y-3">
        <flux:badge color="emerald" icon="check-circle">{{ $page['eyebrow'] }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="information-circle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $page['title'] }}</span>
                </span>
            </flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                {{ $page['helper'] }}
            </flux:text>
        </div>

        @if($actionHref)
            <flux:button href="{{ $actionHref }}" variant="primary" wire:navigate class="data-loading:opacity-70" icon="map-pin">
                {{ $page['action'] }}
            </flux:button>
        @endif
    </section>

    <flux:card class="space-y-4">
        <div class="flex items-start gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                <flux:icon name="{{ $page['icon'] }}" class="size-5" />
            </div>

            <div class="min-w-0 space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="information-circle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $page['empty_title'] }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ $page['empty_text'] }}
                </flux:text>
            </div>
        </div>

        <div class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
            {{ $page['note'] }}
        </div>
    </flux:card>
</x-ui.page>
