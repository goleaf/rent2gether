<div class="max-w-3xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('app.profile.edit') }}</flux:heading>

    @if(session('success'))
        <flux:badge color="green">{{ session('success') }}</flux:badge>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('app.profile.personal_information') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model.blur="name" label="{{ __('app.profile.name') }}" :error="$errors->first('name')" />
                <flux:input wire:model.blur="phone" label="{{ __('app.profile.phone') }}" :error="$errors->first('phone')" />
                <flux:input type="date" wire:model.change="dateOfBirth" label="{{ __('app.profile.date_of_birth') }}" />
                <flux:select wire:model.change="gender" label="{{ __('app.profile.gender') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="male">{{ __('app.profile.male') }}</flux:select.option>
                    <flux:select.option value="female">{{ __('app.profile.female') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('app.profile.other') }}</flux:select.option>
                </flux:select>
                <div class="sm:col-span-2">
                    @include('livewire.geo.partials.country-city-autocomplete', ['autocompleteKey' => 'profile-edit'])
                </div>
                <flux:input wire:model.blur="occupation" label="{{ __('app.profile.occupation') }}" />
            </div>
            <flux:textarea wire:model.blur="bio" label="{{ __('app.profile.about_me') }}" rows="3" />
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('app.profile.lifestyle') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model.change="sleepSchedule" label="{{ __('app.profile.sleep_schedule') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="early_bird">{{ __('app.profile.early_bird') }}</flux:select.option>
                    <flux:select.option value="night_owl">{{ __('app.profile.night_owl') }}</flux:select.option>
                    <flux:select.option value="flexible">{{ __('app.profile.flexible') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model.change="travelPurpose" label="{{ __('app.profile.travel_purpose') }}">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="tourism">{{ __('app.profile.tourism') }}</flux:select.option>
                    <flux:select.option value="work">{{ __('app.profile.work') }}</flux:select.option>
                    <flux:select.option value="study">{{ __('app.profile.study') }}</flux:select.option>
                    <flux:select.option value="relocation">{{ __('app.profile.relocation') }}</flux:select.option>
                </flux:select>
            </div>
            <div class="flex flex-wrap gap-4">
                <flux:checkbox wire:model.change="isSmoker" label="{{ __('app.profile.smoker') }}" />
                <flux:checkbox wire:model.change="hasPets" label="{{ __('app.profile.has_pets') }}" />
                <flux:checkbox wire:model.change="hasAllergies" label="{{ __('app.profile.has_allergies') }}" />
                <flux:checkbox wire:model.change="prefersQuiet" label="{{ __('app.profile.prefers_quiet') }}" />
                <flux:checkbox wire:model.change="willingToShareRoom" label="{{ __('app.profile.share_room') }}" />
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('app.profile.host_profile') }}</flux:heading>
            <flux:checkbox wire:model.change="isHost" label="{{ __('app.profile.is_host') }}" />
            @if($this->isHost)
                <flux:textarea wire:model.blur="hostDescription" label="{{ __('app.profile.host_description') }}" rows="3" />
                <flux:field>
                    <flux:label>{{ __('app.profile.experience_started_year') }}</flux:label>
                    <flux:select wire:model.change="hostExperienceStartedYear">
                        <flux:select.option value="">{{ __('app.profile.experience_started_year_placeholder') }}</flux:select.option>
                        @foreach($this->hostExperienceYearOptions as $year)
                            <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>
                        @if($this->calculatedHostExperienceYears === null)
                            {{ __('app.profile.experience_years_empty') }}
                        @else
                            {{ trans_choice('app.profile.experience_years_calculated', $this->calculatedHostExperienceYears, ['count' => $this->calculatedHostExperienceYears]) }}
                        @endif
                    </flux:description>
                    <flux:error name="hostExperienceStartedYear" />
                </flux:field>
                <flux:checkbox wire:model.change="hostLivesOnSite" label="{{ __('app.profile.lives_on_site') }}" />
            @endif
        </flux:card>

        <flux:button type="submit" variant="primary">{{ __('app.actions.save') }}</flux:button>
    </form>
</div>
