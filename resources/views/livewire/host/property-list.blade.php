<div class="mx-auto max-w-5xl space-y-5 pb-28 sm:pb-8">
    <section class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-2">
                <flux:badge color="emerald">{{ __('host.listings.properties.eyebrow') }}</flux:badge>
                <flux:heading size="xl" level="1">{{ __('host.my_properties') }}</flux:heading>
                <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">
                    {{ __('host.listings.properties.helper') }}
                </flux:text>
            </div>
        </div>

        <flux:button href="{{ route('host.properties.create', ['locale' => app()->getLocale()]) }}" variant="primary" icon="plus" wire:navigate class="w-full sm:w-auto">
            {{ __('host.add_property') }}
        </flux:button>
    </section>

    <div class="space-y-4">
        @forelse($properties as $property)
            @include('livewire.shell.partials.host-property-card', ['property' => $property])
        @empty
            <flux:card class="space-y-3 text-center">
                <flux:heading size="lg">{{ __('host.listings.properties.empty_title') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.empty_properties') }}</flux:text>
                <flux:button href="{{ route('host.properties.create', ['locale' => app()->getLocale()]) }}" variant="primary" icon="plus" wire:navigate>
                    {{ __('host.add_property') }}
                </flux:button>
            </flux:card>
        @endforelse
    </div>
</div>
