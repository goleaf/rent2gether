<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ $room ? __('Edit Room') : __('New Room') }}</flux:heading>
    <flux:text class="text-zinc-500">{{ $property->title }}</flux:text>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:input wire:model="title" label="{{ __('Title') }}" :error="$errors->first('title')" />
            <flux:select wire:model="genderType" label="{{ __('Gender type') }}">
                @foreach($this->genderTypes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
            <flux:textarea wire:model="description" label="{{ __('Description') }}" rows="2" />
            <div class="grid grid-cols-2 gap-4">
                <flux:input type="number" wire:model="capacity" label="{{ __('Capacity') }}" min="1" :error="$errors->first('capacity')" />
                <flux:input type="number" wire:model="areaSqm" label="{{ __('Area (m²)') }}" step="0.1" />
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Amenities') }}</flux:heading>
            <div class="grid grid-cols-2 gap-3">
                <flux:checkbox wire:model="hasLock" label="{{ __('Lock on door') }}" />
                <flux:checkbox wire:model="hasWindow" label="{{ __('Window') }}" />
                <flux:checkbox wire:model="hasWardrobe" label="{{ __('Wardrobe') }}" />
                <flux:checkbox wire:model="hasDesk" label="{{ __('Desk') }}" />
                <flux:checkbox wire:model="hasAc" label="{{ __('Air conditioning') }}" />
                <flux:checkbox wire:model="hasHeating" label="{{ __('Heating') }}" />
                <flux:checkbox wire:model="hasBalcony" label="{{ __('Balcony') }}" />
            </div>
        </flux:card>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ $room ? __('Update') : __('Create') }}</flux:button>
            <flux:button href="{{ route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $property]) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
