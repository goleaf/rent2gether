<div class="mx-auto max-w-3xl space-y-5">
    <section class="space-y-2">
        <flux:heading size="xl" level="1">{{ __('account.profile_setup.heading') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('account.profile_setup.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    @php
        $checklist = [
            ['done' => auth()->user()->profile?->avatar_path || auth()->user()->avatar, 'label' => __('account.profile_setup.checklist.photo')],
            ['done' => (bool) auth()->user()->email_verified_at, 'label' => __('account.profile_setup.checklist.email')],
            ['done' => (bool) auth()->user()->profile?->phone_verified_at, 'label' => __('account.profile_setup.checklist.phone')],
            ['done' => filled($about), 'label' => __('account.profile_setup.checklist.about')],
            ['done' => $prefersQuiet || filled($sleepSchedule) || filled($socialLevel), 'label' => __('account.profile_setup.checklist.preferences')],
        ];
    @endphp

    <flux:card class="space-y-3">
        <flux:heading size="sm">{{ __('account.profile_setup.checklist.title') }}</flux:heading>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach($checklist as $item)
                <div class="flex items-center justify-between gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                    <span>{{ $item['label'] }}</span>
                    <flux:badge :color="$item['done'] ? 'green' : 'zinc'" size="sm">
                        {{ $item['done'] ? __('account.profile_setup.checklist.done') : __('account.profile_setup.checklist.later') }}
                    </flux:badge>
                </div>
            @endforeach
        </div>
        <flux:text size="sm" class="text-zinc-500">{{ __('account.profile_setup.checklist.phone_placeholder') }}</flux:text>
    </flux:card>

    <form wire:submit="save" class="space-y-5">
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('account.profile_setup.photo') }}</flux:heading>
            <div class="flex items-center gap-4">
                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    @if($avatar && str_starts_with((string) $avatar->getMimeType(), 'image/'))
                        <img src="{{ $avatar->temporaryUrl() }}" alt="{{ __('account.profile_setup.photo') }}" class="size-full object-cover">
                    @else
                        <flux:icon name="user" class="size-8 text-zinc-400" />
                    @endif
                </div>
                <flux:field class="flex-1">
                    <flux:input type="file" wire:model="avatar" accept="image/*" />
                    <flux:description>{{ __('account.profile_setup.photo_helper') }}</flux:description>
                    <flux:error name="avatar" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('account.profile_setup.personal') }}</flux:heading>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('account.fields.display_name') }}</flux:label>
                    <flux:input wire:model.blur="displayName" />
                    <flux:error name="displayName" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('account.fields.phone') }}</flux:label>
                    <flux:input wire:model.blur="phone" inputmode="tel" />
                    <flux:error name="phone" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('account.fields.country') }}</flux:label>
                    <flux:input wire:model.blur="country" />
                    <flux:error name="country" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('account.fields.city') }}</flux:label>
                    <flux:input wire:model.blur="city" />
                    <flux:error name="city" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('account.fields.languages') }}</flux:label>
                    <flux:input wire:model.blur="languages" placeholder="{{ __('account.fields.languages_placeholder') }}" />
                    <flux:error name="languages" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('account.fields.date_of_birth') }}</flux:label>
                    <flux:input type="date" wire:model.change="dateOfBirth" />
                    <flux:error name="dateOfBirth" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('account.fields.gender') }}</flux:label>
                    <flux:select wire:model.change="gender">
                        <flux:select.option value="">{{ __('account.options.not_specified') }}</flux:select.option>
                        <flux:select.option value="male">{{ __('account.options.male') }}</flux:select.option>
                        <flux:select.option value="female">{{ __('account.options.female') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('account.options.other') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="gender" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('account.fields.occupation') }}</flux:label>
                    <flux:input wire:model.blur="occupation" />
                    <flux:error name="occupation" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>{{ __('account.fields.about') }}</flux:label>
                <flux:textarea wire:model.blur="about" rows="4" />
                <flux:error name="about" />
            </flux:field>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('account.profile_setup.lifestyle') }}</flux:heading>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('account.fields.travel_purpose') }}</flux:label>
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
                    <flux:label>{{ __('account.fields.sleep_schedule') }}</flux:label>
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
                    <flux:label>{{ __('account.fields.social_level') }}</flux:label>
                    <flux:select wire:model.change="socialLevel">
                        <flux:select.option value="">{{ __('account.options.not_specified') }}</flux:select.option>
                        <flux:select.option value="quiet">{{ __('account.options.quiet') }}</flux:select.option>
                        <flux:select.option value="balanced">{{ __('account.options.balanced') }}</flux:select.option>
                        <flux:select.option value="social">{{ __('account.options.social') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="socialLevel" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('account.fields.allergies') }}</flux:label>
                    <flux:input wire:model.blur="allergies" />
                    <flux:error name="allergies" />
                </flux:field>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <flux:checkbox wire:model.change="smokes" label="{{ __('account.fields.smokes') }}" />
                <flux:checkbox wire:model.change="hasPets" label="{{ __('account.fields.has_pets') }}" />
                <flux:checkbox wire:model.change="prefersQuiet" label="{{ __('account.fields.prefers_quiet') }}" />
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('account.profile_setup.account_type') }}</flux:heading>
            <flux:select wire:model.change="accountRole">
                <flux:select.option value="guest">{{ __('account.roles.guest') }}</flux:select.option>
                <flux:select.option value="host">{{ __('account.roles.host') }}</flux:select.option>
                <flux:select.option value="both">{{ __('account.roles.both') }}</flux:select.option>
            </flux:select>
            <flux:error name="accountRole" />
        </flux:card>

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70">
                <span wire:loading.remove wire:target="save">{{ __('account.profile_setup.save') }}</span>
                <span wire:loading wire:target="save">{{ __('account.actions.saving') }}</span>
            </flux:button>
        </div>
    </form>
</div>
