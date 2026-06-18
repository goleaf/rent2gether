<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('My Properties') }}</flux:heading>
        <flux:button href="{{ route('host.properties.create', ['locale' => app()->getLocale()]) }}" variant="primary" icon="plus">
            {{ __('Add Property') }}
        </flux:button>
    </div>

    <div class="space-y-4">
        @forelse($this->properties as $property)
            <flux:card class="flex items-center justify-between">
                <div class="space-y-1">
                    <a href="{{ route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $property]) }}">
                        <flux:heading size="sm" class="hover:text-blue-600 transition">{{ $property->title }}</flux:heading>
                    </a>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $property->city }}, {{ $property->country }}
                        &middot; {{ $property->rooms_count }} {{ __('rooms') }}
                        &middot; {{ $property->beds_count }} {{ __('beds') }}
                    </flux:text>
                    <flux:badge size="sm">{{ $property->type->label() }}</flux:badge>
                    <flux:badge size="sm" color="{{ $property->status->value === 'active' ? 'green' : 'zinc' }}">{{ $property->status->label() }}</flux:badge>
                </div>
                <flux:button size="sm" href="{{ route('host.properties.edit', ['locale' => app()->getLocale(), 'property' => $property]) }}" icon="pencil" variant="ghost" />
            </flux:card>
        @empty
            <flux:card>
                <flux:text class="text-center text-zinc-500 py-8">{{ __('No properties yet. Add your first property to start hosting.') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</div>
