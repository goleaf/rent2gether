@php($page = __('shell.pages.'.$pageKey))

<div class="mx-auto max-w-5xl space-y-5">
    <section class="space-y-3">
        <flux:badge color="emerald">{{ $page['eyebrow'] }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">{{ $page['title'] }}</flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                {{ $page['helper'] }}
            </flux:text>
        </div>

        @if($actionHref)
            <flux:button href="{{ $actionHref }}" variant="primary" wire:navigate class="data-loading:opacity-70">
                {{ $page['action'] }}
            </flux:button>
        @endif
    </section>

    <livewire:favorites.favorites-list />
</div>
