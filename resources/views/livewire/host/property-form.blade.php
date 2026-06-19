<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ $property ? __('listing.form.edit_property') : __('listing.form.new_property') }}</flux:heading>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('listing.form.basic_info') }}</flux:heading>
            <flux:input wire:model="title" label="{{ __('listing.form.title') }}" :error="$errors->first('title')" />
            <flux:select wire:model="type" label="{{ __('listing.form.type') }}" :error="$errors->first('type')">
                <option value="">{{ __('listing.form.select') }}</option>
                @foreach($this->propertyTypes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
            <flux:textarea wire:model="description" label="{{ __('listing.form.description') }}" rows="3" />
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('listing.form.location') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="country" label="{{ __('listing.form.country') }}" :error="$errors->first('country')" />
                <flux:input wire:model="city" label="{{ __('listing.form.city') }}" :error="$errors->first('city')" />
                <flux:input wire:model="district" label="{{ __('listing.form.district') }}" />
                <flux:input wire:model="street" label="{{ __('listing.form.street') }}" />
                <flux:input wire:model="building" label="{{ __('listing.form.building') }}" />
                <flux:input wire:model="apartment" label="{{ __('listing.form.apartment') }}" />
                <flux:input type="number" wire:model="floor" label="{{ __('listing.form.floor') }}" />
            </div>
            <flux:checkbox wire:model="hasElevator" label="{{ __('listing.form.has_elevator') }}" />
            <flux:input wire:model="nearestTransport" label="{{ __('listing.form.nearest_transport') }}" />
            <flux:textarea wire:model="accessInstructions" label="{{ __('listing.form.access_instructions') }}" rows="2" />
        </flux:card>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ $property ? __('app.actions.update') : __('app.actions.create') }}</flux:button>
            <flux:button href="{{ route('host.properties.index', ['locale' => app()->getLocale()]) }}" variant="ghost">{{ __('app.actions.cancel') }}</flux:button>
        </div>
    </form>
</div>
