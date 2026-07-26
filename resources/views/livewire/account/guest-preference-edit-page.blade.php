<x-ui.page>
    <section class="space-y-2">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="cog-6-tooth" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('preferences.edit.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('preferences.edit.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-5">
        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('preferences.sections.budget') }}</span>
                </span>
            </flux:heading>
            @include('livewire.account.partials.guest-preference-section', ['section' => 'budget'])
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('preferences.sections.place') }}</span>
                </span>
            </flux:heading>
            @include('livewire.account.partials.guest-preference-section', ['section' => 'place'])
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('preferences.sections.comfort') }}</span>
                </span>
            </flux:heading>
            @include('livewire.account.partials.guest-preference-section', ['section' => 'comfort'])
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('preferences.sections.lifestyle') }}</span>
                </span>
            </flux:heading>
            @include('livewire.account.partials.guest-preference-section', ['section' => 'lifestyle'])
        </flux:card>

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="check">
                <span wire:loading.remove wire:target="save">{{ __('preferences.actions.save') }}</span>
                <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
            </flux:button>
        </div>
    </form>

    <livewire:profile.guest-compatibility-profile-form />
</x-ui.page>
