<x-ui.page>
    <section class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-2">
                <flux:badge color="emerald" icon="check-circle">{{ __('host.listings.properties.eyebrow') }}</flux:badge>
                <flux:heading size="xl" level="1">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.my_properties') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                    {{ __('host.listings.properties.helper') }}
                </flux:text>
            </div>
        </div>

        <flux:button href="{{ route('host.listings.create', ['locale' => app()->getLocale()]) }}" variant="primary" icon="plus" wire:navigate class="w-full sm:w-auto">
            {{ __('listing_wizard.title') }}
        </flux:button>
    </section>

    <div class="space-y-4">
        @forelse($properties as $property)
            @include('livewire.shell.partials.host-property-card', ['property' => $property])
        @empty
            <flux:card class="space-y-3 text-center">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.listings.properties.empty_title') }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.empty_properties') }}</flux:text>
                <flux:button href="{{ route('host.listings.create', ['locale' => app()->getLocale()]) }}" variant="primary" icon="plus" wire:navigate>
                    {{ __('listing_wizard.title') }}
                </flux:button>
            </flux:card>
        @endforelse
    </div>
</x-ui.page>
