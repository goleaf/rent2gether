<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald" icon="check-circle">{{ __('host.profile.edit.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host.profile.edit.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.profile.edit.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host.profile.checklist.title') }}</span>
            </span>
        </flux:heading>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach($this->readinessChecklist() as $item)
                <div class="flex items-center justify-between gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                    <span>{{ $item['label'] }}</span>
                    <flux:badge :color="$item['done'] ? 'green' : 'zinc'" size="sm" icon="check-circle">
                        {{ $item['done'] ? __('host.profile.checklist.done') : __('host.profile.checklist.later') }}
                    </flux:badge>
                </div>
            @endforeach
        </div>
        <flux:text size="sm" class="text-zinc-500">{{ __('host.profile.checklist.payout_helper') }}</flux:text>
    </flux:card>

    <form wire:submit="save" class="space-y-5">
        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('host.profile.sections.identity') }}</span>
                </span>
            </flux:heading>
            @include('livewire.host.partials.host-profile-section', ['section' => 'identity'])
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('host.profile.sections.style') }}</span>
                </span>
            </flux:heading>
            @include('livewire.host.partials.host-profile-section', ['section' => 'style'])
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('host.profile.sections.defaults') }}</span>
                </span>
            </flux:heading>
            @include('livewire.host.partials.host-profile-section', ['section' => 'defaults'])
        </flux:card>

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="check">
                <span wire:loading.remove wire:target="save">{{ __('host.profile.actions.save') }}</span>
                <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
            </flux:button>
        </div>
    </form>
</x-ui.page>
