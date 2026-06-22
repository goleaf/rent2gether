<x-ui.page class="space-y-6">
    <flux:heading size="xl">{{ $bed ? __('listing.form.edit_bed') : __('listing.form.new_bed') }}</flux:heading>
    <flux:text class="text-zinc-500">{{ $room->property->title }} &middot; {{ $room->title }}</flux:text>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('listing.form.basic_info') }}</flux:heading>
            <flux:input wire:model="title" label="{{ __('listing.form.title') }}" :error="$errors->first('title')" />
            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="type" label="{{ __('listing.form.type') }}">
                    @foreach($this->bedTypes() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input type="number" wire:model="maxGuests" label="{{ __('listing.form.max_guests') }}" min="1" />
            </div>
            <flux:textarea wire:model.blur="description" label="{{ __('listing.form.description') }}" rows="2" />
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('listing.form.pricing') }}</flux:heading>
            <div class="grid grid-cols-2 gap-4">
                <flux:input type="number" wire:model="pricePerNight" label="{{ __('listing.form.price_per_night') }}" step="0.01" :error="$errors->first('pricePerNight')" />
                <flux:input type="number" wire:model="priceWeekend" label="{{ __('listing.form.weekend_price') }}" step="0.01" />
                <flux:input type="number" wire:model="priceWeekly" label="{{ __('listing.form.weekly_price') }}" step="0.01" />
                <flux:input type="number" wire:model="priceMonthly" label="{{ __('listing.form.monthly_price') }}" step="0.01" />
                <flux:input type="number" wire:model="cleaningFee" label="{{ __('listing.form.cleaning_fee') }}" step="0.01" />
                <flux:input type="number" wire:model="deposit" label="{{ __('listing.form.deposit') }}" step="0.01" />
                <flux:input type="number" wire:model="discountWeekly" label="{{ __('listing.form.weekly_discount') }}" step="0.1" />
                <flux:input type="number" wire:model="discountMonthly" label="{{ __('listing.form.monthly_discount') }}" step="0.1" />
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('listing.form.stay_rules') }}</flux:heading>
            <div class="grid grid-cols-2 gap-4">
                <flux:input type="number" wire:model="minNights" label="{{ __('listing.form.min_nights') }}" min="1" :error="$errors->first('minNights')" />
                <flux:input type="number" wire:model="maxNights" label="{{ __('listing.form.max_nights') }}" />
            </div>
            <flux:checkbox wire:model="instantBook" label="{{ __('listing.form.instant_booking') }}" />
            <flux:select wire:model="cancellationPolicy" label="{{ __('listing.form.cancellation_policy') }}">
                @foreach($this->cancellationPolicies() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('listing.form.amenities') }}</flux:heading>
            <div class="grid grid-cols-2 gap-3">
                <flux:checkbox wire:model="hasLocker" label="{{ __('listing.form.locker') }}" />
                <flux:checkbox wire:model="hasOutlet" label="{{ __('listing.form.power_outlet') }}" />
                <flux:checkbox wire:model="hasLamp" label="{{ __('listing.form.reading_lamp') }}" />
                <flux:checkbox wire:model="hasCurtain" label="{{ __('listing.form.privacy_curtain') }}" />
                <flux:checkbox wire:model="hasShelf" label="{{ __('listing.form.shelf') }}" />
                <flux:checkbox wire:model="hasLuggageSpace" label="{{ __('listing.form.luggage_space') }}" />
                <flux:checkbox wire:model="hasLinen" label="{{ __('listing.form.bed_linen') }}" />
                <flux:checkbox wire:model="hasTowel" label="{{ __('listing.form.towel') }}" />
            </div>
        </flux:card>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ $bed ? __('app.actions.update') : __('app.actions.create') }}</flux:button>
            <flux:button href="{{ route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $room->property]) }}" variant="ghost">{{ __('app.actions.cancel') }}</flux:button>
        </div>
    </form>
</x-ui.page>
