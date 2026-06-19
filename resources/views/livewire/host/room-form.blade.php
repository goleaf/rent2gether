<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ $room ? __('listing.form.edit_room') : __('listing.form.new_room') }}</flux:heading>
    <flux:text class="text-zinc-500">{{ $property->title }}</flux:text>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:input wire:model="title" label="{{ __('listing.form.title') }}" :error="$errors->first('title')" />
            <flux:select wire:model="genderType" label="{{ __('listing.form.gender_type') }}">
                @foreach($this->genderTypes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
            <flux:textarea wire:model="description" label="{{ __('listing.form.description') }}" rows="2" />
            <div class="grid grid-cols-2 gap-4">
                <flux:input type="number" wire:model="capacity" label="{{ __('listing.form.capacity') }}" min="1" :error="$errors->first('capacity')" />
                <flux:input type="number" wire:model="areaSqm" label="{{ __('listing.form.area') }}" step="0.1" />
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('listing.form.amenities') }}</flux:heading>
            <div class="grid grid-cols-2 gap-3">
                <flux:checkbox wire:model="hasLock" label="{{ __('listing.form.lock_on_door') }}" />
                <flux:checkbox wire:model="hasWindow" label="{{ __('listing.form.window') }}" />
                <flux:checkbox wire:model="hasWardrobe" label="{{ __('listing.form.wardrobe') }}" />
                <flux:checkbox wire:model="hasDesk" label="{{ __('listing.form.desk') }}" />
                <flux:checkbox wire:model="hasAc" label="{{ __('listing.form.air_conditioning') }}" />
                <flux:checkbox wire:model="hasHeating" label="{{ __('listing.form.heating') }}" />
                <flux:checkbox wire:model="hasBalcony" label="{{ __('listing.form.balcony') }}" />
            </div>
        </flux:card>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ $room ? __('app.actions.update') : __('app.actions.create') }}</flux:button>
            <flux:button href="{{ route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $property]) }}" variant="ghost">{{ __('app.actions.cancel') }}</flux:button>
        </div>
    </form>
</div>
