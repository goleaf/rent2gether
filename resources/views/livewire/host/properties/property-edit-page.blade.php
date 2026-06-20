<div class="mx-auto flex min-h-screen w-full max-w-xl flex-col gap-5 px-4 pb-24 pt-6">
    <div class="space-y-2">
        <flux:heading size="lg">{{ __('properties.edit.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('properties.edit.helper') }}</flux:text>
    </div>

    @if($this->property)
        <livewire:host.properties.property-card :property-id="$propertyId" :key="'property-card-'.$propertyId" />
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
            {{ __('properties.empty.not_found') }}
        </div>
    @endif
</div>
