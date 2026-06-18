<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Edit Profile') }}</flux:heading>

    @if(session('success'))
        <flux:badge color="green">{{ session('success') }}</flux:badge>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Personal Information') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="name" label="{{ __('Name') }}" :error="$errors->first('name')" />
                <flux:input wire:model="phone" label="{{ __('Phone') }}" :error="$errors->first('phone')" />
                <flux:input type="date" wire:model="dateOfBirth" label="{{ __('Date of birth') }}" />
                <flux:select wire:model="gender" label="{{ __('Gender') }}">
                    <option value="">-</option>
                    <option value="male">{{ __('Male') }}</option>
                    <option value="female">{{ __('Female') }}</option>
                    <option value="other">{{ __('Other') }}</option>
                </flux:select>
                <flux:input wire:model="country" label="{{ __('Country') }}" />
                <flux:input wire:model="city" label="{{ __('City') }}" />
                <flux:input wire:model="occupation" label="{{ __('Occupation') }}" />
            </div>
            <flux:textarea wire:model="bio" label="{{ __('About me') }}" rows="3" />
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Lifestyle & Preferences') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model="sleepSchedule" label="{{ __('Sleep schedule') }}">
                    <option value="">-</option>
                    <option value="early_bird">{{ __('Early bird') }}</option>
                    <option value="night_owl">{{ __('Night owl') }}</option>
                    <option value="flexible">{{ __('Flexible') }}</option>
                </flux:select>
                <flux:select wire:model="travelPurpose" label="{{ __('Travel purpose') }}">
                    <option value="">-</option>
                    <option value="tourism">{{ __('Tourism') }}</option>
                    <option value="work">{{ __('Work') }}</option>
                    <option value="study">{{ __('Study') }}</option>
                    <option value="relocation">{{ __('Relocation') }}</option>
                </flux:select>
            </div>
            <div class="flex flex-wrap gap-4">
                <flux:checkbox wire:model="isSmoker" label="{{ __('Smoker') }}" />
                <flux:checkbox wire:model="hasPets" label="{{ __('Has pets') }}" />
                <flux:checkbox wire:model="hasAllergies" label="{{ __('Has allergies') }}" />
                <flux:checkbox wire:model="prefersQuiet" label="{{ __('Prefers quiet') }}" />
                <flux:checkbox wire:model="willingToShareRoom" label="{{ __('Willing to share room') }}" />
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Host Profile') }}</flux:heading>
            <flux:checkbox wire:model="isHost" label="{{ __('I am also a host') }}" />
            @if($isHost)
                <flux:textarea wire:model="hostDescription" label="{{ __('Host description') }}" rows="3" />
                <flux:input type="number" wire:model="hostExperienceYears" label="{{ __('Years of experience') }}" min="0" />
                <flux:checkbox wire:model="hostLivesOnSite" label="{{ __('I live on site') }}" />
            @endif
        </flux:card>

        <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
    </form>
</div>
