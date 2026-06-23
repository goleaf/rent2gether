<x-ui.page>
    <section class="space-y-2">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="cog-6-tooth" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('account.profile_setup.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('account.profile_setup.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('account.profile_setup.checklist.title') }}</span>
            </span>
        </flux:heading>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach($checklist as $item)
                <div class="flex items-center justify-between gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                    <span>{{ $item['label'] }}</span>
                    <flux:badge :color="$item['done'] ? 'green' : 'zinc'" size="sm" icon="check-circle">
                        {{ $item['done'] ? __('account.profile_setup.checklist.done') : __('account.profile_setup.checklist.later') }}
                    </flux:badge>
                </div>
            @endforeach
        </div>
        <flux:text size="sm" class="text-zinc-500">{{ __('account.profile_setup.checklist.phone_placeholder') }}</flux:text>
    </flux:card>

    <form wire:submit="save" class="space-y-5">
        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="photo" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('account.profile_setup.photo') }}</span>
                </span>
            </flux:heading>
            <div class="flex items-center gap-4">
                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    @if($avatar && str_starts_with((string) $avatar->getMimeType(), 'image/'))
                        <img src="{{ $avatar->temporaryUrl() }}" alt="{{ __('account.profile_setup.photo') }}" class="size-full object-cover">
                    @elseif($savedAvatarPath)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($savedAvatarPath) }}" alt="{{ __('account.profile_setup.photo') }}" class="size-full object-cover">
                    @else
                        <flux:icon name="user" class="size-8 text-zinc-400" />
                    @endif
                </div>
                <div class="flex-1 space-y-2">
                    <flux:file-upload
                        wire:model="avatar"
                        :label="__('account.profile_setup.photo')"
                        :description="__('account.profile_setup.photo_helper')"
                        :error="$errors->first('avatar')"
                    >
                        <flux:file-upload.dropzone
                            :heading="__('account.profile_setup.photo')"
                            :text="__('account.profile_setup.photo_helper')"
                            with-progress
                            inline
                        />
                    </flux:file-upload>
                    <flux:text wire:loading wire:target="avatar" size="sm" class="text-zinc-500 dark:text-zinc-400">
                        {{ __('media.manager.uploading') }}
                    </flux:text>
                </div>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('account.profile_setup.personal') }}</span>
                </span>
            </flux:heading>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.display_name') }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="displayName" icon="user" />
                    <flux:error name="displayName" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.phone') }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="phone" inputmode="tel" icon="phone" />
                    <flux:error name="phone" />
                </flux:field>
                <div class="sm:col-span-2">
                    @include('livewire.geo.partials.country-city-autocomplete', [
                        'autocompleteKey' => 'profile-setup',
                        'countryLabel' => __('account.fields.country'),
                        'cityLabel' => __('account.fields.city'),
                    ])
                </div>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.languages') }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="languages" placeholder="{{ __('account.fields.languages_placeholder') }}" icon="language" />
                    <flux:error name="languages" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.date_of_birth') }}</span>
    </span>
</flux:label>
                    <flux:input type="date" wire:model.change="dateOfBirth" icon="calendar-days" />
                    <flux:error name="dateOfBirth" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.gender') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="gender">
                        <flux:select.option value="">{{ __('account.options.not_specified') }}</flux:select.option>
                        <flux:select.option value="male">{{ __('account.options.male') }}</flux:select.option>
                        <flux:select.option value="female">{{ __('account.options.female') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('account.options.other') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="gender" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.occupation') }}</span>
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
        <span class="min-w-0">{{ __('account.fields.about') }}</span>
    </span>
</flux:label>
                <flux:textarea wire:model.blur="about" rows="4" />
                <flux:error name="about" />
            </flux:field>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('account.profile_setup.lifestyle') }}</span>
                </span>
            </flux:heading>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.travel_purpose') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="travelPurpose">
                        <flux:select.option value="">{{ __('account.options.not_specified') }}</flux:select.option>
                        <flux:select.option value="tourism">{{ __('account.options.tourism') }}</flux:select.option>
                        <flux:select.option value="work">{{ __('account.options.work') }}</flux:select.option>
                        <flux:select.option value="study">{{ __('account.options.study') }}</flux:select.option>
                        <flux:select.option value="relocation">{{ __('account.options.relocation') }}</flux:select.option>
                        <flux:select.option value="temporary_stay">{{ __('account.options.temporary_stay') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="travelPurpose" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.sleep_schedule') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="sleepSchedule">
                        <flux:select.option value="">{{ __('account.options.not_specified') }}</flux:select.option>
                        <flux:select.option value="early_bird">{{ __('account.options.early_bird') }}</flux:select.option>
                        <flux:select.option value="night_owl">{{ __('account.options.night_owl') }}</flux:select.option>
                        <flux:select.option value="flexible">{{ __('account.options.flexible') }}</flux:select.option>
                        <flux:select.option value="regular">{{ __('account.options.regular') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="sleepSchedule" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.social_level') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="socialLevel">
                        <flux:select.option value="">{{ __('account.options.not_specified') }}</flux:select.option>
                        <flux:select.option value="quiet">{{ __('account.options.quiet') }}</flux:select.option>
                        <flux:select.option value="balanced">{{ __('account.options.balanced') }}</flux:select.option>
                        <flux:select.option value="social">{{ __('account.options.social') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="socialLevel" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('account.fields.allergies') }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="allergies" icon="heart" />
                    <flux:error name="allergies" />
                </flux:field>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="smokes" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.fields.smokes') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="smokes" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="hasPets" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.fields.has_pets') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasPets" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="prefersQuiet" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('account.fields.prefers_quiet') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="prefersQuiet" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('account.profile_setup.account_type') }}</span>
                </span>
            </flux:heading>
            <flux:select wire:model.change="accountRole">
                <flux:select.option value="guest">{{ __('account.roles.guest') }}</flux:select.option>
                <flux:select.option value="host">{{ __('account.roles.host') }}</flux:select.option>
                <flux:select.option value="both">{{ __('account.roles.both') }}</flux:select.option>
            </flux:select>
            <flux:error name="accountRole" />
        </flux:card>

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70" icon="check">
                <span wire:loading.remove wire:target="save">{{ __('account.profile_setup.save') }}</span>
                <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
            </flux:button>
        </div>
    </form>
</x-ui.page>
