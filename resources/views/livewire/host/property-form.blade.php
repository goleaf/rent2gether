<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ $property ? __('Edit Property') : __('New Property') }}</flux:heading>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Basic Info') }}</flux:heading>
            <flux:input wire:model="title" label="{{ __('Title') }}" :error="$errors->first('title')" />
            <flux:select wire:model="type" label="{{ __('Type') }}" :error="$errors->first('type')">
                <option value="">{{ __('Select...') }}</option>
                @foreach($this->propertyTypes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
            <flux:textarea wire:model="description" label="{{ __('Description') }}" rows="3" />
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Location') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="country" label="{{ __('Country') }}" :error="$errors->first('country')" />
                <flux:input wire:model="city" label="{{ __('City') }}" :error="$errors->first('city')" />
                <flux:input wire:model="district" label="{{ __('District') }}" />
                <flux:input wire:model="street" label="{{ __('Street') }}" />
                <flux:input wire:model="building" label="{{ __('Building') }}" />
                <flux:input wire:model="apartment" label="{{ __('Apartment') }}" />
                <flux:input type="number" wire:model="floor" label="{{ __('Floor') }}" />
            </div>
            <flux:checkbox wire:model="hasElevator" label="{{ __('Has elevator') }}" />
            <flux:input wire:model="nearestTransport" label="{{ __('Nearest transport') }}" />
            <flux:textarea wire:model="accessInstructions" label="{{ __('Access instructions') }}" rows="2" />
        </flux:card>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ $property ? __('Update') : __('Create') }}</flux:button>
            <flux:button href="{{ route('host.properties.index', ['locale' => app()->getLocale()]) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
