<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('app.profile.edit') }}</flux:heading>

    @if(session('success'))
        <flux:badge color="green">{{ session('success') }}</flux:badge>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('app.profile.personal_information') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="name" label="{{ __('app.profile.name') }}" :error="$errors->first('name')" />
                <flux:input wire:model="phone" label="{{ __('app.profile.phone') }}" :error="$errors->first('phone')" />
                <flux:input type="date" wire:model="dateOfBirth" label="{{ __('app.profile.date_of_birth') }}" />
                <flux:select wire:model="gender" label="{{ __('app.profile.gender') }}">
                    <option value="">-</option>
                    <option value="male">{{ __('app.profile.male') }}</option>
                    <option value="female">{{ __('app.profile.female') }}</option>
                    <option value="other">{{ __('app.profile.other') }}</option>
                </flux:select>
                <flux:input wire:model="country" label="{{ __('listing.form.country') }}" />
                <flux:input wire:model="city" label="{{ __('listing.form.city') }}" />
                <flux:input wire:model="occupation" label="{{ __('app.profile.occupation') }}" />
            </div>
            <flux:textarea wire:model="bio" label="{{ __('app.profile.about_me') }}" rows="3" />
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('app.profile.lifestyle') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model="sleepSchedule" label="{{ __('app.profile.sleep_schedule') }}">
                    <option value="">-</option>
                    <option value="early_bird">{{ __('app.profile.early_bird') }}</option>
                    <option value="night_owl">{{ __('app.profile.night_owl') }}</option>
                    <option value="flexible">{{ __('app.profile.flexible') }}</option>
                </flux:select>
                <flux:select wire:model="travelPurpose" label="{{ __('app.profile.travel_purpose') }}">
                    <option value="">-</option>
                    <option value="tourism">{{ __('app.profile.tourism') }}</option>
                    <option value="work">{{ __('app.profile.work') }}</option>
                    <option value="study">{{ __('app.profile.study') }}</option>
                    <option value="relocation">{{ __('app.profile.relocation') }}</option>
                </flux:select>
            </div>
            <div class="flex flex-wrap gap-4">
                <flux:checkbox wire:model="isSmoker" label="{{ __('app.profile.smoker') }}" />
                <flux:checkbox wire:model="hasPets" label="{{ __('app.profile.has_pets') }}" />
                <flux:checkbox wire:model="hasAllergies" label="{{ __('app.profile.has_allergies') }}" />
                <flux:checkbox wire:model="prefersQuiet" label="{{ __('app.profile.prefers_quiet') }}" />
                <flux:checkbox wire:model="willingToShareRoom" label="{{ __('app.profile.share_room') }}" />
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('app.profile.host_profile') }}</flux:heading>
            <flux:checkbox wire:model="isHost" label="{{ __('app.profile.is_host') }}" />
            @if($isHost)
                <flux:textarea wire:model="hostDescription" label="{{ __('app.profile.host_description') }}" rows="3" />
                <flux:input type="number" wire:model="hostExperienceYears" label="{{ __('app.profile.experience_years') }}" min="0" />
                <flux:checkbox wire:model="hostLivesOnSite" label="{{ __('app.profile.lives_on_site') }}" />
            @endif
        </flux:card>

        <flux:button type="submit" variant="primary">{{ __('app.actions.save') }}</flux:button>
    </form>
</div>
