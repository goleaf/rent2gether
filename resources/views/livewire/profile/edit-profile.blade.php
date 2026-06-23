<x-ui.page class="space-y-6">
    <flux:heading size="xl">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="user" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('app.profile.edit') }}</span>
        </span>
    </flux:heading>

    @if(session('success'))
        <flux:badge color="green" icon="check-circle">{{ session('success') }}</flux:badge>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="user" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('app.profile.personal_information') }}</span>
                </span>
            </flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.name') }}</span>
                        </span>
                    </flux:label>
                    <flux:input wire:model.blur="name" :error="$errors->first('name')" icon="user" />
                    <flux:error name="name" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.phone') }}</span>
                        </span>
                    </flux:label>
                    <flux:input wire:model.blur="phone" :error="$errors->first('phone')" icon="phone" />
                    <flux:error name="phone" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.date_of_birth') }}</span>
                        </span>
                    </flux:label>
                    <flux:input type="date" wire:model.change="dateOfBirth" icon="calendar-days" />
                    <flux:error name="dateOfBirth" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.gender') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="gender">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="male">{{ __('app.profile.male') }}</flux:select.option>
                    <flux:select.option value="female">{{ __('app.profile.female') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('app.profile.other') }}</flux:select.option>
                </flux:select>
                    <flux:error name="gender" />
                </flux:field>
                <div class="sm:col-span-2">
                    @include('livewire.geo.partials.country-city-autocomplete', ['autocompleteKey' => 'profile-edit'])
                </div>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.occupation') }}</span>
                        </span>
                    </flux:label>
                    <flux:input wire:model.blur="occupation" icon="briefcase" />
                    <flux:error name="occupation" />
                </flux:field>
            </div>
                        <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('app.profile.about_me') }}</span>
                    </span>
                </flux:label>
                <flux:textarea wire:model.blur="bio" rows="3" />
                <flux:error name="bio" />
            </flux:field>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="user" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('app.profile.lifestyle') }}</span>
                </span>
            </flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.sleep_schedule') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="sleepSchedule">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="early_bird">{{ __('app.profile.early_bird') }}</flux:select.option>
                    <flux:select.option value="night_owl">{{ __('app.profile.night_owl') }}</flux:select.option>
                    <flux:select.option value="flexible">{{ __('app.profile.flexible') }}</flux:select.option>
                </flux:select>
                    <flux:error name="sleepSchedule" />
                </flux:field>
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.travel_purpose') }}</span>
                        </span>
                    </flux:label>
                    <flux:select wire:model.change="travelPurpose">
                    <flux:select.option value="">{{ __('occupants.options.not_set') }}</flux:select.option>
                    <flux:select.option value="tourism">{{ __('app.profile.tourism') }}</flux:select.option>
                    <flux:select.option value="work">{{ __('app.profile.work') }}</flux:select.option>
                    <flux:select.option value="study">{{ __('app.profile.study') }}</flux:select.option>
                    <flux:select.option value="relocation">{{ __('app.profile.relocation') }}</flux:select.option>
                </flux:select>
                    <flux:error name="travelPurpose" />
                </flux:field>
            </div>
            <div class="flex flex-wrap gap-4">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="isSmoker" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.smoker') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="isSmoker" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="hasPets" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.has_pets') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasPets" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="hasAllergies" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.has_allergies') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasAllergies" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="prefersQuiet" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.prefers_quiet') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="prefersQuiet" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="willingToShareRoom" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.share_room') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="willingToShareRoom" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('app.profile.host_profile') }}</span>
                </span>
            </flux:heading>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="isHost" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('app.profile.is_host') }}</span>
                    </span>
                </flux:label>
                <flux:error name="isHost" />
            </flux:field>
            @if($this->isHost)
                                <flux:field>
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.host_description') }}</span>
                        </span>
                    </flux:label>
                    <flux:textarea wire:model.blur="hostDescription" rows="3" />
                    <flux:error name="hostDescription" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="star" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('app.profile.experience_started_year') }}</span>
    </span>
</flux:label>
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
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="hostLivesOnSite" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('app.profile.lives_on_site') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hostLivesOnSite" />
                </flux:field>
            @endif
        </flux:card>

        <flux:button type="submit" variant="primary" icon="check">{{ __('app.actions.save') }}</flux:button>
    </form>
</x-ui.page>
